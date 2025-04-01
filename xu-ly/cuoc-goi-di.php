<?php
session_start();
require_once '../config/database.php';

$db = new Database();
$conn = $db->getConnection();
if (!isset($_SESSION['user_id'])) {
    redirectTo('trang-chu.php');
}

if (isset($_GET['from_id']) && isset($_GET['to_friend_id'])) {
  $fromUserId = intval($_GET['from_id']);
  $toFriendId = intval($_GET['to_friend_id']);
  $stmt = $conn->prepare("SELECT ho_ten, anh_dai_dien FROM nguoi_dung WHERE id = :to_friend_id");
  $stmt->execute(['to_friend_id' => $fromUserId]);
  $caller = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/call.css"> 
  <title>Cuộc gọi đi</title>
</head>
<body>
  <div class="call-header">
    <img src="../uploads/avatars/default.jpg" alt="Profile Picture" class="profile-pic" id="profilePic">
    <div class="caller-name" id="callerName">Cuộc gọi đi...</div>
    <div class="calling-status" id="callingStatus">Đang gọi...</div>
    <video id="local-video" autoplay playsinline muted></video>
    <video id="remote-video" autoplay playsinline></video>
  </div>
  <!-- <button class="end-call-btn" onclick="endCall()">Kết thúc</button> -->

  <script src="http://localhost:3000/socket.io/socket.io.js"></script>
  <script>
    // Lấy các tham số từ URL
    const urlParams = new URLSearchParams(window.location.search);
    const fromUserId = urlParams.get('from_id');
    const toFriendId = urlParams.get('to_friend_id');

    console.log('From user:', fromUserId);
    console.log('Calling friend:', toFriendId);
    
    // Kết nối đến Socket.IO server
    const socket = io('http://localhost:3000', {
      reconnection: true,
      timeout: 10000
    });
    
    socket.on('connect', () => {
    console.log('Socket from cuoc-goi-di.php connected:', socket.id);
    socket.emit('register-user', fromUserId);

    // Gửi yêu cầu gọi nếu cần
    if (fromUserId && toFriendId) {
        socket.emit('call-user', {
            fromUserId: fromUserId,
            toUserId: toFriendId
        });
      
    
      
    // Xử lý phản hồi khi cuộc gọi được chấp nhận
    socket.on('call-accepted', (data) => {
      document.getElementById('callerName').textContent = data.ho_ten;
      document.getElementById('profilePic').src = data.anh_dai_dien;
      document.getElementById('callingStatus').textContent = 'Đã kết nối';
      console.log('Call accepted, room:', data.roomID);
      // Ở đây bạn có thể khởi tạo kết nối WebRTC nếu cần
    });

    // Xử lý cuộc gọi thất bại
    socket.on('call-failed', (data) => {
      document.getElementById('callerName').textContent = 'Cuộc gọi thất bại: ' + data.message;
      console.log('Call failed:', data.message);
      setTimeout(() => endCall(), 2000);
    });

    socket.on('connect_error', (error) => {
      console.error('Connection error:', error);
      document.getElementById('callerName').textContent = 'Lỗi kết nối';
    });

    socket.on('call-ended', (data) => {
      document.getElementById('callerName').textContent = 'Cuộc gọi đã kết thúc';
      console.log('Call ended:', data.message);
      setTimeout(() => endCall(),2000);
    });
    
    // Hàm kết thúc cuộc gọi
    function endCall() {
      if (socket.connected) {
        socket.emit('call-ended', {
          fromUserId: fromUserId,
          toUserId: toFriendId
        });
      }
    }

    // Xử lý khi trang đóng
    window.onbeforeunload = () => {
      if (socket.connected) {
        socket.emit('call-rejected', {
          fromUserId: fromUserId,
          toUserId: toFriendId
        });
      }
    };
  }});
  </script>
</body>
</html>
