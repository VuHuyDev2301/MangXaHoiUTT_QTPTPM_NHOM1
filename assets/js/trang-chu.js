// Thêm hàm time_elapsed_string
function time_elapsed_string(datetime) {
    const now = new Date();
    // Chuyển đổi datetime string thành đối tượng Date
    const past = new Date(datetime.replace(' ', 'T') + '+07:00');
    const diff = Math.floor((now - past) / 1000);

    if (diff < 60) {
        return 'Vừa xong';
    } else if (diff < 3600) {
        const minutes = Math.floor(diff / 60);
        return `${minutes} phút trước`;
    } else if (diff < 86400) {
        const hours = Math.floor(diff / 3600);
        return `${hours} giờ trước`;
    } else if (diff < 604800) {
        const days = Math.floor(diff / 86400);
        return `${days} ngày trước`;
    } else if (diff < 2592000) {
        const weeks = Math.floor(diff / 604800);
        return `${weeks} tuần trước`;
    } else if (diff < 31536000) {
        const months = Math.floor(diff / 2592000);
        return `${months} tháng trước`;
    } else {
        const years = Math.floor(diff / 31536000);
        return `${years} năm trước`;
    }
}

// Xử lý đăng bài
const createPostForm = document.getElementById('createPostForm');
const imagePreview = document.getElementById('imagePreview');

createPostForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(createPostForm);
    
    try {
        const response = await fetch('xu-ly/dang-bai.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            // Đóng modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('createPostModal'));
            modal.hide();
            
            // Reset form và preview
            createPostForm.reset();
            imagePreview.innerHTML = '';
            
            // Reload trang để hiển thị bài viết mới
            location.reload();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi đăng bài!');
    }
});

// Preview ảnh trước khi đăng
document.getElementById('postImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.innerHTML = `
                <div class="image-preview-wrapper">
                    <img src="${e.target.result}" class="img-preview">
                    <button type="button" class="remove-image" onclick="removeImage()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>`;
        }
        reader.readAsDataURL(file);
    }
});

// Xóa ảnh preview
function removeImage() {
    document.getElementById('postImage').value = '';
    imagePreview.innerHTML = '';
}

// Xử lý thích bài viết
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const postId = this.closest('.post').dataset.postId;
        const icon = this.querySelector('i');
        
        try {
            const response = await fetch('xu-ly/thich.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `bai_viet_id=${postId}`
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                // Cập nhật UI
                if (data.action === 'like') {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    icon.style.color = '#e74c3c';
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    icon.style.color = '';
                }
                
                // Cập nhật số lượt thích
                const likeCount = this.closest('.post').querySelector('.post-stats span:first-child');
                likeCount.innerHTML = `<i class="fas fa-heart"></i> ${data.count}`;
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });
});

// Xử lý bình luận
document.querySelectorAll('.comment-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const post = this.closest('.post');
        const commentsSection = post.querySelector('.comments-section');
        const commentsList = commentsSection.querySelector('.comments-list');
        
        if (commentsSection.style.display === 'none') {
            // Hiển thị loading
            commentsList.innerHTML = '<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải bình luận...</p>';
            commentsSection.style.display = 'block';
            
            try {
                const response = await fetch(`xu-ly/binh-luan.php?bai_viet_id=${post.dataset.postId}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    if (data.comments.length > 0) {
                        commentsList.innerHTML = data.comments.map(comment => `
                            <div class="comment">
                                <img src="uploads/avatars/${comment.anh_dai_dien}" alt="Avatar" class="avatar">
                                <div class="comment-content">
                                    <h6>${comment.ho_ten}</h6>
                                    <p>${comment.noi_dung}</p>
                                    <small>${time_elapsed_string(comment.ngay_binh_luan)}</small>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        commentsList.innerHTML = '<p class="text-center text-muted">Chưa có bình luận nào</p>';
                    }
                } else {
                    commentsList.innerHTML = '<p class="text-center text-danger">Không thể tải bình luận</p>';
                }
            } catch (error) {
                console.error('Error:', error);
                commentsList.innerHTML = '<p class="text-center text-danger">Có lỗi xảy ra khi tải bình luận</p>';
            }
        } else {
            commentsSection.style.display = 'none';
        }
    });
});

// Xử lý gửi bình luận
document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const post = this.closest('.post');
        const input = this.querySelector('.comment-input');
        const commentsList = post.querySelector('.comments-list');
        
        if (!input.value.trim()) return;
        
        try {
            const response = await fetch('xu-ly/binh-luan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `bai_viet_id=${post.dataset.postId}&noi_dung=${encodeURIComponent(input.value)}`
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                // Thêm bình luận mới vào đầu danh sách
                const commentHTML = `
                    <div class="comment">
                        <img src="uploads/avatars/${data.comment.anh_dai_dien}" alt="Avatar" class="avatar">
                        <div class="comment-content">
                            <h6>${data.comment.ho_ten}</h6>
                            <p>${data.comment.noi_dung}</p>
                            <small>${time_elapsed_string(data.comment.ngay_binh_luan)}</small>
                        </div>
                    </div>
                `;
                commentsList.insertAdjacentHTML('afterbegin', commentHTML);
                
                // Reset input
                input.value = '';
                
                // Cập nhật số lượng bình luận
                const commentCount = post.querySelector('.post-stats span:last-child');
                const currentCount = parseInt(commentCount.textContent.match(/\d+/)[0]);
                commentCount.innerHTML = `<i class="fas fa-comment"></i> ${currentCount + 1}`;
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });
});

// Xử lý nút Ảnh/Video
document.querySelector('.action-btn:first-child').addEventListener('click', function() {
    // Mở modal đăng bài
    const createPostModal = new bootstrap.Modal(document.getElementById('createPostModal'));
    createPostModal.show();
    
    // Tự động mở file chooser
    setTimeout(() => {
        document.getElementById('postImage').click();
    }, 500);
});

// Xử lý xóa bài viết
document.querySelectorAll('.btn-delete-post').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Bạn có chắc chắn muốn xóa bài viết này?')) return;
        
        const postId = this.dataset.postId;
        const post = this.closest('.post');
        
        try {
            const response = await fetch('xu-ly/xoa-bai.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `bai_viet_id=${postId}`
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                // Xóa bài viết khỏi DOM với animation
                post.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => post.remove(), 300);
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xóa bài viết!');
        }
    });
}); 