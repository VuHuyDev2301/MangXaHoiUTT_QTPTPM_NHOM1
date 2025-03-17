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
    
    $ho_ten = sanitizeInput($_POST['ho_ten']);
    $email = sanitizeInput($_POST['email']);
    $khoa = sanitizeInput($_POST['khoa']);
    $nam_hoc = sanitizeInput($_POST['nam_hoc']);
    $gioi_thieu = sanitizeInput($_POST['gioi_thieu']);
    
    try {
        // Kiểm tra email đã tồn tại
        $stmt = $conn->prepare("SELECT id FROM nguoi_dung WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            die(json_encode(['status' => 'error', 'message' => 'Email đã được sử dụng bởi tài khoản khác']));
        }
        
        // Cập nhật thông tin người dùng
        $stmt = $conn->prepare("UPDATE nguoi_dung SET ho_ten = ?, email = ?, khoa = ?, nam_hoc = ?, gioi_thieu = ? WHERE id = ?");
        $stmt->execute([$ho_ten, $email, $khoa, $nam_hoc, $gioi_thieu, $_SESSION['user_id']]);
        
        // Cập nhật session
        $_SESSION['ho_ten'] = $ho_ten;
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Đã cập nhật thông tin cá nhân'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ]);
    }
}

// Xử lý upload ảnh đại diện
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['anh_dai_dien'])) {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        // Xử lý upload ảnh
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['anh_dai_dien']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            // Tạo tên file ngẫu nhiên để tránh trùng lặp
            $newname = uniqid() . '.' . $filetype;
            $upload_path = '../uploads/avatars/' . $newname;
            
            if (move_uploaded_file($_FILES['anh_dai_dien']['tmp_name'], $upload_path)) {
                // Xóa ảnh cũ nếu không phải ảnh mặc định
                $stmt = $conn->prepare("SELECT anh_dai_dien FROM nguoi_dung WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $old_avatar = $stmt->fetch(PDO::FETCH_COLUMN);
                
                if ($old_avatar != 'default.jpg') {
                    $old_path = '../uploads/avatars/' . $old_avatar;
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
                
                // Cập nhật ảnh đại diện mới
                $stmt = $conn->prepare("UPDATE nguoi_dung SET anh_dai_dien = ? WHERE id = ?");
                $stmt->execute([$newname, $_SESSION['user_id']]);
                
                // Cập nhật session
                $_SESSION['anh_dai_dien'] = $newname;
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Đã cập nhật ảnh đại diện',
                    'anh_dai_dien' => $newname
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Không thể upload ảnh'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Định dạng file không được hỗ trợ'
            ]);
        }
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ]);
    }
}
?> 