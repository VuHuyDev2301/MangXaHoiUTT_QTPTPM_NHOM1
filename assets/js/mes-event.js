
const chatBox = document.getElementById('chat-box');
    // setInterval(() => {
    //     loadChat();
    // }, 500);
    function scrollToBottom(){
        chatBox.scrollTop = chatBox.scrollHeight;
    }

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
                scrollToBottom();
            }   
        };
        xhr.send();
    }

	let currentFriendId = null;

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

	// setInterval(() => {
	//     const urlParams = new URLSearchParams(window.location.search);
	//     const friendId = urlParams.get('ban_be_id');
	//     if (friendId) {
	//         loadUserInfo(friendId);
	//     }
	// }, 500);
	
            document.addEventListener('DOMContentLoaded', function() {
                const friends = document.querySelectorAll('.friend');
                friends.forEach(friend => {
                    friend.addEventListener('click', function() {
                        const friendId = this.dataset.friendId;
                        friends.forEach(f => f.classList.remove('active'));
                        this.classList.add('active');
                        currentFriendId = friendId;
                        loadChat(friendId);
                        window.history.pushState({}, '', 'messenger.php?ban_be_id=' + friendId);
                    });
                });
            });


            document.addEventListener('DOMContentLoaded', function() {
                const messageInput = document.getElementById('message');
                const plusButton = document.querySelector('.fa-plus-circle');
                const emojiButton = document.querySelector('.fa-smile');
                const sendButton = document.querySelector('.fa-paper-plane').parentElement;

                plusButton.addEventListener('click', () => {
                    // Add attachment functionality here
                });

                emojiButton.addEventListener('click', () => {
                    // Add emoji picker functionality here
                });

                sendButton.addEventListener('click', function() {
                    messageInput.value = '';
                });
            });