<?php
session_start();
require_once 'includes/functions.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    redirectTo('index.php');
}

$db = new Database();
$conn = $db->getConnection();

// Lấy danh sách bài viết
try {
    $stmt = $conn->prepare("
        SELECT bv.*, nd.ho_ten, nd.anh_dai_dien,
        (SELECT COUNT(*) FROM thich WHERE bai_viet_id = bv.id) as so_luot_thich,
        (SELECT COUNT(*) FROM binh_luan WHERE bai_viet_id = bv.id) as so_binh_luan,
        (SELECT chia_se_id FROM bai_viet WHERE id = bv.id) as chia_se_id
        FROM bai_viet bv 
        JOIN nguoi_dung nd ON bv.nguoi_dung_id = nd.id
        ORDER BY bv.ngay_dang DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

// Lấy số lượng lời mời kết bạn
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM ket_ban 
    WHERE nguoi_nhan_id = ? AND trang_thai = 'cho_duyet'
");
$stmt->execute([$_SESSION['user_id']]);
$loi_moi_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTT Social - Trang chủ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/trang-chu.css">
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="container main-container">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-3">
                <?php include 'includes/sidebar.php'; ?>
            </div>

            <!-- Main Feed -->
            <div class="col-md-6">
                <!-- Create Post -->
                <div class="create-post">
                    <div class="create-post-header">
                        <img src="uploads/avatars/<?php echo $_SESSION['anh_dai_dien'] ?? 'default.jpg'; ?>" 
                             alt="Avatar" class="avatar">
                        <div class="post-input">
                            <button class="create-post-btn" data-bs-toggle="modal" data-bs-target="#createPostModal">
                                Bạn đang nghĩ gì?
                            </button>
                        </div>
                    </div>
                    <div class="create-post-actions">
                        <button class="action-btn">
                            <i class="fas fa-images"></i> Ảnh/Video
                        </button>
                        <button class="action-btn">
                            <i class="fas fa-user-tag"></i> Gắn thẻ bạn bè
                        </button>
                        <button class="action-btn">
                            <i class="fas fa-map-marker-alt"></i> Check in
                        </button>
                    </div>
                </div>

                <!-- Posts Feed -->
                <div class="posts-container">
                    <?php
                    foreach ($posts as $post):
                    ?>
                    <div class="post" data-aos="fade-up" data-post-id="<?php echo $post['id']; ?>" id="post-<?php echo $post['id']; ?>">
                        <div class="post-header">
                            <img src="uploads/avatars/<?php echo $post['anh_dai_dien']; ?>" 
                                 alt="Avatar" class="avatar">
                            <div class="post-info">
                                <h6><?php echo $post['ho_ten']; ?></h6>
                                <span class="time">
                                    <?php echo time_elapsed_string($post['ngay_dang']); ?>
                                </span>
                            </div>
                            <?php if ($post['nguoi_dung_id'] == $_SESSION['user_id']): ?>
                            <div class="post-actions-dropdown">
                                <button class="btn-more" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button class="dropdown-item btn-delete-post" data-post-id="<?php echo $post['id']; ?>">
                                            <i class="fas fa-trash-alt"></i> Xóa bài viết
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item btn-edit-post" data-post-id="<?php echo $post['id']; ?>">
                                            <i class="fas fa-edit"></i> Chỉnh sửa
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="post-content">
                            <p><?php echo $post['noi_dung']; ?></p>
                            <?php if ($post['anh']): ?>
                                <img src="uploads/posts/<?php echo $post['anh']; ?>" 
                                     alt="Post image" class="post-image">
                            <?php endif; ?>
                        </div>
                        
                        <div class="post-stats">
                            <span><i class="fas fa-heart"></i> <?php echo $post['so_luot_thich']; ?></span>
                            <span><i class="fas fa-comment"></i> <?php echo $post['so_binh_luan']; ?></span>
                        </div>
                        
                        <div class="post-actions">
                            <button class="action-btn like-btn" data-post-id="<?php echo $post['id']; ?>">
                                <i class="far fa-heart"></i> Thích
                            </button>
                            <button class="action-btn comment-btn" data-post-id="<?php echo $post['id']; ?>">
                                <i class="far fa-comment"></i> Bình luận
                            </button>
                            <button class="action-btn share-btn" data-post-id="<?php echo $post['id']; ?>">
                                <i class="far fa-share-square"></i> Chia sẻ
                            </button>
                        </div>
                        
                        <div class="post-comments">
                            <div class="comments-container" style="display: none;">
                                <!-- Hiển thị bình luận -->
                                <div class="comments-loading">
                                    <i class="fas fa-spinner fa-spin"></i> Đang tải bình luận...
                                </div>
                                <div class="comments-list"></div>
                            </div>
                            <form class="comment-form" data-post-id="<?php echo $post['id']; ?>">
                                <div class="input-group">
                                    <img src="uploads/avatars/<?php echo $_SESSION['anh_dai_dien']; ?>" alt="Avatar" class="avatar">
                                    <input type="text" name="noi_dung" class="form-control comment-input" placeholder="Viết bình luận...">
                                    <button type="submit" class="btn btn-primary">Gửi</button>
                                </div>
                            </form>
                        </div>

                        <?php if ($post['chia_se_id']): ?>
                            <div class="shared-post">
                                <?php
                                // Lấy thông tin bài viết gốc
                                $stmt = $conn->prepare("
                                    SELECT bv.*, nd.ho_ten, nd.anh_dai_dien 
                                    FROM bai_viet bv 
                                    JOIN nguoi_dung nd ON bv.nguoi_dung_id = nd.id 
                                    WHERE bv.id = ?
                                ");
                                $stmt->execute([$post['chia_se_id']]);
                                $original_post = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                if ($original_post):
                                ?>
                                    <div class="original-post">
                                        <div class="post-header">
                                            <img src="uploads/avatars/<?php echo $original_post['anh_dai_dien']; ?>" 
                                                 alt="Avatar" class="avatar">
                                            <div class="post-info">
                                                <h6><?php echo $original_post['ho_ten']; ?></h6>
                                                <span class="time">
                                                    <?php echo time_elapsed_string($original_post['ngay_dang']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="post-content">
                                            <p><?php echo $original_post['noi_dung']; ?></p>
                                            <?php if ($original_post['anh']): ?>
                                                <img src="uploads/posts/<?php echo $original_post['anh']; ?>" 
                                                     alt="Post image" class="post-image">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="col-md-3">
                <div class="sidebar right-sidebar">
                    <div class="section-title">
                        <h5>Bạn bè trực tuyến</h5>
                    </div>
                    <div class="online-friends">
                        <!-- Online friends will be loaded here -->
                    </div>
                    
                    <div class="section-title">
                        <h5>Lời mời kết bạn</h5>
                    </div>
                    <div class="friend-requests">
                        <!-- Friend requests will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Post Modal -->
    <div class="modal fade" id="createPostModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tạo bài viết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createPostForm" action="xu-ly/dang-bai.php" method="POST" enctype="multipart/form-data">
                        <textarea name="noi_dung" placeholder="Bạn đang nghĩ gì?" required></textarea>
                        <div class="image-upload">
                            <label for="postImage">
                                <i class="fas fa-image"></i> Thêm ảnh
                            </label>
                            <input type="file" id="postImage" name="anh" accept="image/*">
                        </div>
                        <div id="imagePreview"></div>
                        <button type="submit" class="btn btn-primary w-100">Đăng bài</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Post Modal -->
    <div class="modal fade" id="editPostModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa bài viết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editPostForm" enctype="multipart/form-data">
                        <input type="hidden" name="bai_viet_id" id="edit_bai_viet_id">
                        <textarea name="noi_dung" id="edit_noi_dung" placeholder="Nội dung bài viết" required></textarea>
                        <div class="image-upload">
                            <label for="editPostImage">
                                <i class="fas fa-image"></i> Thay đổi ảnh
                            </label>
                            <input type="file" id="editPostImage" name="anh" accept="image/*">
                        </div>
                        <div id="editImagePreview"></div>
                        <button type="submit" class="btn btn-primary w-100">Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Post Modal -->
    <div class="modal fade" id="sharePostModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chia sẻ bài viết</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="sharePostForm">
                        <input type="hidden" name="bai_viet_id" id="share_bai_viet_id">
                        <div class="share-preview">
                            <!-- Bài viết gốc sẽ được hiển thị ở đây -->
                        </div>
                        <textarea name="noi_dung" placeholder="Viết gì đó..." class="form-control mb-3"></textarea>
                        <button type="submit" class="btn btn-primary w-100">Chia sẻ ngay</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="assets/js/trang-chu.js"></script>
</body>
</html> 