<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']));
}

$db = new Database();
$conn = $db->getConnection();
$nguoi_dung_id = $_SESSION['user_id'];

// Lấy danh sách thông báo
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'get_thong_bao') {
    try {
        // Truy vấn đơn giản để lấy thông báo
        $stmt = $conn->prepare("
            SELECT tb.*, nd.anh_dai_dien, nd.ho_ten
            FROM thong_bao tb
            LEFT JOIN nguoi_dung nd ON 
                CASE 
                    WHEN tb.loai = 'ket_ban' THEN nd.id = tb.lien_ket_id
                    WHEN tb.loai = 'thich' OR tb.loai = 'binh_luan' THEN nd.id = (
                        SELECT nguoi_dung_id FROM bai_viet WHERE id = tb.lien_ket_id
                    )
                    WHEN tb.loai = 'tin_nhan' THEN nd.id = tb.lien_ket_id
                END
            WHERE tb.nguoi_dung_id = ?
            ORDER BY tb.ngay_tao DESC
            LIMIT 20
        ");
        $stmt->execute([$nguoi_dung_id]);
        $thong_bao = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Lấy số lượng thông báo chưa đọc
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM thong_bao WHERE nguoi_dung_id = ? AND da_doc = 0");
        $stmt->execute([$nguoi_dung_id]);
        $chua_doc = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo json_encode([
            'status' => 'success', 
            'thong_bao' => $thong_bao,
            'chua_doc' => $chua_doc
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}

// Đánh dấu đã đọc thông báo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'danh_dau_da_doc') {
    $thong_bao_id = $_POST['thong_bao_id'] ?? null;
    
    try {
        if ($thong_bao_id) {
            // Đánh dấu một thông báo cụ thể
            $stmt = $conn->prepare("UPDATE thong_bao SET da_doc = 1 WHERE id = ? AND nguoi_dung_id = ?");
            $stmt->execute([$thong_bao_id, $nguoi_dung_id]);
        } else {
            // Đánh dấu tất cả thông báo
            $stmt = $conn->prepare("UPDATE thong_bao SET da_doc = 1 WHERE nguoi_dung_id = ?");
            $stmt->execute([$nguoi_dung_id]);
        }
        
        echo json_encode(['status' => 'success']);
        
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}
?> 