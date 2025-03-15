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
        // Kiểm tra xem đã thích chưa
        $stmt = $conn->prepare("SELECT id FROM thich WHERE bai_viet_id = ? AND nguoi_dung_id = ?");
        $stmt->execute([$bai_viet_id, $nguoi_dung_id]);
        
        if ($stmt->rowCount() > 0) {
            // Nếu đã thích thì bỏ thích
            $stmt = $conn->prepare("DELETE FROM thich WHERE bai_viet_id = ? AND nguoi_dung_id = ?");
            $stmt->execute([$bai_viet_id, $nguoi_dung_id]);
            $action = 'unlike';
        } else {
            // Nếu chưa thích thì thêm lượt thích
            $stmt = $conn->prepare("INSERT INTO thich (bai_viet_id, nguoi_dung_id, ngay_thich) VALUES (?, ?, NOW())");
            $stmt->execute([$bai_viet_id, $nguoi_dung_id]);
            $action = 'like';
            
            // Thêm thông báo cho chủ bài viết
            $stmt = $conn->prepare("SELECT nguoi_dung_id FROM bai_viet WHERE id = ?");
            $stmt->execute([$bai_viet_id]);
            $chu_bai_viet = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($chu_bai_viet['nguoi_dung_id'] != $nguoi_dung_id) {
                $stmt = $conn->prepare("INSERT INTO thong_bao (nguoi_dung_id, loai, noi_dung, lien_ket_id, ngay_tao, da_doc) 
                                      VALUES (?, 'thich', ?, ?, NOW(), 0)");
                $stmt->execute([
                    $chu_bai_viet['nguoi_dung_id'],
                    $_SESSION['ho_ten'] . ' đã thích bài viết của bạn',
                    $bai_viet_id
                ]);
            }
        }
        
        // Lấy số lượt thích mới
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM thich WHERE bai_viet_id = ?");
        $stmt->execute([$bai_viet_id]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo json_encode([
            'status' => 'success',
            'action' => $action,
            'count' => $count
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ]);
    }
}
?> 