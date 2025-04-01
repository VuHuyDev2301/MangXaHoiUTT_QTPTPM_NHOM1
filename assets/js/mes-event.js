let currentFriendId = null;

    const chatBox = document.getElementById('chat-box');
        setInterval(() => {
            loadChat();
        }, 1000);
        function scrollToBottom(){
            chatBox.scrollTop = chatBox.scrollHeight;
        }
        setInterval(() => {
            scrollToBottom();
        }, 5000);

        function loadChat(friendId) {
            if (!friendId && currentFriendId) {
                friendId = currentFriendId;
            }
            if (!friendId) return;
            
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `xu-ly/lay-tin-nhan.php?user_id=${friendId}`, true);
            xhr.onload = () => {
                if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                    chatBox.innerHTML = xhr.response;
                }   
            };
            xhr.send();
        }

	

	function sendMessage() {
		const messageInput = document.getElementById('message');
		const message = messageInput.value.trim();
		
		if (!message) return;
		
		// Get friend_id from URL if not set
		if (!currentFriendId) {
			const urlParams = new URLSearchParams(window.location.search);
			currentFriendId = urlParams.get('ban_be_id');
		}

		if (!currentFriendId) return;

		const formData = new FormData();
		formData.append('message', message);
		formData.append('friend_id', currentFriendId);

		fetch('messenger.php', {
			method: 'POST',
			body: formData
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				messageInput.value = '';
				loadChat(currentFriendId);
			}
		})
		.catch(error => console.error('Error:', error));
	}


	function loadUserInfo(friendId) {
		if (!friendId) return;
		
		const xhr = new XMLHttpRequest();
		xhr.open('GET', `xu-ly/lay-thong-tin.php?user_id=${friendId}`, true);
		xhr.onload = () => {
			if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
				document.getElementById('chat-header-info').innerHTML = xhr.response;
			}
		};
		xhr.send();
	}

	setInterval(() => {
	    const urlParams = new URLSearchParams(window.location.search);
	    const friendId = urlParams.get('ban_be_id');
	    if (friendId) {
	        loadUserInfo(friendId);
	    }
	}, 1000);
	
    document.addEventListener('DOMContentLoaded', function () {
        console.log('DOM đã tải');
    
        const friends = document.querySelectorAll('.friend');
    
        if (friends.length === 0) {
            console.warn('Không tìm thấy phần tử .friend nào!');
        }
    
        friends.forEach(friend => {
            friend.addEventListener('click', async function () {
                const friendId = this.dataset.friendId;
                console.log('Clicked friendId:', friendId);
    
                if (!friendId) {
                    console.error('Lỗi: Không lấy được friendId!');
                    return;
                }
    
                // Bỏ class active ở các bạn bè khác
                friends.forEach(f => f.classList.remove('active'));
                this.classList.add('active');
    
                currentFriendId = friendId;
    
                console.log('Cập nhật trạng thái: Đang gửi AJAX update-status.php');
    
                // Gửi AJAX để cập nhật trạng thái tin nhắn
                try {
                    const response = await fetch('xu-ly/update-status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `ban_be_id=${encodeURIComponent(friendId)}`
                    });
    
                    console.log('Phản hồi update-status.php:', response.status);
    
                    const result = await response.json();
                    console.log('Kết quả:', result);
    
                    if (!result.success) {
                        console.warn('Cập nhật trạng thái thất bại:', result.message);
                    }
                } catch (error) {
                    console.error('Lỗi AJAX:', error);
                }
    
                console.log('Gọi loadChat() với friendId:', friendId);
                loadChat(friendId);
    
                console.log('Cập nhật URL:', `messenger.php?ban_be_id=${friendId}`);
                window.history.pushState({}, '', `messenger.php?ban_be_id=${friendId}`);
            });
        });
    });
    

    document.addEventListener('DOMContentLoaded', function() {
        const messageInput = document.getElementById('message');
        const plusButton = document.querySelector('.fa-plus-circle');
        const emojiButton = document.querySelector('.fa-smile');
        const sendButton = document.querySelector('.fa-paper-plane').parentElement;
            sendButton.addEventListener('click', function() {
                messageInput.value = '';
            });
    });
            
    function searchFriend(keyword) {
        let friends = document.querySelectorAll('.friend');
        keyword = keyword.toLowerCase();
        friends.forEach(friend => {
          let name = friend.querySelector('.friend-name').textContent.toLowerCase();
          friend.style.display = name.includes(keyword) ? "flex" : "none";
        });
    } 
    
    