// Thêm vào đầu file
console.log('Trang-chu.js đã được tải');

// Thêm hàm time_elapsed_string
function time_elapsed_string(datetime) {
    const now = new Date();
    const past = new Date(datetime);
    const diff = Math.floor((now - past) / 1000);
    
    if (diff < 60) {
        return 'Vừa xong';
    } else if (diff < 3600) {
        const minutes = Math.floor(diff / 60);
        return minutes + ' phút trước';
    } else if (diff < 86400) {
        const hours = Math.floor(diff / 3600);
        return hours + ' giờ trước';
    } else if (diff < 604800) {
        const days = Math.floor(diff / 86400);
        return days + ' ngày trước';
    } else {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return past.toLocaleDateString('vi-VN', options);
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

// Xử lý hiển thị bình luận
document.querySelectorAll('.comment-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const postId = this.dataset.postId;
        const post = document.querySelector(`.post[data-post-id="${postId}"]`);
        const commentsContainer = post.querySelector('.comments-container');
        const commentsList = post.querySelector('.comments-list');
        
        // Toggle hiển thị container bình luận
        if (commentsContainer.style.display === 'none') {
            commentsContainer.style.display = 'block';
            
            // Hiển thị loading
            commentsList.innerHTML = '';
            post.querySelector('.comments-loading').style.display = 'block';
            
            try {
                const response = await fetch(`xu-ly/binh-luan.php?bai_viet_id=${postId}`);
                const data = await response.json();
                
                // Ẩn loading
                post.querySelector('.comments-loading').style.display = 'none';
                
                if (data.status === 'success') {
                    if (data.comments.length > 0) {
                        commentsList.innerHTML = data.comments.map(comment => `
                            <div class="comment">
                                <img src="uploads/avatars/${comment.anh_dai_dien}" alt="Avatar" class="avatar">
                                <div class="comment-content">
                                    <div class="comment-header">
                                        <span class="comment-author">${comment.ho_ten}</span>
                                        <span class="comment-time">${time_elapsed_string(comment.ngay_binh_luan)}</span>
                                    </div>
                                    <p>${comment.noi_dung}</p>
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
                console.error('Lỗi:', error);
                post.querySelector('.comments-loading').style.display = 'none';
                commentsList.innerHTML = '<p class="text-center text-danger">Có lỗi xảy ra khi tải bình luận</p>';
            }
        } else {
            commentsContainer.style.display = 'none';
        }
    });
});

// Xử lý gửi bình luận
document.addEventListener('DOMContentLoaded', function() {
    console.log('Đang thiết lập xử lý bình luận...');
    
    document.querySelectorAll('.comment-form').forEach(form => {
        console.log('Tìm thấy form bình luận:', form);
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Form bình luận được submit');
            
            const postId = this.dataset.postId;
            const commentInput = this.querySelector('input[name="noi_dung"]');
            const commentText = commentInput.value.trim();
            
            console.log('Bài viết ID:', postId);
            console.log('Nội dung bình luận:', commentText);
            
            if (!commentText) {
                console.log('Nội dung bình luận trống');
                return;
            }
            
            const formData = new FormData();
            formData.append('bai_viet_id', postId);
            formData.append('noi_dung', commentText);
            
            try {
                console.log('Gửi request bình luận...');
                const response = await fetch('xu-ly/binh-luan.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                console.log('Kết quả bình luận:', data);
                
                if (data.status === 'success') {
                    // Hiển thị container bình luận nếu đang ẩn
                    const commentsContainer = document.querySelector(`.post[data-post-id="${postId}"] .comments-container`);
                    if (commentsContainer.style.display === 'none') {
                        commentsContainer.style.display = 'block';
                    }
                    
                    // Thêm bình luận mới vào DOM
                    const commentsList = document.querySelector(`.post[data-post-id="${postId}"] .comments-list`);
                    
                    const commentHtml = `
                        <div class="comment">
                            <img src="uploads/avatars/${data.comment.anh_dai_dien}" alt="Avatar" class="avatar">
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-author">${data.comment.ho_ten}</span>
                                    <span class="comment-time">Vừa xong</span>
                                </div>
                                <p>${data.comment.noi_dung}</p>
                            </div>
                        </div>
                    `;
                    
                    // Thêm vào đầu danh sách
                    if (commentsList.querySelector('.text-muted')) {
                        // Nếu chưa có bình luận nào, xóa thông báo
                        commentsList.innerHTML = '';
                    }
                    commentsList.insertAdjacentHTML('afterbegin', commentHtml);
                    
                    // Reset input
                    commentInput.value = '';
                    
                    // Cập nhật số lượng bình luận
                    const commentCount = document.querySelector(`.post[data-post-id="${postId}"] .comment-count`);
                    if (commentCount) {
                        const currentCount = parseInt(commentCount.textContent);
                        commentCount.textContent = currentCount + 1;
                    }
                    
                    // Cập nhật thông báo
                    loadNotifications();
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi bình luận');
                }
            } catch (error) {
                console.error('Lỗi khi bình luận:', error);
                alert('Có lỗi xảy ra khi bình luận!');
            }
        });
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

// Xử lý chỉnh sửa bài viết
document.querySelectorAll('.btn-edit-post').forEach(btn => {
    btn.addEventListener('click', async function() {
        const postId = this.dataset.postId;
        const editModal = new bootstrap.Modal(document.getElementById('editPostModal'));
        
        try {
            const response = await fetch(`xu-ly/sua-bai.php?bai_viet_id=${postId}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                // Điền dữ liệu vào form
                document.getElementById('edit_bai_viet_id').value = postId;
                document.getElementById('edit_noi_dung').value = data.data.noi_dung;
                
                // Hiển thị ảnh hiện tại nếu có
                const editImagePreview = document.getElementById('editImagePreview');
                if (data.data.anh) {
                    editImagePreview.innerHTML = `
                        <div class="current-image">
                            <img src="uploads/posts/${data.data.anh}" alt="Current image">
                            <button type="button" class="remove-image" onclick="removeEditImage()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>`;
                } else {
                    editImagePreview.innerHTML = '';
                }
                
                // Hiển thị modal
                editModal.show();
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi tải thông tin bài viết!');
        }
    });
});

// Preview ảnh mới khi chỉnh sửa
document.getElementById('editPostImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('editImagePreview').innerHTML = `
                <div class="image-preview-wrapper">
                    <img src="${e.target.result}" class="img-preview">
                    <button type="button" class="remove-image" onclick="removeEditImage()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>`;
        }
        reader.readAsDataURL(file);
    }
});

