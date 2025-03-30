// const chatBody = document.querySelector(".chat-body");
// const messageInput = document.querySelector(".message-input");
// const sendMessageButton = document.querySelector("#send-message");
// const fileInput = document.querySelector("#file-input");
// const fileUploadWrapper = document.querySelector(".file-upload-wrapper");
// const fileCancelButton = document.querySelector("#file-cancel");
// const chatbotToggler = document.querySelector("#chatbot-toggler");
// const closeChatbot = document.querySelector("#close-chatbot");
// const chatbotBody = document.querySelector(".chatbot-body");
// const chatPopup = document.querySelector(".chatbot-popup");
// const chatHistory = [];
// const initialInputHeight = messageInput.scrollHeight;

// //thiet lap api
// const API_KEY ="AIzaSyA49PnQFgbl4w2F603ofhP-C6fp_fCDonM";
// const API_URL= `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=${API_KEY}`;

// const userData = {
// 	message: null,
// 	file: {
// 		data:null,
// 		mime_type:null
// 	}
// }
// //Tao phan tin nhan voi lop dong va tra no ve
// const createMessageElement = (content, ...classes) => {
// 	const div = document.createElement("div");
// 	div.classList.add("message", ...classes);
// 	div.innerHTML = content;
// 	return div;
// }


// // Hàm chuẩn hóa chuỗi (loại bỏ dấu và khoảng trắng thừa)
// const normalizeString = (str) => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim().toLowerCase();

// // Hàm kiểm tra từ khóa trong tin nhắn
// const checkForKeywordResponse = async (message) => {
// 	const keywords = Object.keys(keywordResponses);

// 	// Chuẩn hóa tin nhắn người dùng
// 	const normalizedMessage = normalizeString(message);

// 	for (let keyword of keywords) {
// 		const words = keyword.split(/[,\s]+/); 
// 		const regexPattern = words.map(word => `\\b${normalizeString(word)}\\b`).join(".*"); 
// 		const regex = new RegExp(regexPattern, "i");

// 		if (regex.test(normalizedMessage)) {
// 			const response = keywordResponses[keyword];
// 			return typeof response === "function" ? await response() : response; 
// 		}
// 	}

// 	return null; 
// };



// //Bot phan hoi qua API
// const generateBotReponse = async (incomingMessageDiv) => {
// 	const messageElement = incomingMessageDiv.querySelector(".message-text");
	
// 	//Lich su tro chuyen cua user
// 	chatHistory.push({
// 		role: "user",
// 		parts: [{ text: userData.message }, ...(userData.file.data ? [{inline_data: userData.file}] : [])]
// 	});
	

// 	//cau hinh API request 
// 	const requestOptions = {
// 		method: "POST",
// 		headers: {"Content-Type": "application/json"},
// 		body: JSON.stringify({
// 			contents: chatHistory
// 		})
// 	}
// 	try {
// 		const response = await fetch(API_URL,  requestOptions);
// 		const data = await response.json();
// 		if(!response.ok) throw new Error(data.error.message);

// 		//Hien thi van ban phan hoi cua bot
// 		const apiResponseText = data.candidates[0].content.parts[0].text.replace(/\*\*(.*?)\*\*/g, "$1").trim();
// 		messageElement.innerText = apiResponseText;

// 		//Lich su tro chuyen cua bot
// 		chatHistory.push({
// 		role: "model",
// 		parts: [{ text: apiResponseText }]
// 	});

// 	} catch (error) {
// 		console.log(error);
// 		messageElement.innerText = error.message;
// 		messageElement.style.color = "#ff0000";
// 	}

// 	finally{
// 		//Thiet lap lai tep du lieu nguoi dung, loai bo thinking (...) va tu dong dieu huog xuong cuoi doan chat
// 		userData.file = {};
// 		incomingMessageDiv.classList.remove("thinking");
// 		chatBody.scrollTo({top: chatBody.scrollHeight, behavior: "smooth"});
// 	}
// }



// //Xu ly tep dau vao
// fileInput.addEventListener("change" , () => {
// 	const file = fileInput.files[0];
// 	if(!file) return;

// 	const reader = new FileReader();
// 	reader.onload = (e) => {
// 		fileUploadWrapper.querySelector("img").src = e.target.result;
// 		fileUploadWrapper.classList.add("file-uploaded");
// 		const base64String = e.target.result.split(",")[1];

// 		//Luu tru tep trong duLieu nguoi dung
// 		userData.file = {
// 			data:base64String,
// 			mime_type:file.type
// 		}
// 		fileInput.value = "";
// 	}


// 	reader.readAsDataURL(file);
// });

