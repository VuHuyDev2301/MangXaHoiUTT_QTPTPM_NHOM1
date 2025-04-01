<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirectTo('../trang-chu.php');
}

$db = new Database();
$conn = $db->getConnection();

// Handle GET request for initial load

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $outgoing_id = $_SESSION['user_id'];
    $incoming_id = isset($_GET['user_id']) && is_numeric($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    $stmt = $conn->prepare("
        SELECT m.*, n.ho_ten, n.anh_dai_dien 
        FROM ban_be m 
        JOIN nguoi_dung n ON n.id = m.nguoi_dung_id 
        WHERE (m.nguoi_dung_id = :outgoing_id AND m.ban_be_id = :incoming_id)
        OR (m.ban_be_id = :outgoing_id AND m.nguoi_dung_id = :incoming_id)
        ORDER BY m.thoi_gian ASC
    ");
    
    $stmt->execute([
        'outgoing_id' => $outgoing_id,
        'incoming_id' => $incoming_id
    ]);
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $currentDate = null;

    if (empty($messages)) {
        echo "<p class='no-messages'>Chưa có tin nhắn nào. Hãy bắt đầu trò chuyện ngay!</p>";
    } else {
        foreach ($messages as $message) {
            $isOutgoing = $message['nguoi_dung_id'] == $_SESSION['user_id'];
            $messageClass = $isOutgoing ? 'outgoing' : 'incoming';
            $avatar = $message['anh_dai_dien'];
            $time = date("H:i", strtotime($message['thoi_gian']));
            $date = date("d/m/Y", strtotime($message['thoi_gian']));

            // Phân chia tin nhắn theo ngày
            if ($currentDate !== $date) {
                echo "<div class='date-separator'>$date</div>";
                $currentDate = $date;
            }

            // Xử lý trạng thái tin nhắn
            $status = strtolower($message['trang_thai']);
            $statusClass = ($status === 'chua_gui') ? 'error' : '';
            $statusText = "";
            $statusIcon = "";

            // Gán trạng thái với icon
            switch ($status) {
                case "da_xem":
                    $statusText = "Đã xem";
                    $statusIcon = "✔✔"; // Dấu V kép
                    break;
                case "da_gui":
                    $statusText = "Đã gửi";
                    $statusIcon = "✔"; // Dấu V đơn
                    break;
                case "chua_xem":
                    $statusText = "Chưa xem";
                    $statusIcon = "⏳"; // Đồng hồ chờ
                    break;
                case "chua_gui":
                    $statusText = "Chưa gửi";
                    $statusIcon = "❌"; // Dấu X đỏ
                    break;
            }

            // Hiển thị tin nhắn
            echo "<div class='message {$messageClass}'>";

            // Hiển thị avatar nếu là tin nhắn nhận vào
            if (!$isOutgoing) {
                echo "<img src='uploads/avatars/{$avatar}' alt='Avatar' class='incoming-avatar'>";
            }

            // Tin nhắn
            echo "<div class='bubble'>";
            echo htmlspecialchars($message['tin_nhan']);
            echo "</div>";

            // Hiển thị thời gian tách biệt
            echo "<span class='message-time'>{$time}</span>";

            echo "</div>";

            // Hiển thị trạng thái tin nhắn
            $statusPosition = $isOutgoing ? 'outgoing-status' : 'incoming-status';
            echo "<div class='message-status {$statusClass} {$statusPosition}'>
                    {$statusText} <span class='status-icon'>{$statusIcon}</span>
                </div>";

        }
    }
}
?>