// Xóa ảnh khi chỉnh sửa
function removeEditImage() {
    document.getElementById('editPostImage').value = '';
    document.getElementById('editImagePreview').innerHTML = '';
}

// Xử lý submit form chỉnh sửa
document.getElementById('editPostForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const postId = formData.get('bai_viet_id');
    const post = document.querySelector(`.post[data-post-id="${postId}"]`);
    
    try {
        const response = await fetch('xu-ly/sua-bai.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            // Cập nhật nội dung bài viết
            post.querySelector('.post-content p').textContent = data.data.noi_dung;
            
            // Cập nhật ảnh nếu có
            const postImage = post.querySelector('.post-image');
            if (data.data.anh) {
                if (postImage) {
                    postImage.src = `uploads/posts/${data.data.anh}`;
                } else {
                    post.querySelector('.post-content').insertAdjacentHTML(
                        'beforeend',
                        `<img src="uploads/posts/${data.data.anh}" alt="Post image" class="post-image">`
                    );
                }
            } else if (postImage) {
                postImage.remove();
            }
            
            // Đóng modal
            const editModal = bootstrap.Modal.getInstance(document.getElementById('editPostModal'));
            editModal.hide();
            
            // Reset form
            this.reset();
            document.getElementById('editImagePreview').innerHTML = '';
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi cập nhật bài viết!');
    }
});

