<div class="sidebar left-sidebar">
    <div class="user-profile">
        <img src="uploads/avatars/<?php echo $_SESSION['anh_dai_dien'] ?? 'default.jpg'; ?>" 
             alt="Profile" class="profile-image">
        <h4><?php echo $_SESSION['ho_ten']; ?></h4>
        <p>Sinh viên UTT</p>
    </div>
    
    <ul class="sidebar-menu">
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'trang-chu.php') ? 'active' : ''; ?>">
            <a href="trang-chu.php"><i class="fas fa-home"></i> Trang chủ</a>
        </li>
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'ban-be.php') ? 'active' : ''; ?>">
            <a href="ban-be.php">
                <i class="fas fa-user-friends"></i> Bạn bè
                <?php if (isset($loi_moi_count) && $loi_moi_count > 0): ?>
                    <span class="badge bg-danger"><?php echo $loi_moi_count; ?></span>
                <?php endif; ?>
            </a>
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