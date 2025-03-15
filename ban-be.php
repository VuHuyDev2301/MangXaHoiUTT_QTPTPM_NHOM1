<?php
session_start();
require_once 'includes/functions.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    redirectTo('index.php');
}

$db = new Database();
$conn = $db->getConnection();

// Lấy số lượng lời mời kết bạn
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM ket_ban 
    WHERE nguoi_nhan_id = ? AND trang_thai = 'cho_duyet'
");
$stmt->execute([$_SESSION['user_id']]);
$loi_moi_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Lấy danh sách bạn bè
try {
    $stmt = $conn->prepare("
        SELECT 
            CASE 
                WHEN kb.nguoi_gui_id = ? THEN kb.nguoi_nhan_id
                ELSE kb.nguoi_gui_id
            END as ban_be_id,
            nd.ho_ten, nd.anh_dai_dien, nd.trang_thai, nd.gioi_thieu, nd.khoa, nd.nam_hoc
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
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $ban_be = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

// Lấy danh sách lời mời kết bạn
try {
    $stmt = $conn->prepare("
        SELECT kb.id, kb.nguoi_gui_id, kb.ngay_gui, nd.ho_ten, nd.anh_dai_dien, nd.gioi_thieu
        FROM ket_ban kb
        JOIN nguoi_dung nd ON kb.nguoi_gui_id = nd.id
        WHERE kb.nguoi_nhan_id = ? AND kb.trang_thai = 'cho_duyet'
        ORDER BY kb.ngay_gui DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $loi_moi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}

// Lấy danh sách người dùng để gợi ý kết bạn
try {
    $stmt = $conn->prepare("
        SELECT nd.id, nd.ho_ten, nd.anh_dai_dien, nd.gioi_thieu, nd.khoa
        FROM nguoi_dung nd
        WHERE nd.id != ?
        AND nd.id NOT IN (
            SELECT 
                CASE 
                    WHEN kb.nguoi_gui_id = ? THEN kb.nguoi_nhan_id
                    ELSE kb.nguoi_gui_id
                END
            FROM ket_ban kb
            WHERE (kb.nguoi_gui_id = ? OR kb.nguoi_nhan_id = ?)
            AND kb.trang_thai IN ('da_dong_y', 'cho_duyet')
        )
        ORDER BY RAND()
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    $goi_y = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTT Social - Bạn bè</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/trang-chu.css">
    <link rel="stylesheet" href="assets/css/ban-be.css">
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

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="content-wrapper">
                    <div class="section-header">
                        <h4>Bạn bè</h4>
                    </div>
                    
                    <!-- Lời mời kết bạn -->
                    <?php if (count($loi_moi) > 0): ?>
                    <div class="friend-requests-section">
                        <h5>Lời mời kết bạn (<?php echo count($loi_moi); ?>)</h5>
                        <div class="friend-requests">
                            <?php foreach ($loi_moi as $loi_moi_item): ?>
                            <div class="friend-request-card" data-request-id="<?php echo $loi_moi_item['id']; ?>">
                                <div class="friend-info">
                                    <img src="uploads/avatars/<?php echo $loi_moi_item['anh_dai_dien']; ?>" alt="Avatar" class="avatar">
                                    <div class="friend-details">
                                        <h6><?php echo $loi_moi_item['ho_ten']; ?></h6>
                                        <p><?php echo $loi_moi_item['gioi_thieu'] ?? ''; ?></p>
                                        <small><?php echo time_elapsed_string($loi_moi_item['ngay_gui']); ?></small>
                                    </div>
                                </div>
                                <div class="friend-actions">
                                    <button class="btn btn-primary btn-sm btn-accept" data-request-id="<?php echo $loi_moi_item['id']; ?>">
                                        <i class="fas fa-check"></i> Chấp nhận
                                    </button>
                                    <button class="btn btn-light btn-sm btn-decline" data-request-id="<?php echo $loi_moi_item['id']; ?>">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Danh sách bạn bè -->
                    <div class="friends-list-section">
                        <h5>Tất cả bạn bè (<?php echo count($ban_be); ?>)</h5>
                        <div class="friends-list">
                            <?php if (count($ban_be) > 0): ?>
                                <?php foreach ($ban_be as $ban): ?>
                                <div class="friend-card">
                                    <div class="friend-info">
                                        <img src="uploads/avatars/<?php echo $ban['anh_dai_dien']; ?>" alt="Avatar" class="avatar">
                                        <div class="friend-details">
                                            <h6><?php echo $ban['ho_ten']; ?></h6>
                                            <p><?php echo $ban['khoa'] . ' - K' . $ban['nam_hoc']; ?></p>
                                            <span class="status <?php echo $ban['trang_thai'] == 'hoat_dong' ? 'online' : 'offline'; ?>">
                                                <?php echo $ban['trang_thai'] == 'hoat_dong' ? 'Đang hoạt động' : 'Không hoạt động'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="friend-actions">
                                        <a href="profile.php?id=<?php echo $ban['ban_be_id']; ?>" class="btn btn-light btn-sm">
                                            <i class="fas fa-user"></i> Xem trang cá nhân
                                        </a>
                                        <button class="btn btn-danger btn-sm btn-unfriend" data-friend-id="<?php echo $ban['ban_be_id']; ?>">
                                            <i class="fas fa-user-times"></i> Hủy kết bạn
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-user-friends"></i>
                                    <p>Bạn chưa có bạn bè nào</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Gợi ý kết bạn -->
                    <div class="friend-suggestions-section">
                        <h5>Gợi ý kết bạn</h5>
                        <div class="friend-suggestions">
                            <?php foreach ($goi_y as $nguoi_dung): ?>
                            <div class="friend-suggestion-card">
                                <div class="friend-info">
                                    <img src="uploads/avatars/<?php echo $nguoi_dung['anh_dai_dien']; ?>" alt="Avatar" class="avatar">
                                    <div class="friend-details">
                                        <h6><?php echo $nguoi_dung['ho_ten']; ?></h6>
                                        <p><?php echo $nguoi_dung['khoa']; ?></p>
                                        <p><?php echo $nguoi_dung['gioi_thieu'] ?? ''; ?></p>
                                    </div>
                                </div>
                                <div class="friend-actions">
                                    <button class="btn btn-primary btn-sm btn-add-friend" data-user-id="<?php echo $nguoi_dung['id']; ?>">
                                        <i class="fas fa-user-plus"></i> Kết bạn
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/ban-be.js"></script>
</body>
</html> 