
<?php
session_start();
require_once 'includes/functions.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    redirectTo('trang-chu.php');
}

$db = new Database();
$conn = $db->getConnection();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MessengUTTers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/messenger.css">
    <script src="https://cdn.jsdelivr.net/npm/socket.io-client@4.5.1/dist/socket.io.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/peerjs/1.3.1/peerjs.min.js"></script>

</head>
<body>
    <div class="container">
        <!-- Sidebar with friend list -->
        <div class="sidebar-msg">
            <a class="navbar-brand" href="trang-chu.php">
                <img src="assets/images/logo.png" alt="UTT Social" height="40">
            </a>
            <div class="search-box-msg">
            <input type="text" id="search_friend" placeholder="Search messages..." oninput="searchFriend(this.value)">
            <i class="fas fa-search"></i>
            </div>

            <div id="searchResults">
                <?php
                $userId = $_SESSION['user_id'];

                // Truy vấn danh sách bạn bè có tin nhắn gần nhất
                $stmt = $conn->prepare("
                    SELECT nd.id, nd.ho_ten, nd.anh_dai_dien, 
                        (SELECT tin_nhan FROM ban_be 
                            WHERE (nguoi_dung_id = nd.id AND ban_be_id = :user_id) 
                            OR (ban_be_id = nd.id AND nguoi_dung_id = :user_id) 
                            ORDER BY thoi_gian DESC LIMIT 1) AS lastMessage
                    FROM nguoi_dung nd
                    ORDER BY nd.ho_ten ASC
                ");

                $stmt->execute(['user_id' => $userId]);
                $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($friends) {
                    foreach ($friends as $row) {
                        $lastMessage = $row['lastMessage'] ? htmlspecialchars($row['lastMessage']) : "Không có tin nhắn";
                        echo "<div class='friend' data-friend-id='{$row['id']}'>
                                <img src='uploads/avatars/{$row['anh_dai_dien']}' alt='Avatar'>
                                <div class='friend-info'>
                                    <span class='friend-name'>{$row['ho_ten']}</span>
                                    <span class='last-message'>{$lastMessage}</span>
                                </div>
                            </div>";
                    }
                } else {
                    echo "<p>Không tìm thấy bạn bè nào.</p>";
                }
                ?>
            </div>

            <script>
            function searchFriend(keyword) {
                let friends = document.querySelectorAll('.friend');
                keyword = keyword.toLowerCase();

                friends.forEach(friend => {
                    let name = friend.querySelector('.friend-name').textContent.toLowerCase();
                    friend.style.display = name.includes(keyword) ? "block" : "none";
                });
            }
            </script>

            
            <div class="friend-list">
            <?php
            $stmt = $conn->prepare("SELECT * FROM nguoi_dung WHERE id != :user_id");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($friends)) {
                foreach ($friends as $row) {
                    $lastMsgStmt = $conn->prepare(
                        "SELECT tin_nhan 
                        FROM ban_be
                        WHERE (nguoi_dung_id = :user_id AND ban_be_id = :friend_id)
                        OR (ban_be_id = :friend_id AND nguoi_dung_id = :user_id)
                        ORDER BY id DESC LIMIT 1"
                    );
                    $lastMsgStmt->execute([
                        'user_id' => $_SESSION['user_id'],
                        'friend_id' => $row['id']
                    ]);
                    $lastMsg = $lastMsgStmt->fetch();
                    $lastMessage = $lastMsg ? htmlspecialchars(substr($lastMsg['tin_nhan'], 0, 30)) . '...' : 'Chưa có tin nhắn nào';

                    echo "<div class='friend' data-friend-id='{$row['id']}'>
                        <img src='uploads/avatars/{$row['anh_dai_dien']}' alt='Avatar'>
                        <div class='friend-info'>
                            <span class='friend-name'>{$row['ho_ten']}</span>
                            <span class='last-message'>{$lastMessage}</span>
                        </div>
                    </div>";
                }
            } else {
                echo "<p class='no-friends'>Không có bạn bè nào.</p>";
            }
            ?>

            </div>
        </div>

        <!-- Chat window -->
        <div class="chat-container-msg">
            <div class="chat-header-msg">
                <div class="user-info-msg">
                    <div class="user-info-msg" id="chat-header-info">
    
                     </div>
                <div class="chat-actions">
                    <i class="fas fa-phone" title="Call" onclick="initiateVoiceCall('<?php echo $_GET['ban_be_id'] ?? ''; ?>')"></i>
                    <i class="fas fa-video" title="Video call"></i>
                    <i class="fas fa-info-circle" title="Info"></i>
                </div>
            </div>
            <!-- Các modal sẽ được tạo động thông qua JavaScript -->

            <script>
            // -----------------------------
            // Outgoing call: Khi người dùng nhấn gọi đi
            // -----------------------------
            function initiateVoiceCall(friendId) {
                if (!friendId) {
                alert('Please select a friend to call');
                return;
                }
                
                // Kiểm tra modal gọi đi đã tồn tại hay chưa
                let modal = document.getElementById('callModal');
                if (!modal) {
                modal = document.createElement('div');
                modal.id = 'callModal';
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content">
                    <iframe src="xu-ly/cuoc-goi-di.php?from_id=<?php echo $_SESSION['user_id']; ?>&to_friend_id=${friendId}" 
                            style="width:100%; height:calc(100% - 60px); border:none; border-radius:8px;">
                    </iframe>
                    <button class="end-call-btn" onclick="closeCallModal()">Kết thúc</button>
                    </div>
                `;
                document.body.appendChild(modal);
                
                // Thêm CSS cho modal (nếu chưa có)
                const style = document.createElement('style');
                style.textContent = `
                    .modal {
                    display: none;
                    position: fixed;
                    z-index: 1000;
                    left: 50%;
                    top: 50%;
                    transform: translate(-50%, -50%);
                    width: 400px;
                    height: 600px;
                    background-color: white;
                    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
                    border-radius: 8px;
                    padding: 20px;
                    text-align: center;
                    }
                    .modal-content {
                    width: 100%;
                    height: 100%;
                    position: relative;
                    }
                    .modal-content iframe {
                    width: 100%;
                    height: calc(100% - 60px);
                    border: none;
                    border-radius: 8px;
                    }
                    .end-call-btn {
                    position: absolute;
                    bottom: 10px;
                    left: 50%;
                    transform: translateX(-50%);
                    padding: 12px 24px;
                    background: red;
                    color: white;
                    border: none;
                    font-size: 16px;
                    font-weight: bold;
                    cursor: pointer;
                    border-radius: 6px;
                    transition: 0.3s;
                    }
                    .end-call-btn:hover {
                    background: darkred;
                    }
                `;
                document.head.appendChild(style);
                } else {
                // Nếu modal đã tồn tại, chỉ cập nhật src của iframe với friendId mới
                let iframe = modal.querySelector('iframe');
                iframe.src = `xu-ly/cuoc-goi-di.php?from_id=<?php echo $_SESSION['user_id']; ?>&to_friend_id=${friendId}`;
                }
                
                modal.style.display = 'block';
            }

            function closeCallModal() {
                let modal = document.getElementById('callModal');
                if (modal) {
                socket.emit('cakk-ended', {
                    fromUserId: <?php echo json_encode($_SESSION['user_id']); ?>,
                    toUserId: <?php echo json_encode($_GET['ban_be_id'] ?? ''); ?>
                });
                modal.style.display = 'none';
                }
            }

            // -----------------------------
            // Khởi tạo Socket.IO và đăng ký user
            // -----------------------------
            const fromUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
            const socket = io('http://localhost:3000', { reconnection: true, timeout: 10000 });

            socket.on('connect', () => {
                console.log('Socket (from messenger.php) connected:', socket.id);
                socket.emit('register-user', fromUserId);
            });

            socket.on('call-failed', (data) => {
                alert('Cuộc gọi thất bại: ' + data.message);
                setTimeout(() => closeCallModal(), 2000);
            });

            socket.on('call-accepted', (data) => {
                console.log('Call accepted, room:', data.roomID);
                // Logic chuyển giao cuộc gọi sang WebRTC có thể được thực hiện tại đây nếu cần.
            });

            socket.on('connect_error', (error) => {
                console.error('Connection error:', error);
            });

            window.onbeforeunload = () => {
                if (socket.connected) {
                socket.emit('call-rejected', {
                    fromUserId: fromUserId,
                    toUserId: <?php echo json_encode($_GET['ban_be_id'] ?? ''); ?>
                });
                }
            };

            // -----------------------------
            // Incoming call: Xử lý sự kiện cuộc gọi đến từ server
            // -----------------------------
            socket.on('incoming-call', (data) => {
                const { fromUserId: callerId, toUserId, roomID } = data;
                console.log('Incoming call:', data);
                const currentUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
                // Chỉ hiển thị modal nếu toUserId trùng với currentUserId
                if (toUserId === currentUserId) {
                showIncomingCallAlert(callerId, roomID);
                }
            });

            // Hàm hiển thị modal alert cuộc gọi đến với các nút "Chấp nhận" và "Từ chối"
            function showIncomingCallAlert(callerId, roomID) {
                let modal = document.getElementById('incoming-call-modal');
                if (!modal) {
                modal = document.createElement('div');
                modal.id = 'incoming-call-modal';
                modal.innerHTML = `
                    <div class="modal-content">
                    <p id="call-name">Đang tải...</p>
                    <button class="btn accept" id="accept-call">Chấp nhận</button>
                    <button class="btn reject" id="reject-call">Từ chối</button>
                    </div>
                `;
                // Bạn có thể thêm CSS cho modal alert này ở đây hoặc qua file CSS riêng
                document.body.appendChild(modal);
                }
                document.getElementById('call-name').innerText = `Cuộc gọi đến từ ${callerId}`;
                modal.style.display = 'block';

                // Xử lý chấp nhận cuộc gọi
                document.getElementById('accept-call').onclick = () => {
                socket.emit('call-accepted', { fromUserId: callerId, toUserId: <?php echo json_encode($_SESSION['user_id']); ?> });
                modal.style.display = 'none';
                showActiveCallModal(callerId, roomID);
                socket.emit('join-room', { roomID });
                };

                // Xử lý từ chối cuộc gọi
                document.getElementById('reject-call').onclick = () => {
                socket.emit('call-rejected', { fromUserId: callerId, toUserId: <?php echo json_encode($_SESSION['user_id']); ?> });
                modal.style.display = 'none';
                };
            }

            // Hàm hiển thị modal active call (sau khi chấp nhận cuộc gọi)
            function showActiveCallModal(callerId, roomID) {
                let modal = document.getElementById('callModal');
                if (!modal) {
                modal = document.createElement('div');
                modal.id = 'callModal';
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content">
                    <iframe src="./xu-ly/cuoc-goi-den.php?from_friend_id=${callerId}&to_user_id=<?php echo $_SESSION['user_id']; ?>" 
                            style="width:100%; height:calc(100% - 60px); border:none; border-radius:8px;"></iframe>
                    <button class="end-call-btn" onclick="closeCallModal()">Kết thúc</button>
                    </div>
                `;
                const style = document.createElement('style');
                style.textContent = `
                    .modal {
                    display: none;
                    position: fixed;
                    z-index: 1000;
                    left: 50%;
                    top: 50%;
                    transform: translate(-50%, -50%);
                    width: 400px;
                    height: 600px;
                    background-color: white;
                    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
                    border-radius: 8px;
                    padding: 20px;
                    text-align: center;
                    }
                    .modal-content {
                    width: 100%;
                    height: 100%;
                    position: relative;
                    }
                    .modal-content iframe {
                    width: 100%;
                    height: calc(100% - 60px);
                    border: none;
                    border-radius: 8px;
                    }
                    .end-call-btn {
                    position: absolute;
                    bottom: 10px;
                    left: 50%;
                    transform: translateX(-50%);
                    padding: 12px 24px;
                    background: red;
                    color: white;
                    border: none;
                    font-size: 16px;
                    font-weight: bold;
                    cursor: pointer;
                    border-radius: 6px;
                    transition: 0.3s;
                    }
                    .end-call-btn:hover {
                    background: darkred;
                    }
                `;
                document.head.appendChild(style);
                document.body.appendChild(modal);
                } else {
                let iframe = modal.querySelector('iframe');
                iframe.src = `./xu-ly/cuoc-goi-den.php?from_friend_id=${callerId}&to_user_id=<?php echo $_SESSION['user_id']; ?>`;
                }
                modal.style.display = 'block';
            }


            // Xử lý các sự kiện khác từ server nếu cần
            socket.on('call-accepted', (data) => {
                console.log('Call accepted:', data);
            });
            
            </script>
            <div class="chat-box" id="chat-box">
            
            </div>
            

            <div class="chat-input">
                <i class="fas fa-plus-circle"></i>
                <input type="text" id="message" placeholder="Type a message..." onkeypress="if(event.key === 'Enter') { event.preventDefault(); sendMessage(); }">
                <i class="fas fa-smile"></i>
                <button type="button" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $message = $_POST['message'] ?? '';
                    $friendId = $_POST['friend_id'] ?? '';
                    
                    if (!empty($message) && !empty($friendId)) {
                        $stmt = $conn->prepare("INSERT INTO ban_be (nguoi_dung_id, ban_be_id, tin_nhan) VALUES (:user_id, :friend_id, :message)");
                        $stmt->execute([
                            'user_id' => $_SESSION['user_id'],
                            'friend_id' => $friendId,
                            'message' => $message
                        ]);
                        
                        echo json_encode(['success' => true]);
                        exit;
                    }
                    echo json_encode(['success' => false]);
                    exit;
                }
                ?>
            </div>
        </div>
    </div>
    
    <script src="assets/js/mes-event.js"></script>
</body>

</html>
