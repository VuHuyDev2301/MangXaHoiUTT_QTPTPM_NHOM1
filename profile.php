<?php
session_start();
require_once 'includes/functions.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    redirectTo('index.php');
}

$db = new Database();
$conn = $db->getConnection();

// Lấy ID người dùng từ URL hoặc sử dụng ID người dùng hiện tại
$profile_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];

// Lấy thông tin người dùng
try {
    $stmt = $conn->prepare("
        SELECT * FROM nguoi_dung WHERE id = ?
    ");
    $stmt->execute([$profile_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        setFlashMessage('error', 'Không tìm thấy người dùng');
        redirectTo('trang-chu.php');
    }
} catch(PDOException $e) {
    setFlashMessage('error', 'Có lỗi xảy ra: ' . $e->getMessage());
    redirectTo('trang-chu.php');
}

// Kiểm tra trạng thái kết bạn
$friend_status = null;
if ($profile_id != $_SESSION['user_id']) {
    try {
        $stmt = $conn->prepare("
            SELECT id, trang_thai, nguoi_gui_id 
            FROM ket_ban 
            WHERE (nguoi_gui_id = ? AND nguoi_nhan_id = ?) 
               OR (nguoi_gui_id = ? AND nguoi_nhan_id = ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $profile_id, $profile_id, $_SESSION['user_id']]);
        $ket_ban = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ket_ban) {
            $friend_status = $ket_ban['trang_thai'];
            $is_sender = $ket_ban['nguoi_gui_id'] == $_SESSION['user_id'];
        }
    } catch(PDOException $e) {
        // Xử lý lỗi
    }
}

// Lấy bài viết của người dùng
try {
    $stmt = $conn->prepare("
        SELECT bv.*, nd.ho_ten, nd.anh_dai_dien,
        (SELECT COUNT(*) FROM thich WHERE bai_viet_id = bv.id) as so_luot_thich,
        (SELECT COUNT(*) FROM binh_luan WHERE bai_viet_id = bv.id) as so_binh_luan
        FROM bai_viet bv 
        JOIN nguoi_dung nd ON bv.nguoi_dung_id = nd.id
        WHERE bv.nguoi_dung_id = ?
        ORDER BY bv.ngay_dang DESC
    ");
    $stmt->execute([$profile_id]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Xử lý lỗi
}

// Lấy số lượng bạn bè
try {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM ket_ban
        WHERE (nguoi_gui_id = ? OR nguoi_nhan_id = ?)
        AND trang_thai = 'da_dong_y'
    ");
    $stmt->execute([$profile_id, $profile_id]);
    $friend_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
} catch(PDOException $e) {
    $friend_count = 0;
}

// Lấy danh sách bạn bè để hiển thị
try {
    $stmt = $conn->prepare("
        SELECT 
            CASE 
                WHEN kb.nguoi_gui_id = ? THEN kb.nguoi_nhan_id
                ELSE kb.nguoi_gui_id
            END as ban_be_id,
            nd.ho_ten, nd.anh_dai_dien
        FROM ket_ban kb
        JOIN nguoi_dung nd ON (
            CASE 
                WHEN kb.nguoi_gui_id = ? THEN kb.nguoi_nhan_id
                ELSE kb.nguoi_gui_id
            END = nd.id
        )
        WHERE (kb.nguoi_gui_id = ? OR kb.nguoi_nhan_id = ?) 
        AND kb.trang_thai = 'da_dong_y'
        ORDER BY nd.ho_ten
        LIMIT 6
    ");
    $stmt->execute([$profile_id, $profile_id, $profile_id, $profile_id]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $friends = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $user['ho_ten']; ?> - UTT Social</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/trang-chu.css">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="container main-container">
        <!-- Profile Header -->
        <div class="profile-header">
            
            
            <div class="profile-info">
                <div class="profile-picture">
                    <img src="uploads/avatars/<?php echo $user['anh_dai_dien']; ?>" alt="Profile Picture">
                    <?php if ($profile_id == $_SESSION['user_id']): ?>
                    <button class="btn btn-light btn-sm edit-avatar">
                        <i class="fas fa-camera"></i>
                    </button>
                    <?php endif; ?>
                </div>
                
                <div class="profile-details">
                    <h2><?php echo $user['ho_ten']; ?></h2>
                    <p class="profile-meta">
                        <i class="fas fa-graduation-cap"></i> <?php echo $user['khoa']; ?> - K<?php echo $user['nam_hoc']; ?>
                    </p>
                    <p class="profile-bio"><?php echo $user['gioi_thieu'] ?: 'Chưa có giới thiệu'; ?></p>
                </div>
                
                <div class="profile-actions">
                    <?php if ($profile_id == $_SESSION['user_id']): ?>
                        <button class="btn btn-primary btn-edit-profile" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </button>
                    <?php else: ?>
                        <?php if ($friend_status == 'da_dong_y'): ?>
                            <button class="btn btn-primary btn-unfriend" data-friend-id="<?php echo $profile_id; ?>">
                                <i class="fas fa-user-check"></i> Bạn bè
                            </button>
                            <button class="btn btn-outline-primary">
                                <i class="fas fa-comment"></i> Nhắn tin
                            </button>
                        <?php elseif ($friend_status == 'cho_duyet'): ?>
                            <?php if ($is_sender): ?>
                                <button class="btn btn-secondary" disabled>
                                    <i class="fas fa-user-clock"></i> Đã gửi lời mời
                                </button>
                            <?php else: ?>
                                <button class="btn btn-primary btn-accept-request" data-request-id="<?php echo $ket_ban['id']; ?>">
                                    <i class="fas fa-user-plus"></i> Chấp nhận lời mời
                                </button>
                                <button class="btn btn-outline-secondary btn-decline-request" data-request-id="<?php echo $ket_ban['id']; ?>">
                                    <i class="fas fa-user-times"></i> Từ chối
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="btn btn-primary btn-add-friend" data-user-id="<?php echo $profile_id; ?>">
                                <i class="fas fa-user-plus"></i> Kết bạn
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="profile-nav">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#posts">Bài viết</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Giới thiệu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#friends">Bạn bè (<?php echo $friend_count; ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#photos">Ảnh</a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-4">
                <!-- About Section -->
                <div class="profile-section" id="about">
                    <div class="section-header">
                        <h4>Giới thiệu</h4>
                        <?php if ($profile_id == $_SESSION['user_id']): ?>
                        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="section-content">
                        <div class="info-item">
                            <i class="fas fa-graduation-cap"></i>
                            <div>
                                <p>Học tại <strong>Trường Đại học Công nghệ GTVT</strong></p>
                                <p class="text-muted">Khoa <?php echo $user['khoa']; ?> - K<?php echo $user['nam_hoc']; ?></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-home"></i>
                            <div>
                                <p>Sống tại <strong>Hà Nội</strong></p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <p><?php echo $user['gioi_thieu'] ?: 'Chưa có thông tin giới thiệu'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Friends Section -->
                <div class="profile-section" id="friends">
                    <div class="section-header">
                        <h4>Bạn bè (<?php echo $friend_count; ?>)</h4>
                        <a href="ban-be.php?id=<?php echo $profile_id; ?>" class="btn btn-sm btn-light">
                            Xem tất cả
                        </a>
                    </div>
                    <div class="section-content">
                        <div class="friends-grid">
                            <?php if (count($friends) > 0): ?>
                                <?php foreach ($friends as $friend): ?>
                                <div class="friend-item">
                                    <a href="profile.php?id=<?php echo $friend['ban_be_id']; ?>">
                                        <img src="uploads/avatars/<?php echo $friend['anh_dai_dien']; ?>" alt="<?php echo $friend['ho_ten']; ?>">
                                        <span><?php echo $friend['ho_ten']; ?></span>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Chưa có bạn bè</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Photos Section -->
                <div class="profile-section" id="photos">
                    <div class="section-header">
                        <h4>Ảnh</h4>
                        <a href="#" class="btn btn-sm btn-light">
                            Xem tất cả
                        </a>
                    </div>
                    <div class="section-content">
                        <div class="photos-grid">
                            <?php 
                            $photo_count = 0;
                            foreach ($posts as $post) {
                                if ($post['anh'] && $photo_count < 9) {
                                    echo '<div class="photo-item">
                                        <img src="uploads/posts/' . $post['anh'] . '" alt="Photo">
                                    </div>';
                                    $photo_count++;
                                }
                            }
                            
                            if ($photo_count == 0) {
                                echo '<p class="text-muted">Chưa có ảnh</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Posts -->
            <div class="col-md-8">
                <!-- Create Post (only for profile owner) -->
                <?php if ($profile_id == $_SESSION['user_id']): ?>
                <div class="create-post">
                    <div class="create-post-header">
                        <img src="uploads/avatars/<?php echo $_SESSION['anh_dai_dien']; ?>" alt="Avatar" class="avatar">
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
                <?php endif; ?>
                
                <!-- Posts Section -->
                <div class="posts-container" id="posts">
                    <?php if (count($posts) > 0): ?>
                        <?php foreach ($posts as $post): ?>
                        <div class="post" data-post-id="<?php echo $post['id']; ?>" id="post-<?php echo $post['id']; ?>">
                            <div class="post-header">
                                <img src="uploads/avatars/<?php echo $post['anh_dai_dien']; ?>" alt="Avatar" class="avatar">
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
                                    <img src="uploads/posts/<?php echo $post['anh']; ?>" alt="Post image" class="post-image">
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
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-posts">
                            <i class="fas fa-file-alt"></i>
                            <p>Chưa có bài viết nào</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <?php if ($profile_id == $_SESSION['user_id']): ?>
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa trang cá nhân</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editProfileForm" action="xu-ly/cap-nhat-profile.php" method="POST">
                        <div class="mb-3">
                            <label for="ho_ten" class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" id="ho_ten" name="ho_ten" value="<?php echo $user['ho_ten']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="khoa" class="form-label">Khoa</label>
                            <input type="text" class="form-control" id="khoa" name="khoa" value="<?php echo $user['khoa']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="nam_hoc" class="form-label">Năm học</label>
                            <input type="number" class="form-control" id="nam_hoc" name="nam_hoc" value="<?php echo $user['nam_hoc']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="gioi_thieu" class="form-label">Giới thiệu</label>
                            <textarea class="form-control" id="gioi_thieu" name="gioi_thieu" rows="3"><?php echo $user['gioi_thieu']; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Lưu thay đổi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
    <script src="assets/js/trang-chu.js"></script>
    <script src="assets/js/profile.js"></script>
</body>
</html> 