<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $bai_viet_id = $_POST['bai_viet_id'];
    $nguoi_dung_id = $_SESSION['user_id'];
    
    try {
        // Kiểm tra quyền xóa
        $stmt = $conn->prepare("SELECT nguoi_dung_id, anh FROM bai_viet WHERE id = ?");
        $stmt->execute([$bai_viet_id]);
        $bai_viet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($bai_viet['nguoi_dung_id'] != $nguoi_dung_id) {
            die(json_encode(['status' => 'error', 'message' => 'Không có quyền xóa bài viết này']));
        }
        
        // Xóa ảnh nếu có
        if ($bai_viet['anh']) {
            $anh_path = "../uploads/posts/" . $bai_viet['anh'];
            if (file_exists($anh_path)) {
                unlink($anh_path);
            }
        }
        
        // Xóa bài viết (các bình luận và lượt thích sẽ tự động xóa do ràng buộc khóa ngoại)
        $stmt = $conn->prepare("DELETE FROM bai_viet WHERE id = ?");
        $stmt->execute([$bai_viet_id]);
        
        echo json_encode(['status' => 'success', 'message' => 'Đã xóa bài viết']);
        
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}
?> 