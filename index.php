<?php
session_start();
require_once 'includes/functions.php';
if(isset($_SESSION['user_id'])) {
    header("Location: trang-chu.php");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTT Social - Đăng nhập</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="container">
        <div class="forms-container">
            <div class="signin-signup">
                <!-- Form Đăng nhập -->
                <form action="xu-ly/dang-nhap.php" method="POST" class="sign-in-form">
                    <h2 class="title">Đăng nhập</h2>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="ten_dang_nhap" placeholder="Tên đăng nhập" required>
                    </div>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="mat_khau" placeholder="Mật khẩu" required>
                    </div>
                    <input type="submit" value="Đăng nhập" class="btn solid">
                    
                    <p class="social-text">Hoặc đăng nhập với</p>
                    <div class="social-media">
                        <a href="#" class="social-icon">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-google"></i>
                        </a>
                    </div>
                </form>

                <!-- Form Đăng ký -->
                <form action="xu-ly/dang-ky.php" method="POST" class="sign-up-form">
                    <h2 class="title">Đăng ký</h2>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="ten_dang_nhap" placeholder="Tên đăng nhập" required>
                    </div>
                    <div class="input-field">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="input-field">
                        <i class="fas fa-user-circle"></i>
                        <input type="text" name="ho_ten" placeholder="Họ và tên" required>
                    </div>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="mat_khau" placeholder="Mật khẩu" required>
                    </div>
                    <div class="input-field">
                        <i class="fas fa-graduation-cap"></i>
                        <input type="text" name="khoa" placeholder="Khoa" required>
                    </div>
                    <div class="input-field">
                        <i class="fas fa-calendar"></i>
                        <input type="number" name="nam_hoc" placeholder="Năm học" required>
                    </div>
                    <input type="submit" class="btn" value="Đăng ký">
                </form>
            </div>
        </div>

        <div class="panels-container">
            <div class="panel left-panel">
                <div class="content">
                    <h3>Chưa có tài khoản?</h3>
                    <p>Tham gia ngay cộng đồng UTT Social để kết nối với bạn bè và chia sẻ những khoảnh khắc đáng nhớ!</p>
                    <button class="btn transparent" id="sign-up-btn">Đăng ký</button>
                </div>
                <img src="assets/images/log.svg" class="image" alt="">
            </div>
            <div class="panel right-panel">
                <div class="content">
                    <h3>Đã có tài khoản?</h3>
                    <p>Đăng nhập ngay để không bỏ lỡ những thông tin mới nhất từ bạn bè!</p>
                    <button class="btn transparent" id="sign-in-btn">Đăng nhập</button>
                </div>
                <img src="assets/images/register.svg" class="image" alt="">
            </div>
        </div>
    </div>
    <script src="assets/js/auth.js"></script>
    <?php if ($flash = getFlashMessage()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>" id="flash-message">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>
</body>
</html>
