<?php
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function redirectTo($location) {
    header("Location: " . $location);
    exit();
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Tính số tuần từ số ngày
    $weeks = floor($diff->days / 7);
    
    $string = array(
        'y' => ['năm', $diff->y],
        'm' => ['tháng', $diff->m],
        'w' => ['tuần', $weeks],
        'd' => ['ngày', $diff->d % 7], // Số ngày còn lại sau khi tính tuần
        'h' => ['giờ', $diff->h],
        'i' => ['phút', $diff->i],
        's' => ['giây', $diff->s]
    );

    foreach ($string as $k => $v) {
        if ($v[1] > 0) {
            return $v[1] . ' ' . $v[0] . ($v[1] > 1 ? '' : '') . ' trước';
        }
    }

    return 'Vừa xong';
}

function get_short_time($datetime) {
    $now = time();
    $time = strtotime($datetime);
    $diff = $now - $time;

    if ($diff < 60) {
        return 'Vừa xong';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' phút';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' giờ';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' ngày';
    } else {
        return date('d/m/Y', $time);
    }
}

function format_datetime($datetime) {
    $format = array(
        'Monday'    => 'Thứ Hai',
        'Tuesday'   => 'Thứ Ba',
        'Wednesday' => 'Thứ Tư',
        'Thursday'  => 'Thứ Năm',
        'Friday'    => 'Thứ Sáu',
        'Saturday'  => 'Thứ Bảy',
        'Sunday'    => 'Chủ Nhật',
        'January'   => 'Tháng 1',
        'February'  => 'Tháng 2',
        'March'     => 'Tháng 3',
        'April'     => 'Tháng 4',
        'May'       => 'Tháng 5',
        'June'      => 'Tháng 6',
        'July'      => 'Tháng 7',
        'August'    => 'Tháng 8',
        'September' => 'Tháng 9',
        'October'   => 'Tháng 10',
        'November'  => 'Tháng 11',
        'December'  => 'Tháng 12'
    );

    $date = new DateTime($datetime);
    $weekday = $date->format('l');
    $month = $date->format('F');
    
    $weekday_vi = $format[$weekday];
    $month_vi = $format[$month];
    
    return $weekday_vi . ', ' . $date->format('d') . ' ' . $month_vi . ' ' . $date->format('Y H:i');
}
?> 