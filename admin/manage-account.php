<?php
session_start();
require_once '../config/database.php';
include 'includes/sidebar.php';

$db = new Database();
$conn = $db->getConnection();

// Lấy tất cả tài khoản người dùng từ cơ sở dữ liệu
$stmt = $conn->query("SELECT id, ten_dang_nhap, ho_ten, anh_dai_dien, trang_thai FROM nguoi_dung");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Kiểm tra yêu cầu POST thay đổi trạng thái tài khoản
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'change_status') {

    $user_id = $_POST['user_id'];
    $status = $_POST['status'];

    $db = new Database();
    $conn = $db->getConnection();

    try {
        // Cập nhật trạng thái tài khoản
        $stmt = $conn->prepare("UPDATE nguoi_dung SET trang_thai = :status WHERE id = :user_id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "Trạng thái tài khoản đã được thay đổi!";
        } else {
            echo "Lỗi khi thay đổi trạng thái!";
        }
    } catch (PDOException $e) {
        echo "Lỗi: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Quản lý tài khoản</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #007bff; color: white; }
        img { max-width: 100px;width:50px; border-radius: 50%; }
        .lock-btn { background-color: red; color: white; padding: 5px 10px; border: none; cursor: pointer; }
        .lock-btn:hover { background-color: darkred; }
        .unlock-btn { background-color: green; color: white; padding: 5px 10px; border: none; cursor: pointer; }
        .unlock-btn:hover { background-color: darkgreen; }
        .content {
            margin-left: 220px; /* Dịch phần nội dung ra khỏi sidebar */
            padding: 20px;
        }

    </style>
</head>
<body>
<div class ="content">
<h1>Quản lý tài khoản người dùng</h1>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên đăng nhập</th>
            <th>Họ tên</th>
            <th>Ảnh đại diện</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= $user['ten_dang_nhap'] ?></td>
                <td><?= $user['ho_ten'] ?></td>
                <td><img src="../uploads/avatars/<?= $user['anh_dai_dien'] ?>" alt="Avatar"></td>
                <td><?= ucfirst($user['trang_thai']) ?></td>
                <td>
                    <?php if ($user['trang_thai'] == 'hoat_dong'): ?>
                        <button class="lock-btn" onclick="changeStatus(<?= $user['id'] ?>, 'khoa')">Khóa</button>
                    <?php else: ?>
                        <button class="unlock-btn" onclick="changeStatus(<?= $user['id'] ?>, 'hoat_dong')">Mở khóa</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<script>
    function changeStatus(userId, status) {
        if (confirm("Bạn có chắc chắn muốn thay đổi trạng thái tài khoản này?")) {
            $.ajax({
                url: 'manage-account.php',
                type: 'POST',
                data: {
                    action: 'change_status',
                    user_id: userId,
                    status: status
                },
                success: function(response) {
                    location.reload();  
                },
                error: function(xhr, status, error) {
                    alert('Có lỗi xảy ra: ' + error);
                }
            });
        }
    }
</script>
</body>
</html>
