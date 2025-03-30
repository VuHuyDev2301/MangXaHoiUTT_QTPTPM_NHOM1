<?php
session_start();
// Database connection (adjust credentials as needed)
$conn = mysqli_connect("localhost", "root", "", "utt_social");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: trang-chu.php");
    exit();
}
include './includes/navbar.php';
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM nguoi_dung WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Social Media</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/trang-chu.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
        }
        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .cover-photo {
            height: 350px;
            background-color: #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
            position: relative;
        }
        .profile-photo {
            width: 168px;
            height: 168px;
            border-radius: 50%;
            border: 4px solid white;
            position: absolute;
            bottom: -50px;
            left: 20px;
        }
        .profile-info {
            margin-top: 60px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
        }
        .profile-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .profile-bio {
            color: #65676b;
            margin-bottom: 20px;
        }
        .profile-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #1877f2;
            color: white;
        }
        .btn-secondary {
            background-color: #e4e6eb;
            color: #050505;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="cover-photo">
            <img src="uploads/avatars/<?php echo $user['anh_dai_dien'] ?? 'default.jpg'; ?>" alt="Cover Photo" style="width: 100%; height: 100%; object-fit: cover;">
            <img src="uploads/avatars/<?php echo $user['anh_dai_dien'] ?? 'default.jpg' ?>" alt="Profile Photo" class="profile-photo">
        </div>
        
        <div class="profile-info">
            <div class="profile-name"><?php echo htmlspecialchars($user['ho_ten']); ?></div>
            <div class="profile-bio"><?php echo htmlspecialchars($user['gioi_thieu'] ?? 'No bio yet'); ?></div>
            
            <div class="profile-actions">
                <a href="xu-ly/cap-nhat-ttcn.php" class="btn btn-primary"><i class="fas fa-edit"></i> Edit Profile</a>
                <button class="btn btn-secondary"><i class="fas fa-user-friends"></i> Friends</button>
                <button class="btn btn-secondary"><i class="fas fa-images"></i> Photos</button>
            </div>
        </div>
        
        <!-- Posts Section -->
        <div class="posts-section">
            <?php
            $posts_query = "SELECT * FROM bai_viet WHERE nguoi_dung_id = $user_id ORDER BY ngay_dang DESC";
            $posts_result = mysqli_query($conn, $posts_query);
            
            while ($post = mysqli_fetch_assoc($posts_result)) {
                echo '<div class="post" style="background: white; padding: 20px; margin: 20px 0; border-radius: 8px;">';
                echo '<p>' . htmlspecialchars($post['content']) . '</p>';
                echo '<small>' . $post['created_at'] . '</small>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>