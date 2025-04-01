<div class="sidebar">
    <a class="navbar-brand" href="../trang-chu.php">
        <img src="../assets/images/logo.png" alt="UTT Social" style="max-width: 100px;width: 100px;border-radius: inherit;">
    </a>
    <ul>
        <li><a href="manage-account.php">Quản lý tài khoản</a></li>
        <li><a href="manage-post.php">Quản lý bài viết</a></li>
    </ul>
</div>

    <style>
        .navbar-brand {
    display: flex;
    align-items: center; 
    justify-content: center; 
    height: 100px; 
    }
    .sidebar {
    width: 200px;
    background-color: #007bff;
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    padding: 20px;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
    animation: slideIn 0.5s ease-in-out;
    }


    .sidebar:hover {
        box-shadow: 4px 0 15px rgba(255, 255, 255, 0.3);
        transition: box-shadow 0.3s ease-in-out;
    }

    .sidebar h2 {
        text-align: center;
        font-size: 20px;
        margin-bottom: 20px;
        opacity: 0;
        animation: fadeIn 1s forwards 0.3s;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
    }

    /* Hiệu ứng cho mục menu */
    .sidebar ul li {
        margin: 15px 0;
        opacity: 0;
        animation: fadeIn 1s forwards 0.5s;
    }

    .sidebar ul li:nth-child(2) { animation-delay: 0.6s; }

    .sidebar ul li a {
        color: white;
        text-decoration: none;
        font-size: 16px;
        display: block;
        padding: 10px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 5px;
        text-align: center;
        transition: all 0.3s ease-in-out;
    }

    /* Hiệu ứng hover cho menu */
    .sidebar ul li a:hover {
        background: rgba(255, 255, 255, 0.4);
        transform: scale(1.05);
        box-shadow: 0px 0px 8px rgba(255, 255, 255, 0.5);
    }

    /* Animation khi mở sidebar */
    @keyframes slideIn {
        from {
            transform: translateX(-220px);
        }
        to {
            transform: translateX(0);
        }
    }

    /* Animation fade in cho text */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    </style>
