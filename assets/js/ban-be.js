// Xử lý chấp nhận lời mời kết bạn
document.querySelectorAll('.btn-accept').forEach(btn => {
    btn.addEventListener('click', async function() {
        const requestId = this.dataset.requestId;
        const requestCard = this.closest('.friend-request-card');
        
        try {
            const response = await fetch('xu-ly/ket-ban.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=dong_y&ket_ban_id=${requestId}`
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                // Hiển thị thông báo thành công
                alert('Đã chấp nhận lời mời kết bạn');
                
                // Xóa card lời mời
                requestCard.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => {
                    requestCard.remove();
                    
                    // Cập nhật số lượng lời mời
                    const requestCount = document.querySelectorAll('.friend-request-card').length;
                    const requestTitle = document.querySelector('.friend-requests-section h5');
                    if (requestTitle) {
                        requestTitle.textContent = `Lời mời kết bạn (${requestCount})`;
                    }
                    
                    // Nếu không còn lời mời nào, ẩn section
                    if (requestCount === 0) {
                        document.querySelector('.friend-requests-section').style.display = 'none';
                    }
                    
                    // Thêm người dùng vào danh sách bạn bè (cần reload để hiển thị đúng)
                    location.reload();
                }, 300);
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi chấp nhận lời mời kết bạn!');
        }
    });
});

// Xử lý từ chối lời mời kết bạn
document.querySelectorAll('.btn-decline').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Bạn có chắc chắn muốn từ chối lời mời kết bạn này?')) return;
        
        const requestId = this.dataset.requestId;
        const requestCard = this.closest('.friend-request-card');
        
        try {
            const response = await fetch('xu-ly/ket-ban.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=tu_choi&ket_ban_id=${requestId}`
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                // Xóa card lời mời
                requestCard.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => {
                    requestCard.remove();
                    
                    // Cập nhật số lượng lời mời
                    const requestCount = document.querySelectorAll('.friend-request-card').length;
                    const requestTitle = document.querySelector('.friend-requests-section h5');
                    if (requestTitle) {
                        requestTitle.textContent = `Lời mời kết bạn (${requestCount})`;
                    }
                    
                    // Nếu không còn lời mời nào, ẩn section
                    if (requestCount === 0) {
                        document.querySelector('.friend-requests-section').style.display = 'none';
                    }
                }, 300);
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi từ chối lời mời kết bạn!');
        }
    });
});

// Xử lý hủy kết bạn
document.querySelectorAll('.btn-unfriend').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Bạn có chắc chắn muốn hủy kết bạn với người này?')) return;
        
        const friendId = this.dataset.friendId;
        const friendCard = this.closest('.friend-card');
        
        try {
            const response = await fetch('xu-ly/ket-ban.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=huy_ket_ban&ban_be_id=${friendId}`
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                // Xóa card bạn bè
                friendCard.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => {
                    friendCard.remove();
                    
                    // Cập nhật số lượng bạn bè
                    const friendCount = document.querySelectorAll('.friend-card').length;
                    const friendTitle = document.querySelector('.friends-list-section h5');
                    if (friendTitle) {
                        friendTitle.textContent = `Tất cả bạn bè (${friendCount})`;
                    }
                    
                    // Nếu không còn bạn bè nào, hiển thị trạng thái trống
                    if (friendCount === 0) {
                        const emptyState = `
                            <div class="empty-state">
                                <i class="fas fa-user-friends"></i>
                                <p>Bạn chưa có bạn bè nào</p>
                            </div>
                        `;
                        document.querySelector('.friends-list').innerHTML = emptyState;
                    }
                }, 300);
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi hủy kết bạn!');
        }
    });
});

// Xử lý gửi lời mời kết bạn
document.querySelectorAll('.btn-add-friend').forEach(btn => {
    btn.addEventListener('click', async function() {
        const userId = this.dataset.userId;
        const suggestionCard = this.closest('.friend-suggestion-card');
        
        try {
            const response = await fetch('xu-ly/ket-ban.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=gui_loi_moi&nguoi_nhan_id=${userId}`
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
                // Thay đổi nút thành "Đã gửi lời mời"
                this.innerHTML = '<i class="fas fa-check"></i> Đã gửi lời mời';
                this.classList.remove('btn-primary');
                this.classList.add('btn-secondary');
                this.disabled = true;
                
                // Hiển thị thông báo
                alert('Đã gửi lời mời kết bạn');
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi gửi lời mời kết bạn!');
        }
    });
});

// Thêm animation fadeOut
document.head.insertAdjacentHTML('beforeend', `
    <style>
        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-10px);
                height: 0;
                margin: 0;
                padding: 0;
                overflow: hidden;
            }
        }
    </style>
`); 