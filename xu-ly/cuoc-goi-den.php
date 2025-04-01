<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/call.css">
  <title>Cuộc Gọi Đến</title>
</head>
<body>
  
  <!-- Modal hiển thị sau khi chấp nhận cuộc gọi (Active Call) -->
      <div class="call-header">
        <img src="../uploads/avatars/default.jpg" alt="Profile Picture" class="profile-pic" id="profilePic">
        <div class="caller-name" id="caller-name">Cuộc gọi đến</div>
        <p>Thời gian: <span id="call-timer">0:00</span></p>
        <video id="local-video" autoplay playsinline muted></video>
        <video id="remote-video" autoplay playsinline></video>
        <!-- <div class="calling-status" id="calling-status">Đang kết nối...</div> -->
      </div>
      <!-- <button class="end-call-btn" onclick="endCall()">Kết thúc</button> -->
  <!-- Kết nối Socket.IO -->
  <script src="http://localhost:3000/socket.io/socket.io.js"></script>
  <script>
    // Lấy các tham số từ URL của cuộc gọi đến
    const urlParams = new URLSearchParams(window.location.search);
    const callerId = urlParams.get('from_friend_id');       // Người gọi
    const currentUserId = urlParams.get('to_user_id'); // Người nhận (chính là user hiện tại)
    
    console.log('Caller ID:', callerId);
    console.log('Current User ID:', currentUserId);
  
    // Khởi tạo kết nối socket cho user nhận cuộc gọi
    const socket = io('http://localhost:3000', { reconnection: true, timeout: 10000 });
    
    socket.on('connect', () => {
        console.log('Socket connected:', socket.id);
        socket.emit('register-user', currentUserId);
    });
  
    // Hiển thị modal cuộc gọi đến khi có sự kiện incoming-call từ server
    socket.on('incoming-call', (data) => {
        const { fromUserId, toUserId, roomID } = data;
        // Chỉ hiển thị nếu người nhận (toUserId) khớp với currentUserId
        if (toUserId === currentUserId) {
            document.getElementById('call-modal').style.display = 'block';
            document.getElementById('call-name').innerText = `Cuộc gọi đến từ ${fromUserId}`;
  
            let callDuration = 0; // Biến lưu thời gian cuộc gọi (tính bằng giây)
            let callTimer; // Biến lưu setInterval

            // Xử lý chấp nhận cuộc gọi
            document.getElementById('accept-call').onclick = () => {
            socket.emit('call-accepted', { fromUserId, toUserId });
            document.getElementById('incoming-call-modal').style.display = 'none';
            document.getElementById('call-modal').style.display = 'block';

            // Reset thời gian và khởi động đồng hồ đếm
            callDuration = 0;
            updateCallDuration(); // Hiển thị ban đầu
            callTimer = setInterval(() => {
                callDuration++;
                updateCallDuration();
            }, 1000);

            socket.emit('join-room', { roomID });
            };

            // Hàm cập nhật hiển thị thời gian cuộc gọi
            function updateCallDuration() {
                const minutes = Math.floor(callDuration / 60);
                const seconds = callDuration % 60;
                document.getElementById('call-timer').innerText = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            }

            // Xử lý kết thúc cuộc gọi
            document.getElementById('end-call').onclick = () => {
                clearInterval(callTimer); // Dừng đếm thời gian
                document.getElementById('call-modal').style.display = 'none';
                socket.emit('call-ended', { fromUserId, toUserId });
                setTimeout(() => {
                  endCall();
                  document.getElementById('call-modal').style.display = 'none';
                }, 2000);
                 
            };

          
            // Xử lý từ chối cuộc gọi
            document.getElementById('reject-call').onclick = () => {
                socket.emit('call-rejected', { fromUserId, toUserId });
                document.getElementById('incoming-call-modal').style.display = 'none';
                setTimeout(() => {
                  endCall();
                  document.getElementById('call-modal').style.display = 'none';
                }, 2000);
            };
        }
    });
    // Hàm kết thúc cuộc gọi
    function endCall() {
      if (socket.connected) {
        socket.emit('call-ended', {
          fromUserId: callerId,
          toUserId: currentUserId
        });
      }
    }
  </script>
  
</body>
</html>
