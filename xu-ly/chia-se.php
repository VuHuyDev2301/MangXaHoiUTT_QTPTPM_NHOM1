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
    $noi_dung = isset($_POST['noi_dung']) ? sanitizeInput($_POST['noi_dung']) : null;
    $nguoi_dung_id = $_SESSION['user_id'];
    
    try {
        // Kiểm tra bài viết tồn tại
        $stmt = $conn->prepare("SELECT nguoi_dung_id FROM bai_viet WHERE id = ?");
        $stmt->execute([$bai_viet_id]);
        $bai_viet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$bai_viet) {
            die(json_encode(['status' => 'error', 'message' => 'Bài viết không tồn tại']));
        }
        
        // Thêm bài viết chia sẻ
        $stmt = $conn->prepare("INSERT INTO bai_viet (nguoi_dung_id, noi_dung, chia_se_id, ngay_dang) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$nguoi_dung_id, $noi_dung, $bai_viet_id]);
        
        // Thêm thông báo cho chủ bài viết gốc
        if ($bai_viet['nguoi_dung_id'] != $nguoi_dung_id) {
            $stmt = $conn->prepare("INSERT INTO thong_bao (nguoi_dung_id, loai, noi_dung, lien_ket_id, ngay_tao) 
                                  VALUES (?, 'chia_se', ?, ?, NOW())");
            $stmt->execute([
                $bai_viet['nguoi_dung_id'],
                $_SESSION['ho_ten'] . ' đã chia sẻ bài viết của bạn',
                $bai_viet_id
            ]);
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Đã chia sẻ bài viết'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ]);
    }
}
?> 