document.addEventListener('DOMContentLoaded', function() {
    console.log('Profile.js đã được tải');
    
    // Xử lý nút kết bạn
    const addFriendBtn = document.querySelector('.btn-add-friend');
    if (addFriendBtn) {
        addFriendBtn.addEventListener('click', async function() {
            const userId = this.dataset.userId;
            
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
                    this.innerHTML = '<i class="fas fa-user-clock"></i> Đã gửi lời mời';
                    this.classList.remove('btn-add-friend');
                    this.classList.add('btn-cancel-request');
                    
                    // Thêm chức năng hủy lời mời
                    this.dataset.action = 'cancel';
                    
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
    }
    
    // Xử lý nút hủy lời mời kết bạn
    const cancelRequestBtn = document.querySelector('.btn-cancel-request');
    if (cancelRequestBtn) {
        cancelRequestBtn.addEventListener('click', function() {
            if (confirm('Bạn có chắc chắn muốn hủy lời mời kết bạn?')) {
                // Thực hiện hủy lời mời kết bạn
                // Sau đó cập nhật UI
                this.innerHTML = '<i class="fas fa-user-plus"></i> Kết bạn';
                this.classList.remove('btn-cancel-request');
                this.classList.add('btn-add-friend');
                this.dataset.action = 'add';
            }
        });
    }
    
    // Xử lý nút chấp nhận lời mời kết bạn
    const acceptRequestBtn = document.querySelector('.btn-accept-request');
    if (acceptRequestBtn) {
        acceptRequestBtn.addEventListener('click', async function() {
            const requestId = this.dataset.requestId;
            
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
                    // Cập nhật UI
                    const friendActions = this.closest('.profile-actions');
                    friendActions.innerHTML = `
                        <button class="btn btn-light btn-message">
                            <i class="fas fa-comment"></i> Nhắn tin
                        </button>
                        <button class="btn btn-light btn-unfriend" data-friend-id="${this.dataset.userId}">
                            <i class="fas fa-user-times"></i> Hủy kết bạn
                        </button>
                    `;
                    
                    // Thêm event listener cho nút hủy kết bạn
                    addUnfriendEventListener();
                    
                    // Hiển thị thông báo
                    alert('Đã chấp nhận lời mời kết bạn');
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi chấp nhận lời mời kết bạn!');
            }
        });
    }
    
    // Xử lý nút từ chối lời mời kết bạn
    const declineRequestBtn = document.querySelector('.btn-decline-request');
    if (declineRequestBtn) {
        declineRequestBtn.addEventListener('click', async function() {
            if (!confirm('Bạn có chắc chắn muốn từ chối lời mời kết bạn này?')) return;
            
            const requestId = this.dataset.requestId;
            
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
                    // Cập nhật UI
                    const friendActions = this.closest('.profile-actions');
                    friendActions.innerHTML = `
                        <button class="btn btn-primary btn-add-friend" data-user-id="${this.dataset.userId}">
                            <i class="fas fa-user-plus"></i> Kết bạn
                        </button>
                    `;
                    
                    // Thêm event listener cho nút kết bạn
                    const newAddFriendBtn = friendActions.querySelector('.btn-add-friend');
                    if (newAddFriendBtn) {
                        newAddFriendBtn.addEventListener('click', addFriendHandler);
                    }
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi từ chối lời mời kết bạn!');
            }
        });
    }
    
    // Xử lý nút hủy kết bạn
    function addUnfriendEventListener() {
        const unfriendBtn = document.querySelector('.btn-unfriend');
        if (unfriendBtn) {
            unfriendBtn.addEventListener('click', async function() {
                if (!confirm('Bạn có chắc chắn muốn hủy kết bạn?')) return;
                
                const friendId = this.dataset.friendId;
                
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
                        // Cập nhật UI
                        const friendActions = this.closest('.profile-actions');
                        friendActions.innerHTML = `
                            <button class="btn btn-primary btn-add-friend" data-user-id="${friendId}">
                                <i class="fas fa-user-plus"></i> Kết bạn
                            </button>
                        `;
                        
                        // Thêm event listener cho nút kết bạn
                        const newAddFriendBtn = friendActions.querySelector('.btn-add-friend');
                        if (newAddFriendBtn) {
                            newAddFriendBtn.addEventListener('click', addFriendHandler);
                        }
                        
                        // Hiển thị thông báo
                        alert('Đã hủy kết bạn');
                    } else {
                        alert(data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi hủy kết bạn!');
                }
            });
        }
    }
    
    // Thêm event listener cho nút hủy kết bạn ban đầu
    addUnfriendEventListener();
    
    // Hàm xử lý sự kiện kết bạn
    function addFriendHandler() {
        const userId = this.dataset.userId;
        
        fetch('xu-ly/ket-ban.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=gui_loi_moi&nguoi_nhan_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Thay đổi nút thành "Đã gửi lời mời"
                this.innerHTML = '<i class="fas fa-user-clock"></i> Đã gửi lời mời';
                this.classList.remove('btn-add-friend');
                this.classList.add('btn-cancel-request');
                
                // Thêm chức năng hủy lời mời
                this.dataset.action = 'cancel';
                
                // Hiển thị thông báo
                alert('Đã gửi lời mời kết bạn');
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi gửi lời mời kết bạn!');
        });
    }
    
    // Xử lý form chỉnh sửa profile
    const editProfileForm = document.getElementById('editProfileForm');
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('xu-ly/cap-nhat-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Đóng modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editProfileModal'));
                    modal.hide();
                    
                    // Cập nhật UI
                    document.querySelector('.profile-details h2').textContent = formData.get('ho_ten');
                    document.querySelector('.profile-meta').textContent = formData.get('khoa') + ' - K' + formData.get('nam_hoc');
                    document.querySelector('.profile-bio').textContent = formData.get('gioi_thieu');
                    
                    // Hiển thị thông báo
                    alert('Đã cập nhật thông tin cá nhân');
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi cập nhật thông tin!');
            });
        });
    }
}); 