// fileCancelButton.addEventListener("click", () => {
// 	userData.file = {};
// 	fileUploadWrapper.classList.remove("file-uploaded");
// });


// sendMessageButton.addEventListener("click", (e) => handleOutgoingMessage(e));
// document.querySelector("#file-upload").addEventListener("click", () => fileInput.click());

// chatbotToggler.addEventListener("click", () => {
// chatbotBody.classList.toggle("show-chatbot");
// });

// // Xử lý từ khóa
// const keywordResponses = {
// 	"help": "Sure! Please tell me more about what you need help with.",
// 	"thanks": "You're welcome! Let me know if you need anything else.",
// };

// // Sự kiện mở cửa sổ chat mới khi nhấn vào phần tử .friend
// document.querySelectorAll(".friend").forEach(friend => {
// 	friend.addEventListener("click", function () {
// 		let friendId = this.getAttribute("data-friend-id");
// 		let friendName = this.textContent.trim();
		
// 		// Nếu cửa sổ chat với bạn này đã mở, không tạo lại
// 		if (document.getElementById(`chat-${friendId}`)) return;

// 		// Tạo cửa sổ chat mới
// 		let chatPopup = document.createElement("div");
// 		chatPopup.classList.add("chatbot-popup");
// 		chatPopup.id = `chat-${friendId}`;
// 		chatPopup.innerHTML = `
// 			<div class="chat-header">
// 			<div class="header-info">
// 				<img class="chatbot-logo" src="uploads/avatars/default.jpg" alt="Avatar">
// 				<h2 class="logo-text">${friendName}</h2>
// 			</div>
// 			<button class="close-chatbot material-symbols-rounded" data-chat-id="${friendId}">
// 				keyboard_arrow_down
// 			</button>
// 			</div>
// 			<div class="chat-body">
//             <div class="message bot-message">
//                 <svg class="bot-avatar" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 1024 1024">
//                     <path d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4.7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5zM867.2 644.5V453.1h26.5c19.4 0 35.1 15.7 35.1 35.1v121.1c0 19.4-15.7 35.1-35.1 35.1h-26.5zM95.2 609.4V488.2c0-19.4 15.7-35.1 35.1-35.1h26.5v191.3h-26.5c-19.4 0-35.1-15.7-35.1-35.1zM561.5 149.6c0 23.4-15.6 43.3-36.9 49.7v44.9h-30v-44.9c-21.4-6.5-36.9-26.3-36.9-49.7 0-28.6 23.3-51.9 51.9-51.9s51.9 23.3 51.9 51.9z"></path>
//                 </svg>
//                 <div class="message-text">
//                     Hey there 👋 <br> How can I help you today?
//                 </div> 
//             </div>
//         	</div>

// 			<!-- Chat Footer -->
// 			<div class="chat-footer">
// 				<form action="#" class="chat-form">
// 					<textarea placeholder="Message..." class="message-input" required></textarea>
// 					<div class="chat-controls">
// 						<div class="file-upload-wrapper">
// 							<input type="file" accept="./uploads/avatars/*" id="file-input" hidden>
// 							<img src="#">
// 							<button type="button" id="file-upload" class="material-symbols-rounded">attach_file</button>
// 							<button type="button" id="file-cancel" class="material-symbols-rounded">close</button>
// 						</div>
// 						<button type="submit" id="send-message" class="material-symbols-rounded">arrow_upward</button>
// 					</div>
// 				</form>
// 			</div>
// 		`;

// 		// Thêm chatPopup vào chat-container
// 		document.getElementById("chat-container").appendChild(chatPopup);
// 	});
// });

// // Event delegation for chat container
// document.getElementById("chat-container").addEventListener("click", function(e) {
// 	// Handle close button
// 	if (e.target.classList.contains("close-chatbot")) {
// 		const chatId = `chat-${e.target.dataset.chatId}`;
// 		closeChat(chatId);
// 	}
// 	if (e.target.classList.contains("send-message")) {
// 		handleOutgoingMessage(e);
// 	}
// 	if (e.target.classList.contains("file-upload")) {
// 		document.querySelector("#file-input").click();
// 	}
// 	if (e.target.classList.contains("file-cancel")) {
// 		document.querySelector("#file-input").value = "";
// 		document.querySelector(".file-upload-wrapper").classList.remove("file-uploaded");
// 	}
// });

