<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json'); // Đảm bảo phản hồi JSON

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']));
}

if (!isset($_POST['ban_be_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Thiếu tham số ban_be_id']));
}

$userId = $_SESSION['user_id'];
$friendId = $_POST['ban_be_id'];

try {
    // Kết nối CSDL
    $db = new Database();
    $conn = $db->getConnection();

    // Cập nhật trạng thái tin nhắn
    $stmt = $conn->prepare("
        UPDATE ban_be 
        SET trang_thai = 'da_xem' 
        WHERE ban_be_id = :user_id  
        AND nguoi_dung_id = :ban_be_id 
        AND trang_thai = 'chua_xem'
    ");
    $stmt->execute([
        'ban_be_id' => $friendId,
        'user_id' => $userId
    ]);

    // Kiểm tra số dòng bị ảnh hưởng
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không có tin nhắn cần cập nhật']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
