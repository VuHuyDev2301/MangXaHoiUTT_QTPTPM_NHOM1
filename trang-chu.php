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
        (SELECT COUNT(*) FROM binh_luan WHERE bai_viet_id = bv.id) as so_binh_luan
        FROM bai_viet bv 
        JOIN nguoi_dung nd ON bv.nguoi_dung_id = nd.id
        ORDER BY bv.ngay_dang DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
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
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="assets/images/logo.png" alt="UTT Social" height="40">
            </a>
            
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Tìm kiếm...">
            </div>

            <div class="nav-right">
                <div class="nav-user">
                    <img src="uploads/avatars/<?php echo $_SESSION['anh_dai_dien'] ?? 'default.jpg'; ?>" 
                         alt="Avatar" class="avatar">
                    <span class="username"><?php echo $_SESSION['ho_ten']; ?></span>
                </div>
                
                <div class="nav-notifications">
                    <div class="icon-badge-container">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                </div>
                
                <div class="nav-messages">
                    <div class="icon-badge-container">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">5</span>
                    </div>
                </div>
                
                <a href="xu-ly/dang-xuat.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container main-container">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-3">
                <div class="sidebar left-sidebar">
                    <div class="user-profile">
                        <img src="uploads/avatars/<?php echo $_SESSION['anh_dai_dien'] ?? 'default.jpg'; ?>" 
                             alt="Profile" class="profile-image">
                        <h4><?php echo $_SESSION['ho_ten']; ?></h4>
                        <p>Sinh viên UTT</p>
                    </div>
                    
                    <ul class="sidebar-menu">
                        <li class="active">
                            <a href="#"><i class="fas fa-home"></i> Trang chủ</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-user-friends"></i> Bạn bè</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-images"></i> Ảnh</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-calendar-alt"></i> Sự kiện</a>
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-cog"></i> Cài đặt</a>
                        </li>
                    </ul>
                </div>
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
                    <div class="post" data-aos="fade-up">
                        <div class="post-header">
                            <img src="uploads/avatars/<?php echo $post['anh_dai_dien']; ?>" 
                                 alt="Avatar" class="avatar">
                            <div class="post-info">
                                <h6><?php echo $post['ho_ten']; ?></h6>
                                <span class="time">
                                    <?php echo time_elapsed_string($post['ngay_dang']); ?>
                                </span>
                            </div>
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
                            <button class="action-btn comment-btn">
                                <i class="far fa-comment"></i> Bình luận
                            </button>
                            <button class="action-btn share-btn">
                                <i class="far fa-share-square"></i> Chia sẻ
                            </button>
                        </div>
                        
                        <div class="comments-section">
                            <!-- Comments will be loaded here -->
                        </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="assets/js/trang-chu.js"></script>
</body>
</html> 