// Xử lý chia sẻ bài viết
document.querySelectorAll('.share-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const post = this.closest('.post');
        const postId = post.dataset.postId;
        const shareModal = new bootstrap.Modal(document.getElementById('sharePostModal'));
        
        // Lưu ID bài viết vào form
        document.getElementById('share_bai_viet_id').value = postId;
        
        // Tạo preview bài viết gốc
        const preview = document.querySelector('.share-preview');
        preview.innerHTML = `
            <div class="post-content">
                <p>${post.querySelector('.post-content p').textContent}</p>
                ${post.querySelector('.post-image') ? 
                    `<img src="${post.querySelector('.post-image').src}" alt="Post image">` : 
                    ''}
            </div>
        `;
        
        shareModal.show();
    });
});

// Xử lý submit form chia sẻ
document.getElementById('sharePostForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('xu-ly/chia-se.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            // Đóng modal
            const shareModal = bootstrap.Modal.getInstance(document.getElementById('sharePostModal'));
            shareModal.hide();
            
            // Reset form
            this.reset();
            
            // Reload trang để hiển thị bài viết mới
            location.reload();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi chia sẻ bài viết!');
    }
});

// Xử lý thông báo
function loadNotifications() {
    console.log('Đang tải thông báo...');
    fetch('xu-ly/thong-bao.php?action=get_thong_bao')
        .then(response => response.json())
        .then(data => {
            console.log('Dữ liệu thông báo:', data);
            if (data.status === 'success') {
                updateNotificationBadge(data.chua_doc);
                renderNotifications(data.thong_bao);
            }
        })
        .catch(error => console.error('Lỗi khi tải thông báo:', error));
}

function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-count');
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'block';
    } else {
        badge.style.display = 'none';
    }
}

function renderNotifications(notifications) {
    const container = document.querySelector('.notifications-list');
    
    if (!notifications || notifications.length === 0) {
        container.innerHTML = `
            <div class="no-notifications">
                <p>Không có thông báo mới</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    notifications.forEach(notification => {
        const isUnread = notification.da_doc == 0 ? 'unread' : '';
        const time = time_elapsed_string(notification.ngay_tao);
        
        html += `
            <div class="notification-item ${isUnread}" 
                 data-id="${notification.id}" 
                 data-type="${notification.loai}" 
                 data-link="${notification.lien_ket_id}">
                <img src="uploads/avatars/${notification.anh_dai_dien || 'default.jpg'}" alt="Avatar" class="notification-avatar">
                <div class="notification-content">
                    <p class="notification-text">${notification.noi_dung}</p>
                    <span class="notification-time">${time}</span>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Thêm event listener cho các thông báo
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            const id = this.dataset.id;
            const type = this.dataset.type;
            const linkId = this.dataset.link;
            
            // Đánh dấu đã đọc
            markAsRead(id);
            
            // Xử lý chuyển hướng dựa vào loại thông báo
            if (type === 'ket_ban') {
                // Chuyển đến trang bạn bè
                window.location.href = 'ban-be.php';
            } else if (type === 'binh_luan' || type === 'thich') {
                // Chuyển đến bài viết
                window.location.href = `trang-chu.php#post-${linkId}`;
            } else if (type === 'tin_nhan') {
                // Chuyển đến tin nhắn (nếu có)
                window.location.href = `tin-nhan.php?id=${linkId}`;
            }
        });
    });
}

function markAsRead(id) {
    const formData = new FormData();
    formData.append('action', 'danh_dau_da_doc');
    if (id) formData.append('thong_bao_id', id);
    
    fetch('xu-ly/thong-bao.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            loadNotifications();
        }
    })
    .catch(error => console.error('Lỗi:', error));
}

// Đánh dấu tất cả đã đọc
document.querySelector('.mark-all-read').addEventListener('click', function() {
    markAsRead();
});

// Load thông báo khi trang được tải
document.addEventListener('DOMContentLoaded', function() {
    console.log('Trang đã tải xong, đang tải thông báo...');
    loadNotifications();
    
    // Cập nhật thông báo mỗi 30 giây
    setInterval(loadNotifications, 30000);
}); 