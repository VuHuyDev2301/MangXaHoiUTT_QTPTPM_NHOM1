<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->getConnection();

    // Lấy và làm sạch dữ liệu đầu vào
    $ten_dang_nhap = sanitizeInput($_POST['ten_dang_nhap']);
    $email = sanitizeInput($_POST['email']);
    $ho_ten = sanitizeInput($_POST['ho_ten']);
    $mat_khau = $_POST['mat_khau'];
    $khoa = sanitizeInput($_POST['khoa']);
    $nam_hoc = sanitizeInput($_POST['nam_hoc']);

    try {
        // Kiểm tra tên đăng nhập đã tồn tại
        $stmt = $conn->prepare("SELECT id FROM nguoi_dung WHERE ten_dang_nhap = ?");
        $stmt->execute([$ten_dang_nhap]);
        if ($stmt->rowCount() > 0) {
            setFlashMessage('error', 'Tên đăng nhập đã tồn tại!');
            redirectTo('../index.php');
        }

        // Kiểm tra email đã tồn tại
        $stmt = $conn->prepare("SELECT id FROM nguoi_dung WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            setFlashMessage('error', 'Email đã được sử dụng!');
            redirectTo('../index.php');
        }

        // Mã hóa mật khẩu
        $mat_khau_hash = hashPassword($mat_khau);

        // Thêm người dùng mới
        $stmt = $conn->prepare("INSERT INTO nguoi_dung (ten_dang_nhap, email, ho_ten, mat_khau, khoa, nam_hoc) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ten_dang_nhap, $email, $ho_ten, $mat_khau_hash, $khoa, $nam_hoc]);

        setFlashMessage('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
        redirectTo('../index.php');

    } catch(PDOException $e) {
        setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        redirectTo('../index.php');
    }
}
?> 