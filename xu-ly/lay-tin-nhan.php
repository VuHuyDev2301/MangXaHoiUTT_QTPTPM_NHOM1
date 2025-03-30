<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirectTo('../trang-chu.php');
}

$db = new Database();
$conn = $db->getConnection();


// // Handle POST request for auto-refresh
// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     if (isset($_POST['incoming_id'])) {
//         $outgoing_id = $_SESSION['user_id'];
//         $incoming_id = $_POST['incoming_id'];
        
//         $stmt = $conn->prepare(
//             "SELECT m.*, n.ho_ten, n.anh_dai_dien 
//             FROM ban_be m 
//             JOIN nguoi_dung n ON n.id = m.nguoi_dung_id 
//             WHERE (m.nguoi_dung_id = :outgoing_id AND m.ban_be_id = :incoming_id)
//             OR (m.ban_be_id = :incoming_id AND m.nguoi_dung_id = :outgoing_id)
//             ORDER BY m.thoi_gian ASC"
//         );
        
//         $stmt->execute([
//             'outgoing_id' => $outgoing_id,
//             'incoming_id' => $incoming_id
//         ]);
//         while ($message = $stmt->fetch(PDO::FETCH_ASSOC)) {
//             if ($message['nguoi_dung_id'] == $outgoing_id) {
//             // Outgoing message
//             echo "<div class='message outgoing'>";
//             echo "<div class='bubble'>" . htmlspecialchars($message['tin_nhan']) . "</div>";
//             echo "</div>";
//             } else {
//             // Incoming message
//             echo "<div class='message incoming'>";
//             echo "<img src='uploads/avatars/{$message['anh_dai_dien']}' alt='Avatar'>";
//             echo "<div class='bubble'>" . htmlspecialchars($message['tin_nhan']) . "</div>";
//             echo "</div>";
//             }
//         }
//     }
// }

// Handle GET request for initial load
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $outgoing_id = $_SESSION['user_id'];
        if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
            $incoming_id = intval($_GET['user_id']); // Chuyển về kiểu số nguyên an toàn
        } else {
            $incoming_id = 0; // Gán giá trị mặc định nếu không hợp lệ
        }
        
        $stmt = $conn->prepare(
            "SELECT m.*, n.ho_ten, n.anh_dai_dien 
            FROM ban_be m 
            JOIN nguoi_dung n ON n.id = m.nguoi_dung_id 
            WHERE (m.nguoi_dung_id = :outgoing_id AND m.ban_be_id = :incoming_id)
            OR (m.ban_be_id = :outgoing_id AND m.nguoi_dung_id = :incoming_id)
            ORDER BY m.id ASC"
        );
        
        $stmt->execute([
            'outgoing_id' => $outgoing_id,
            'incoming_id' => $incoming_id
        ]);
        while ($message = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $isOutgoing = $message['nguoi_dung_id'] == $_SESSION['user_id'];
            $messageClass = $isOutgoing ? 'outgoing' : 'incoming';
            $avatar = $message['anh_dai_dien'];
            echo "<div class='message {$messageClass}'>";
            
            if (!$isOutgoing) {
                echo "<img src='uploads/avatars/{$avatar}' alt='Avatar' class='incoming-avatar'>";
            }
            echo "<div class='bubble'>" . htmlspecialchars($message['tin_nhan']) . "</div>";
            echo "</div>";
        }
    }
?>