// // Hàm đóng cửa sổ chat
// function closeChat(chatId) {
//     let chatPopup = document.getElementById(chatId);
//     if (chatPopup) chatPopup.remove();
// }
const chatBody = document.querySelector(".chat-body");
const messageInput = document.querySelector(".message-input");
const sendMessageButton = document.querySelector("#send-message");
const fileInput = document.querySelector("#file-input");
const fileUploadWrapper = document.querySelector(".file-upload-wrapper");
const fileCancelButton = document.querySelector("#file-cancel");
const friends = document.querySelectorAll(".friend");
const closeChatbot = document.querySelector("#close-chatbot");
const chatbotBody = document.querySelector(".chatbot-body");

const chatHistory = [];
const initialInputHeight = messageInput.scrollHeight;

//thiet lap api
const API_KEY ="AIzaSyA49PnQFgbl4w2F603ofhP-C6fp_fCDonM";
const API_URL= `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=${API_KEY}`;

const userData = {
	message: null,
	file: {
		data:null,
		mime_type:null
	}
}
//Tao phan tin nhan voi lop dong va tra no ve
const createMessageElement = (content, ...classes) => {
	const div = document.createElement("div");
	div.classList.add("message", ...classes);
	div.innerHTML = content;
	return div;
}                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              


// Hàm chuẩn hóa chuỗi (loại bỏ dấu và khoảng trắng thừa)
const normalizeString = (str) => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim().toLowerCase();

// Hàm kiểm tra từ khóa trong tin nhắn
const checkForKeywordResponse = async (message) => {
const keywords = Object.keys(keywordResponses);

// Chuẩn hóa tin nhắn người dùng
const normalizedMessage = normalizeString(message);

for (let keyword of keywords) {
	const words = keyword.split(/[,\s]+/); 
	const regexPattern = words.map(word => `\\b${normalizeString(word)}\\b`).join(".*"); 
	const regex = new RegExp(regexPattern, "i");

	if (regex.test(normalizedMessage)) {
		const response = keywordResponses[keyword];
		return typeof response === "function" ? await response() : response; 
	}
}

return null; 
};



//Bot phan hoi qua API
const generateBotReponse = async (incomingMessageDiv) => {
	const messageElement = incomingMessageDiv.querySelector(".message-text");
	
	//Lich su tro chuyen cua user
	chatHistory.push({
		role: "user",
		parts: [{ text: userData.message }, ...(userData.file.data ? [{inline_data: userData.file}] : [])]
	});

	//cau hinh API request 
	const requestOptions = {
		method: "POST",
		headers: {"Content-Type": "application/json"},
		body: JSON.stringify({
			contents: chatHistory
		})
	}
	try {
		const response = await fetch(API_URL,  requestOptions);
		const data = await response.json();
		if(!response.ok) throw new Error(data.error.message);

		//Hien thi van ban phan hoi cua bot
		const apiResponseText = data.candidates[0].content.parts[0].text.replace(/\*\*(.*?)\*\*/g, "$1").trim();
		messageElement.innerText = apiResponseText;

		//Lich su tro chuyen cua bot
		chatHistory.push({
		role: "model",
		parts: [{ text: apiResponseText }]
	});

	} catch (error) {
		console.log(error);
		messageElement.innerText = error.message;
		messageElement.style.color = "#ff0000";
	}

	finally{
		//Thiet lap lai tep du lieu nguoi dung, loai bo thinking (...) va tu dong dieu huog xuong cuoi doan chat
		userData.file = {};
		incomingMessageDiv.classList.remove("thinking");
		chatBody.scrollTo({top: chatBody.scrollHeight, behavior: "smooth"});
	}
}


