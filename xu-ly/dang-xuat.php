<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    $db = new Database();
    $conn = $db->getConnection();

    try {
        // Cập nhật trạng thái offline
        $stmt = $conn->prepare("UPDATE nguoi_dung SET trang_thai = 'khong-hoat-dong' WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Xóa session
        session_destroy();
        
        redirectTo('../index.php');
    } catch(PDOException $e) {
        setFlashMessage('error', 'Có lỗi xảy ra khi đăng xuất!');
        redirectTo('../trang-chu.php');
    }
} else {
    redirectTo('../index.php');
}
?> 