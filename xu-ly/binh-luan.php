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
    $noi_dung = sanitizeInput($_POST['noi_dung']);
    $nguoi_dung_id = $_SESSION['user_id'];
    
    try {
        // Thêm bình luận
        $stmt = $conn->prepare("INSERT INTO binh_luan (bai_viet_id, nguoi_dung_id, noi_dung, ngay_binh_luan) 
                              VALUES (?, ?, ?, NOW())");
        $stmt->execute([$bai_viet_id, $nguoi_dung_id, $noi_dung]);
        
        // Thêm thông báo cho chủ bài viết
        $stmt = $conn->prepare("SELECT nguoi_dung_id FROM bai_viet WHERE id = ?");
        $stmt->execute([$bai_viet_id]);
        $chu_bai_viet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($chu_bai_viet['nguoi_dung_id'] != $nguoi_dung_id) {
            $stmt = $conn->prepare("INSERT INTO thong_bao (nguoi_dung_id, loai, noi_dung, lien_ket_id, ngay_tao) 
                                  VALUES (?, 'binh_luan', ?, ?, NOW())");
            $stmt->execute([
                $chu_bai_viet['nguoi_dung_id'],
                $_SESSION['ho_ten'] . ' đã bình luận về bài viết của bạn',
                $bai_viet_id
            ]);
        }
        
        // Trả về thông tin bình luận mới
        echo json_encode([
            'status' => 'success',
            'comment' => [
                'id' => $conn->lastInsertId(),
                'noi_dung' => $noi_dung,
                'ho_ten' => $_SESSION['ho_ten'],
                'anh_dai_dien' => $_SESSION['anh_dai_dien'],
                'ngay_binh_luan' => date('Y-m-d H:i:s')
            ]
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ]);
    }
}

// Lấy danh sách bình luận
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $bai_viet_id = $_GET['bai_viet_id'];
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT bl.*, nd.ho_ten, nd.anh_dai_dien
            FROM binh_luan bl
            JOIN nguoi_dung nd ON bl.nguoi_dung_id = nd.id
            WHERE bl.bai_viet_id = ?
            ORDER BY bl.ngay_binh_luan DESC
        ");
        $stmt->execute([$bai_viet_id]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'comments' => $comments
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ]);
    }
}
?> 