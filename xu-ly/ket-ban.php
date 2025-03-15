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

// Gửi lời mời kết bạn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'gui_loi_moi') {
    $nguoi_nhan_id = $_POST['nguoi_nhan_id'];
    
    try {
        // Kiểm tra xem đã gửi lời mời trước đó chưa
        $stmt = $conn->prepare("SELECT id, trang_thai FROM ket_ban 
                              WHERE (nguoi_gui_id = ? AND nguoi_nhan_id = ?) 
                              OR (nguoi_gui_id = ? AND nguoi_nhan_id = ?)");
        $stmt->execute([$nguoi_dung_id, $nguoi_nhan_id, $nguoi_nhan_id, $nguoi_dung_id]);
        $ket_ban = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ket_ban) {
            if ($ket_ban['trang_thai'] == 'da_dong_y') {
                die(json_encode(['status' => 'error', 'message' => 'Các bạn đã là bạn bè']));
            } elseif ($ket_ban['trang_thai'] == 'cho_duyet') {
                die(json_encode(['status' => 'error', 'message' => 'Đã gửi lời mời kết bạn trước đó']));
            } elseif ($ket_ban['trang_thai'] == 'tu_choi') {
                // Cập nhật lại trạng thái nếu đã từ chối trước đó
                $stmt = $conn->prepare("UPDATE ket_ban SET trang_thai = 'cho_duyet', ngay_gui = NOW() WHERE id = ?");
                $stmt->execute([$ket_ban['id']]);
            }
        } else {
            // Thêm lời mời kết bạn mới
            $stmt = $conn->prepare("INSERT INTO ket_ban (nguoi_gui_id, nguoi_nhan_id, trang_thai, ngay_gui) 
                                  VALUES (?, ?, 'cho_duyet', NOW())");
            $stmt->execute([$nguoi_dung_id, $nguoi_nhan_id]);
        }
        
        // Thêm thông báo
        $stmt = $conn->prepare("INSERT INTO thong_bao (nguoi_dung_id, loai, noi_dung, lien_ket_id, ngay_tao) 
                              VALUES (?, 'ket_ban', ?, ?, NOW())");
        $stmt->execute([
            $nguoi_nhan_id,
            $_SESSION['ho_ten'] . ' đã gửi lời mời kết bạn',
            $nguoi_dung_id
        ]);
        
        echo json_encode(['status' => 'success', 'message' => 'Đã gửi lời mời kết bạn']);
        
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}

// Chấp nhận lời mời kết bạn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'dong_y') {
    $ket_ban_id = $_POST['ket_ban_id'];
    
    try {
        // Kiểm tra quyền
        $stmt = $conn->prepare("SELECT * FROM ket_ban WHERE id = ? AND nguoi_nhan_id = ? AND trang_thai = 'cho_duyet'");
        $stmt->execute([$ket_ban_id, $nguoi_dung_id]);
        $ket_ban = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ket_ban) {
            die(json_encode(['status' => 'error', 'message' => 'Không tìm thấy lời mời kết bạn']));
        }
        
        // Cập nhật trạng thái
        $stmt = $conn->prepare("UPDATE ket_ban SET trang_thai = 'da_dong_y' WHERE id = ?");
        $stmt->execute([$ket_ban_id]);
        
        // Thêm thông báo
        $stmt = $conn->prepare("INSERT INTO thong_bao (nguoi_dung_id, loai, noi_dung, lien_ket_id, ngay_tao) 
                              VALUES (?, 'ket_ban', ?, ?, NOW())");
        $stmt->execute([
            $ket_ban['nguoi_gui_id'],
            $_SESSION['ho_ten'] . ' đã chấp nhận lời mời kết bạn của bạn',
            $nguoi_dung_id
        ]);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Đã chấp nhận lời mời kết bạn',
            'friend' => [
                'id' => $ket_ban['nguoi_gui_id'],
                'ket_ban_id' => $ket_ban_id
            ]
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}

// Từ chối lời mời kết bạn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'tu_choi') {
    $ket_ban_id = $_POST['ket_ban_id'];
    
    try {
        // Kiểm tra quyền
        $stmt = $conn->prepare("SELECT * FROM ket_ban WHERE id = ? AND nguoi_nhan_id = ? AND trang_thai = 'cho_duyet'");
        $stmt->execute([$ket_ban_id, $nguoi_dung_id]);
        
        if ($stmt->rowCount() == 0) {
            die(json_encode(['status' => 'error', 'message' => 'Không tìm thấy lời mời kết bạn']));
        }
        
        // Cập nhật trạng thái
        $stmt = $conn->prepare("UPDATE ket_ban SET trang_thai = 'tu_choi' WHERE id = ?");
        $stmt->execute([$ket_ban_id]);
        
        echo json_encode(['status' => 'success', 'message' => 'Đã từ chối lời mời kết bạn']);
        
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}

// Hủy kết bạn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'huy_ket_ban') {
    $ban_be_id = $_POST['ban_be_id'];
    
    try {
        // Xóa kết bạn
        $stmt = $conn->prepare("DELETE FROM ket_ban 
                              WHERE (nguoi_gui_id = ? AND nguoi_nhan_id = ?) 
                              OR (nguoi_gui_id = ? AND nguoi_nhan_id = ?)");
        $stmt->execute([$nguoi_dung_id, $ban_be_id, $ban_be_id, $nguoi_dung_id]);
        
        echo json_encode(['status' => 'success', 'message' => 'Đã hủy kết bạn']);
        
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}

// Lấy danh sách lời mời kết bạn
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'loi_moi') {
    try {
        $stmt = $conn->prepare("
            SELECT kb.id, kb.nguoi_gui_id, kb.ngay_gui, nd.ho_ten, nd.anh_dai_dien
            FROM ket_ban kb
            JOIN nguoi_dung nd ON kb.nguoi_gui_id = nd.id
            WHERE kb.nguoi_nhan_id = ? AND kb.trang_thai = 'cho_duyet'
            ORDER BY kb.ngay_gui DESC
        ");
        $stmt->execute([$nguoi_dung_id]);
        $loi_moi = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'loi_moi' => $loi_moi]);
        
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}

// Lấy danh sách bạn bè
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'ban_be') {
    try {
        $stmt = $conn->prepare("
            SELECT 
                CASE 
                    WHEN kb.nguoi_gui_id = ? THEN kb.nguoi_nhan_id
                    ELSE kb.nguoi_gui_id
                END as ban_be_id,
                nd.ho_ten, nd.anh_dai_dien, nd.trang_thai
            FROM ket_ban kb
            JOIN nguoi_dung nd ON (
                CASE 
                    WHEN kb.nguoi_gui_id = ? THEN kb.nguoi_nhan_id
                    ELSE kb.nguoi_gui_id
                END = nd.id
            )
            WHERE (kb.nguoi_gui_id = ? OR kb.nguoi_nhan_id = ?) 
            AND kb.trang_thai = 'da_dong_y'
            ORDER BY nd.ho_ten
        ");
        $stmt->execute([$nguoi_dung_id, $nguoi_dung_id, $nguoi_dung_id, $nguoi_dung_id]);
        $ban_be = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'ban_be' => $ban_be]);
        
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
    }
}
?> 