-- Thêm dữ liệu mẫu cho bảng nguoi_dung
INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, ho_ten, email, anh_dai_dien, khoa, nam_hoc, gioi_thieu) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin UTT', 'admin@utt.edu.vn', 'admin.jpg', 'Quản trị', 2020, 'Quản trị viên hệ thống'),
('nguyenvana', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', 'nguyenvana@utt.edu.vn', 'user1.jpg', 'Công nghệ thông tin', 2021, 'Sinh viên năm 3 ngành CNTT'),
('tranthib', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị B', 'tranthib@utt.edu.vn', 'user2.jpg', 'Kế toán', 2022, 'Yêu thích âm nhạc và du lịch'),
('levanc', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lê Văn C', 'levanc@utt.edu.vn', 'user3.jpg', 'Điện tử', 2021, 'Đam mê công nghệ'),
('phamthid', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Phạm Thị D', 'phamthid@utt.edu.vn', 'user4.jpg', 'Quản trị kinh doanh', 2023, 'Thích kinh doanh online');

-- Thêm dữ liệu mẫu cho bảng bai_viet
INSERT INTO bai_viet (nguoi_dung_id, noi_dung, anh, ngay_dang) VALUES
(2, 'Hôm nay là một ngày tuyệt vời tại UTT! 🌟 #UTTLife', 'post1.jpg', '2024-03-15 08:30:00'),
(3, 'Chia sẻ một số khoảnh khắc đẹp trong buổi văn nghệ của khoa 🎵', 'post2.jpg', '2024-03-15 09:15:00'),
(4, 'Dự án mới của nhóm đã hoàn thành! Cảm ơn các bạn đã hợp tác 🚀', 'post3.jpg', '2024-03-14 14:20:00'),
(2, 'Chúc mừng đội tuyển bóng đá UTT đã giành chiến thắng! ⚽', 'post4.jpg', '2024-03-14 16:45:00'),
(5, 'Chia sẻ kinh nghiệm thực tập tại doanh nghiệp 📚', NULL, '2024-03-13 10:30:00');

-- Thêm dữ liệu mẫu cho bảng binh_luan
INSERT INTO binh_luan (bai_viet_id, nguoi_dung_id, noi_dung, ngay_binh_luan) VALUES
(1, 3, 'Tuyệt vời quá! 👍', '2024-03-15 08:35:00'),
(1, 4, 'Chúc mừng bạn nhé!', '2024-03-15 08:40:00'),
(2, 2, 'Buổi văn nghệ hay quá! 🎉', '2024-03-15 09:20:00'),
(3, 5, 'Dự án rất ấn tượng 👏', '2024-03-14 14:30:00'),
(4, 3, 'Chúc mừng đội tuyển nhà trường 🏆', '2024-03-14 16:50:00');

-- Thêm dữ liệu mẫu cho bảng thich
INSERT INTO thich (bai_viet_id, nguoi_dung_id, ngay_thich) VALUES
(1, 3, '2024-03-15 08:31:00'),
(1, 4, '2024-03-15 08:32:00'),
(1, 5, '2024-03-15 08:33:00'),
(2, 2, '2024-03-15 09:16:00'),
(2, 4, '2024-03-15 09:17:00'),
(3, 2, '2024-03-14 14:25:00'),
(3, 3, '2024-03-14 14:26:00'),
(4, 3, '2024-03-14 16:46:00'),
(4, 5, '2024-03-14 16:47:00'),
(5, 2, '2024-03-13 10:35:00');

-- Thêm dữ liệu mẫu cho bảng ket_ban
INSERT INTO ket_ban (nguoi_gui_id, nguoi_nhan_id, trang_thai, ngay_gui) VALUES
(2, 3, 'da_dong_y', '2024-03-10 09:00:00'),
(2, 4, 'da_dong_y', '2024-03-10 09:15:00'),
(3, 5, 'da_dong_y', '2024-03-11 14:20:00'),
(4, 5, 'cho_duyet', '2024-03-12 16:30:00'),
(5, 2, 'da_dong_y', '2024-03-13 08:45:00');

-- Thêm dữ liệu mẫu cho bảng tin_nhan
INSERT INTO tin_nhan (nguoi_gui_id, nguoi_nhan_id, noi_dung, da_doc, ngay_gui) VALUES
(2, 3, 'Chào bạn, cho mình hỏi về bài tập môn CSDL được không?', true, '2024-03-14 09:30:00'),
(3, 2, 'Được bạn, bạn cần hỏi gì?', true, '2024-03-14 09:35:00'),
(2, 3, 'Mình chưa hiểu phần normalized forms lắm', true, '2024-03-14 09:40:00'),
(4, 5, 'Bạn ơi, ngày mai có buổi họp nhóm đồ án không?', false, '2024-03-15 10:00:00'),
(5, 4, 'Có bạn nhé, 2h chiều tại thư viện', false, '2024-03-15 10:05:00');

-- Thêm dữ liệu mẫu cho bảng thong_bao
INSERT INTO thong_bao (nguoi_dung_id, loai, noi_dung, da_doc, lien_ket_id, ngay_tao) VALUES
(2, 'thich', 'Trần Thị B đã thích bài viết của bạn', false, 1, '2024-03-15 08:31:00'),
(2, 'binh_luan', 'Lê Văn C đã bình luận về bài viết của bạn', false, 1, '2024-03-15 08:40:00'),
(3, 'ket_ban', 'Phạm Thị D đã gửi lời mời kết bạn', true, 4, '2024-03-12 16:30:00'),
(4, 'tin_nhan', 'Bạn có tin nhắn mới từ Phạm Thị D', false, 5, '2024-03-15 10:05:00'),
(5, 'thich', 'Nguyễn Văn A đã thích bài viết của bạn', true, 5, '2024-03-13 10:35:00'); 