<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../trang-chu.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = mysqli_connect("localhost", "root", "", "utt_social");
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ho_ten = mysqli_real_escape_string($conn, $_POST['ho_ten']);
    $gioi_thieu = mysqli_real_escape_string($conn, $_POST['gioi_thieu']);
    
    // Handle profile photo upload
    if (isset($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['anh_dai_dien']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = '../uploads/avatars/' . $new_filename;
            
            if (move_uploaded_file($_FILES['anh_dai_dien']['tmp_name'], $upload_path)) {
                $update_photo = ", anh_dai_dien = '$new_filename'";
            }
        }
    } else {
        $update_photo = "";
    }
    // Update profile information
    $update_query = "UPDATE nguoi_dung SET 
                    ho_ten = '$ho_ten',
                    gioi_thieu = '$gioi_thieu'
                    $update_photo
                    WHERE id = $user_id";
                    
                    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "Profile updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating profile: " . mysqli_error($conn);
    }
    
    header('Location: ../profile.php');
    exit();
}

// Get current user data

$user_id = mysqli_real_escape_string($conn, $user_id);
$query = "SELECT * FROM nguoi_dung WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .edit-profile-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .form-group:hover {
            transform: translateX(5px);
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group input[type="file"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="file"]:focus,
        .form-group textarea:focus {
            border-color: #007bff;
            outline: none;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .form-actions {
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            transition: transform 0.2s ease;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            text-decoration: none;
        }

        .btn i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="edit-profile-container">
        <h2>Edit Profile</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <!-- <div class="form-group">
                <label>Cover Photo</label>
                <input type="file" name="anh_bia" accept="image/*">
            </div> -->

            <div class="form-group">
                <label>Profile Photo</label>
                <input type="file" name="anh_dai_dien" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="ho_ten" value="<?php echo htmlspecialchars($user['ho_ten']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Bio</label>
                <textarea name="gioi_thieu"><?php echo htmlspecialchars($user['gioi_thieu'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <a href="../profile.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            <?php
            if (isset($_SESSION['message'])) {
                echo '<div class="alert alert-success">' . $_SESSION['message'] . '</div>';
                unset($_SESSION['message']);
            } elseif (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            ?>
            </div>
        </form>
    </div>
</body>
</html>