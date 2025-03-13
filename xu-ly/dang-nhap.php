<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->getConnection();

    // Lấy và làm sạch dữ liệu đầu vào
    $ten_dang_nhap = sanitizeInput($_POST['ten_dang_nhap']);
    $mat_khau = $_POST['mat_khau'];

    try {
        // Kiểm tra thông tin đăng nhập
        $stmt = $conn->prepare("SELECT id, mat_khau, ho_ten, anh_dai_dien, trang_thai FROM nguoi_dung WHERE ten_dang_nhap = ?");
        $stmt->execute([$ten_dang_nhap]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user['trang_thai'] == 'khoa') {
                setFlashMessage('error', 'Tài khoản của bạn đã bị khóa!');
                redirectTo('../index.php');
            }

            if (verifyPassword($mat_khau, $user['mat_khau'])) {
                // Đăng nhập thành công
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['ho_ten'] = $user['ho_ten'];
                $_SESSION['anh_dai_dien'] = $user['anh_dai_dien'];
                
                // Cập nhật trạng thái hoạt động
                $stmt = $conn->prepare("UPDATE nguoi_dung SET trang_thai = 'hoat_dong' WHERE id = ?");
                $stmt->execute([$user['id']]);

                redirectTo('../trang-chu.php');
            } else {
                setFlashMessage('error', 'Mật khẩu không chính xác!');
                redirectTo('../index.php');
            }
        } else {
            setFlashMessage('error', 'Tên đăng nhập không tồn tại!');
            redirectTo('../index.php');
        }

    } catch(PDOException $e) {
        setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        redirectTo('../index.php');
    }
}
?> 