<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="trang-chu.php">
            <img src="assets/images/logo.png" alt="UTT Social" height="40">
        </a>
        
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Tìm kiếm...">
        </div>

        <div class="nav-right">
            <div class="nav-user">
                <a href="ban-be.php" class="nav-link">
                    <i class="fas fa-user-friends"></i>
                    <span class="nav-text">Bạn bè</span>
                    <?php if (isset($loi_moi_count) && $loi_moi_count > 0): ?>
                    <span class="badge bg-danger"><?php echo $loi_moi_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="messenger.php" class="nav-link">
                    <i class="fas fa-comment-alt"></i>
                    <span class="nav-text">Tin nhắn</span>
                    <?php if (isset($loi_moi_count) && $loi_moi_count > 0): ?>
                    <span class="badge bg-danger"><?php echo $loi_moi_count; ?></span>
                    <?php endif; ?>
                </a>
                
                <div class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span class="nav-text">Thông báo</span>
                        <span class="badge bg-danger notification-count" style="display: none;"></span>
                    </a>
                    <div class="dropdown-menu notifications-dropdown" aria-labelledby="notificationsDropdown">
                        <div class="notifications-header">
                            <h6>Thông báo</h6>
                            <button class="mark-all-read">Đánh dấu tất cả đã đọc</button>
                        </div>
                        <div class="notifications-list">
                            <!-- Thông báo sẽ được load bằng JavaScript -->
                            <div class="no-notifications">
                                <p>Không có thông báo mới</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="uploads/avatars/<?php echo $_SESSION['anh_dai_dien']; ?>" alt="Avatar" class="avatar">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Trang cá nhân</a></li>
                        <li><a class="dropdown-item" href="ban-be.php"><i class="fas fa-user-friends"></i> Bạn bè</a></li>
                        <li><a class="dropdown-item" href="cai-dat.php"><i class="fas fa-cog"></i> Cài đặt</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="xu-ly/dang-xuat.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav> 