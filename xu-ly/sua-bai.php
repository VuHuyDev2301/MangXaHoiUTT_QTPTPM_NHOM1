<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']));
}

// Xử lý lấy thông tin bài viết để chỉnh sửa
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $bai_viet_id = $_GET['bai_viet_id'];
    $nguoi_dung_id = $_SESSION['user_id'];
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM bai_viet WHERE id = ? AND nguoi_dung_id = ?");
        $stmt->execute([$bai_viet_id, $nguoi_dung_id]);
        $bai_viet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($bai_viet) {
            echo json_encode([
                'status' => 'success',
                'data' => $bai_viet
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Không tìm thấy bài viết hoặc không có quyền chỉnh sửa'
            ]);
        }
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ]);
    }
}

// Xử lý cập nhật bài viết
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new Database();
    $conn = $db->getConnection();
    
    $bai_viet_id = $_POST['bai_viet_id'];
    $noi_dung = sanitizeInput($_POST['noi_dung']);
    $nguoi_dung_id = $_SESSION['user_id'];
    
    try {
        // Kiểm tra quyền chỉnh sửa
        $stmt = $conn->prepare("SELECT nguoi_dung_id, anh FROM bai_viet WHERE id = ?");
        $stmt->execute([$bai_viet_id]);
        $bai_viet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($bai_viet['nguoi_dung_id'] != $nguoi_dung_id) {
            die(json_encode(['status' => 'error', 'message' => 'Không có quyền chỉnh sửa bài viết này']));
        }
        
        // Xử lý upload ảnh mới nếu có
        $anh = $bai_viet['anh']; // Giữ nguyên ảnh cũ nếu không upload ảnh mới
        if (isset($_FILES['anh']) && $_FILES['anh']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['anh']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                // Xóa ảnh cũ nếu có
                if ($bai_viet['anh']) {
                    $anh_path = "../uploads/posts/" . $bai_viet['anh'];
                    if (file_exists($anh_path)) {
                        unlink($anh_path);
                    }
                }
                
                // Upload ảnh mới
                $newname = uniqid() . '.' . $filetype;
                $upload_path = '../uploads/posts/' . $newname;
                
                if (move_uploaded_file($_FILES['anh']['tmp_name'], $upload_path)) {
                    $anh = $newname;
                }
            }
        }
        
        // Cập nhật bài viết
        $stmt = $conn->prepare("UPDATE bai_viet SET noi_dung = ?, anh = ? WHERE id = ?");
        $stmt->execute([$noi_dung, $anh, $bai_viet_id]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Đã cập nhật bài viết',
            'data' => [
                'noi_dung' => $noi_dung,
                'anh' => $anh
            ]
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
        ]);
    }
}
?> 