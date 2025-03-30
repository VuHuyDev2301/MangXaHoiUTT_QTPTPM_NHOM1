<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirectTo('../trang-chu.php');
}

$db = new Database();
$conn = $db->getConnection();

// Get user info for chat header
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT anh_dai_dien, ho_ten, trang_thai FROM nguoi_dung WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userInfo) {
        echo "<div class='chat-header-msg'>";
        echo "<div class='user-info-msg'>";
        echo "<div class='user-info-msg' id='chat-header-info'>";
        echo "<img src='uploads/avatars/" . htmlspecialchars($userInfo['anh_dai_dien']) . "' alt='Avatar'>";
        echo "<span>" . htmlspecialchars($userInfo['ho_ten']) . "</span>";
        $status_html = '';
        if ($userInfo['trang_thai'] === 'hoat_dong') {
            $status_html = '<span class="status online">Đang hoạt động</span>';
        } else {
            $last_activity = strtotime($userInfo['trang_thai']);
            $current_time = time();
            $diff_minutes = round(($current_time - $last_activity) / 60);
            
            if ($diff_minutes < 60) {
            $status_html = '<span class="status">Hoạt động ' . $diff_minutes . ' phút trước</span>';
            } elseif ($diff_minutes < 720) { // 12 hours
            $hours = floor($diff_minutes / 60);
            $status_html = '<span class="status">Hoạt động ' . $hours . ' giờ trước</span>';
            } else {
            $status_html = '<span class="status offline">Không hoạt động</span>';
            }
        }
        echo $status_html;
        echo "</div></div></div>";
    }
}