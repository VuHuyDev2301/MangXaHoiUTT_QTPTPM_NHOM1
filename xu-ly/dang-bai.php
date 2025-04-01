<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirectTo('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $noi_dung = sanitizeInput($_POST['noi_dung']);
    $nguoi_dung_id = $_SESSION['user_id'];
    
    try {
        // Xử lý upload ảnh nếu có
        $anh = null;
        if (isset($_FILES['anh']) && $_FILES['anh']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['anh']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                // Tạo tên file ngẫu nhiên để tránh trùng lặp
                $newname = uniqid() . '.' . $filetype;
                $upload_path = '../uploads/posts/' . $newname;
                
                if (move_uploaded_file($_FILES['anh']['tmp_name'], $upload_path)) {
                    $anh = $newname;
                }
            }
        }
        
        // Thêm bài viết vào database
        $stmt = $conn->prepare("INSERT INTO bai_viet (nguoi_dung_id, noi_dung, anh, ngay_dang) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$nguoi_dung_id, $noi_dung, $anh]);
        
        // Trả về JSON response
        echo json_encode([
            'status' => 'success',
            'message' => 'Đăng bài thành công!'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ]);
    }
}

?> 