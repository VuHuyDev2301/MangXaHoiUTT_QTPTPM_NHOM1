-- Tạo cơ sở dữ liệu
CREATE DATABASE utt_social CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Bảng người dùng
CREATE TABLE nguoi_dung (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ten_dang_nhap VARCHAR(50) UNIQUE NOT NULL,
    mat_khau VARCHAR(255) NOT NULL,
    ho_ten VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    anh_dai_dien VARCHAR(255) DEFAULT 'default.jpg',
    khoa VARCHAR(100),
    nam_hoc INT,
    gioi_thieu TEXT,
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
    trang_thai ENUM('hoat_dong', 'khoa') DEFAULT 'hoat_dong'
);

-- Bảng bài viết
CREATE TABLE bai_viet (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nguoi_dung_id INT,
    noi_dung TEXT,
    anh VARCHAR(255),
    ngay_dang DATETIME DEFAULT CURRENT_TIMESTAMP,
    chia_se_id INT NULL,
    FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (chia_se_id) REFERENCES bai_viet(id) ON DELETE CASCADE
);
alter TABLE bai_viet ADD COLUMN trang_thai default 'chua_duyet' AFTER chia_se_id;

-- Bảng bình luận
CREATE TABLE binh_luan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bai_viet_id INT,
    nguoi_dung_id INT,
    noi_dung TEXT,
    ngay_binh_luan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bai_viet_id) REFERENCES bai_viet(id) ON DELETE CASCADE,
    FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
);

-- Bảng thích
CREATE TABLE thich (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bai_viet_id INT,
    nguoi_dung_id INT,
    ngay_thich DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bai_viet_id) REFERENCES bai_viet(id) ON DELETE CASCADE,
    FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
);

-- Bảng kết bạn
CREATE TABLE ket_ban (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nguoi_gui_id INT,
    nguoi_nhan_id INT,
    trang_thai ENUM('cho_duyet', 'da_dong_y', 'tu_choi') DEFAULT 'cho_duyet',
    ngay_gui DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nguoi_gui_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (nguoi_nhan_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
);

-- Bảng tin nhắn
CREATE TABLE tin_nhan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nguoi_gui_id INT,
    nguoi_nhan_id INT,
    noi_dung TEXT,
    da_doc BOOLEAN DEFAULT FALSE,
    ngay_gui DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nguoi_gui_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (nguoi_nhan_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
);

-- Bảng thông báo
CREATE TABLE thong_bao (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nguoi_dung_id INT,
    loai ENUM('thich', 'binh_luan', 'ket_ban', 'tin_nhan') NOT NULL,
    noi_dung TEXT,
    da_doc BOOLEAN DEFAULT FALSE,
    lien_ket_id INT,
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE
); 

CREATE TABLE `ban_be` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `nguoi_dung_id` INT NOT NULL,
    `ban_be_id` INT NOT NULL,
    FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`ban_be_id`) REFERENCES `nguoi_dung`(`id`) ON DELETE CASCADE
);ALTER TABLE `ban_be` ADD `tin_nhan` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci NOT NULL AFTER `ban_be_id`;
 ALTER TABLE `ban_be` 
ADD `trang_thai` ENUM('da_xem', 'chua_xem', 'dang_gui', 'da_gui') NOT NULL DEFAULT 'chua_xem' AFTER `tin_nhan`,
ADD `thoi_gian` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `trang_thai`;

DELIMITER //

CREATE TRIGGER `them_ban_be` AFTER UPDATE ON `ket_ban`
FOR EACH ROW
BEGIN
    IF NEW.trang_thai = 'da_dong_y' THEN
        INSERT INTO `ban_be` (`nguoi_dung_id`, `ban_be_id`) VALUES (NEW.nguoi_gui_id, NEW.nguoi_nhan_id);
        INSERT INTO `ban_be` (`nguoi_dung_id`, `ban_be_id`) VALUES (NEW.nguoi_nhan_id, NEW.nguoi_gui_id);
    END IF;
END //

DELIMITER ;
