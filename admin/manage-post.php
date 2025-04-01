<?php
session_start();

// Kiểm tra yêu cầu AJAX
if (isset($_POST['fetch_posts']) && $_POST['fetch_posts'] === 'true') {
    // Lấy danh sách bài viết từ cơ sở dữ liệu
    require_once '../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->query("SELECT bv.id AS post_id, bv.noi_dung, bv.anh, nd.ho_ten, bv.ngay_dang ,bv.trang_thai FROM bai_viet bv JOIN nguoi_dung nd ON bv.nguoi_dung_id = nd.id ORDER BY bv.ngay_dang DESC");
    
    // Kiểm tra lỗi truy vấn SQL
    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi truy vấn cơ sở dữ liệu.']);
        exit;
    }
    
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Trả về dữ liệu JSON
    echo json_encode([
        'status' => 'success',
        'posts' => $posts
    ]);
    exit; 

} 

// Xử lý duyệt bài viết
if (isset($_POST['approve_post']) && isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];
    require_once '../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();

    // Cập nhật trạng thái bài viết thành "Đã duyệt"
    $stmt = $conn->prepare("UPDATE bai_viet SET trang_thai = 'da_duyet' WHERE id = :post_id");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Bài viết đã được duyệt!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra khi duyệt bài viết.'
        ]);
    }
    exit;
}
    if(isset($_POST['delete_post']) && isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];

    require_once '../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("DELETE FROM bai_viet WHERE id = :post_id");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Bài viết đã được xóa!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra khi xóa bài viết.'
        ]);
    }
    exit;
    
}
    include 'includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bài viết</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color: #007bff; color: white; }
        img { max-width: 100px; border-radius: 5px; }
        .delete-btn { background-color: red; color: white; padding: 5px 10px; border: none; cursor: pointer; }
        .delete-btn:hover { background-color: darkred; }
        .approve-btn { background-color: green; color: white; padding: 5px 10px; border: none; cursor: pointer; }
        .approve-btn:hover { background-color: darkgreen; }
        .content {
            margin-left: 220px; 
            padding: 20px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="content">
<h1>Quản lý bài viết</h1>
    <table id="posts-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nội dung</th>
                <th>Ảnh</th>
                <th>Người đăng</th>
                <th>Ngày đăng</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
</div>
        <tbody id="posts-body">

        </tbody>
    </table>

    <script>
    function loadPosts() {
        $.ajax({
            url: 'manage-post.php',
            type: 'POST',
            data: { fetch_posts: true },
            dataType: 'json',
            success: function(data) {
                if (data.status === 'success') {
                    let postsHtml = '';
                    data.posts.forEach(post => {
                        postsHtml += `<tr>
                            <td>${post.post_id}</td>
                            <td>${post.noi_dung}</td>
                            <td>${post.anh ? `<img src='../uploads/posts/${post.anh}' width='100'>` : 'Không có ảnh'}</td>
                            <td>${post.ho_ten}</td>
                            <td>${post.ngay_dang}</td>
                            <td>${post.trang_thai}</td>
                            <td>
                                <button class='delete-btn' data-id='${post.post_id}'>Xóa</button>
                                ${post.trang_thai === 'chua_duyet' ? `<button class='approve-btn' data-id='${post.post_id}'>Duyệt</button>` : ''}
                            </td>
                        </tr>`;
                    });
                    $('#posts-body').html(postsHtml);
                } else {
                    console.error('Lỗi tải bài viết:', data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Lỗi kết nối:', error);
            }
        });
    }
    $(document).on('click', '.approve-btn', function() {
    if (!confirm('Bạn có chắc chắn muốn duyệt bài viết này?')) return;

    let postId = $(this).data('id');
    $.ajax({
        url: 'manage-post.php',
        type: 'POST',
        data: { approve_post: true, post_id: postId },
        dataType: 'json',
        success: function(data) {
            if (data.status === 'success') {
                alert(data.message);
                loadPosts();
            } else {
                alert('Lỗi: ' + data.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Lỗi phản hồi từ server:', error);
            alert('Lỗi kết nối: ' + error);
        }
        });
    });

    $(document).on('click', '.delete-btn', function() {
        if (!confirm('Bạn có chắc chắn muốn xóa bài viết này?')) return;

        let postId = $(this).data('id');
        $.ajax({
            url: 'manage-post.php',
            type: 'POST',
            data: { delete_post: true, post_id: postId },
            dataType: 'json',
            success: function(data) {
                if (data.status === 'success') {
                    alert(data.message);
                    loadPosts();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Lỗi phản hồi từ server:', error);
                alert('Lỗi kết nối: ' + error);
            }
        });
    });

    $(document).ready(function() {
        loadPosts();
        setInterval(loadPosts, 5000);
    });
    </script>
</body>
</html>