// Phản hồi tin nhắn người dùng
const handleOutgoingMessage = async (e) => {
	e.preventDefault();
	userData.message = messageInput.value.trim();
	messageInput.value = "";

	fileUploadWrapper.classList.remove("file-uploaded");
	messageInput.dispatchEvent(new Event("input"));

	// Tạo và hiển thị phần tin nhắn người dùng
	const messageContent = `<div class="message-text"></div>
							${userData.file.data ? `<img src="data:${userData.file.mime_type};base64,${userData.file.data}" class="attachment"/> `: "" }`;
	
	const outgoingMessageDiv = createMessageElement(messageContent, "user-message");
	outgoingMessageDiv.querySelector(".message-text").textContent = userData.message;
	chatBody.appendChild(outgoingMessageDiv);
	chatBody.scrollTo({top: chatBody.scrollHeight, behavior: "smooth"});

	// Kiểm tra từ khóa và phản hồi tự động nếu có
	const keywordResponse = await checkForKeywordResponse(userData.message);
	if (keywordResponse) {

		const formattedResponse = keywordResponse
		.replace(/\n/g, "<br>** ")  
		.replace(/^/, "** ");
		// Tạo và hiển thị phần tin nhắn phản hồi tự động
		
		const messageContent = `<svg class="bot-avatar" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 1024 1024">
						<path d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4.7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5zM867.2 644.5V453.1h26.5c19.4 0 35.1 15.7 35.1 35.1v121.1c0 19.4-15.7 35.1-35.1 35.1h-26.5zM95.2 609.4V488.2c0-19.4 15.7-35.1 35.1-35.1h26.5v191.3h-26.5c-19.4 0-35.1-15.7-35.1-35.1zM561.5 149.6c0 23.4-15.6 43.3-36.9 49.7v44.9h-30v-44.9c-21.4-6.5-36.9-26.3-36.9-49.7 0-28.6 23.3-51.9 51.9-51.9s51.9 23.3 51.9 51.9z"></path>
					</svg><div class="message-text">${formattedResponse}</div>`;
		const incomingMessageDiv = createMessageElement(messageContent, "bot-message");
		chatBody.appendChild(incomingMessageDiv);
		chatBody.scrollTo({top: chatBody.scrollHeight, behavior: "smooth"});
	} else {
		// Nếu không có từ khóa, tạo tin nhắn bot phản hồi thông qua API
		setTimeout(() => {
			const messageContent = `<svg class="bot-avatar" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 1024 1024">
						<path d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4.7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5zM867.2 644.5V453.1h26.5c19.4 0 35.1 15.7 35.1 35.1v121.1c0 19.4-15.7 35.1-35.1 35.1h-26.5zM95.2 609.4V488.2c0-19.4 15.7-35.1 35.1-35.1h26.5v191.3h-26.5c-19.4 0-35.1-15.7-35.1-35.1zM561.5 149.6c0 23.4-15.6 43.3-36.9 49.7v44.9h-30v-44.9c-21.4-6.5-36.9-26.3-36.9-49.7 0-28.6 23.3-51.9 51.9-51.9s51.9 23.3 51.9 51.9z"></path>
					</svg>
					<div class="message-text">
						<div class="thinking-indicator">
							<div class="dot"></div>
							<div class="dot"></div>
							<div class="dot"></div>
						</div>
					</div>`;

			const incomingMessageDiv = createMessageElement(messageContent, "bot-message","thinking");
			chatBody.appendChild(incomingMessageDiv);
			chatBody.scrollTo({top: chatBody.scrollHeight, behavior: "smooth"});
			generateBotReponse(incomingMessageDiv);
		}, 600);
	}
};
//Xu ly khi an nut enter de gui chat
messageInput.addEventListener("keydown", (e) => {
	const userMessage = e.target.value.trim();
	if(e.key === "Enter" && userMessage && !e.shiftKey && window.innerWidth > 768) {
		handleOutgoingMessage(e);
	}
});

//Tu dong dieu chinh vung nhap lieu
messageInput.addEventListener("input", () => {
	messageInput.style.height = `${initialInputHeight}px`;
	messageInput.style.height = `${messageInput.scrollHeight}px`;
	document.querySelector(".chat-form").style.borderRadius = messageInput.scrollHeight > 
	initialInputHeight ? "15px" : "32px";
});

//Xu ly tep dau vao
fileInput.addEventListener("change" , () => {
	const file = fileInput.files[0];
	if(!file) return;

	const reader = new FileReader();
	reader.onload = (e) => {
		fileUploadWrapper.querySelector("img").src = e.target.result;
		fileUploadWrapper.classList.add("file-uploaded");
		const base64String = e.target.result.split(",")[1];

		//Luu tru tep trong duLieu nguoi dung
		userData.file = {
			data:base64String,
			mime_type:file.type
		}
		fileInput.value = "";
	}


	reader.readAsDataURL(file);
});

fileCancelButton.addEventListener("click", () => {
	userData.file = {};
	fileUploadWrapper.classList.remove("file-uploaded");
});


sendMessageButton.addEventListener("click", (e) => handleOutgoingMessage(e));
document.querySelector("#file-upload").addEventListener("click", () => fileInput.click());


friends.forEach(friend => {
	friend.addEventListener("click", () => {
		chatbotBody.classList.toggle("show-chatbot"); // Hiển thị/tắt chatbot
	});
});

closeChatbot.addEventListener("click", () => {
	chatbotBody.classList.remove("show-chatbot"); // Đóng chatbot
});

// Xử lý từ khóa
const keywordResponses = {
"help": "Sure! Please tell me more about what you need help with.",
"thanks": "You're welcome! Let me know if you need anything else.",
"bye": "Không tiễn bro!",
"địa điểm": "278 Lam Son, Dong Tam, Vinh Yen, Vinh Phuc"}
