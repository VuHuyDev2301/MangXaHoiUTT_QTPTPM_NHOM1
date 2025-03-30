const express = require('express');
const app = express();
const http = require('http').createServer(app);
const io = require('socket.io')(http, {
    cors: {
        origin: "http://localhost", // Adjust this to match your frontend URL
        methods: ["GET", "POST"]
    }
});
// Store active users and their socket connections
const activeUsers = new Map();
// Store active calls to prevent duplicate calls
const activeCalls = new Set();
io.on('connection', (socket) => {
    console.log('A user connected:', socket.id);
    
    // Handle user registration
    socket.on('register-user', (userId) => {
        if (activeUsers.has(userId)) {
            // Nếu user đã đăng ký, sử dụng socketId đã có để thực hiện các công việc khác
            console.log(`User ${userId} already registered with socket ${activeUsers.get(userId)}`);
            // Ví dụ: có thể cập nhật lại trạng thái hay gửi thông tin đến client
            socket.emit('user-already-registered', { socketId: activeUsers.get(userId) });
        } else {
            // Nếu chưa có, đăng ký user với socket.id hiện tại
            activeUsers.set(userId, socket.id);
            console.log(`User ${userId} registered with socket ${socket.id}`);
        }
        console.log('Current active users:', Array.from(activeUsers));
            });
        // Xử lý khi caller gửi yêu cầu gọi
        socket.on('call-user', (data) => {
            const { fromUserId, toUserId } = data;
            const roomID = `room_${fromUserId}_${toUserId}`;
            const callId = `${fromUserId}-${toUserId}`;

            console.log('Call initiation:', { fromUserId, toUserId, callId, roomID });

            // Kiểm tra nếu cuộc gọi đã tồn tại
            if (activeCalls.has(callId)) {
                console.log('Call already exists:', callId);
                socket.emit('call-failed', {
                    message: 'Call already in progress'
                });
                return;
            }

            // Thêm cuộc gọi vào activeCalls
            activeCalls.add(callId);
            console.log('Active calls:', Array.from(activeCalls));

            // Lấy socketId của receiver (người nhận cuộc gọi)
            const receiverSocketId = activeUsers.get(toUserId);
            if (receiverSocketId) {
                // Gửi sự kiện incoming-call đến receiver
                io.to(receiverSocketId).emit('incoming-call', {
                    fromUserId,
                    toUserId,
                    roomID
                });
                console.log(`Incoming call event sent to receiver (${toUserId}) at socket ${receiverSocketId}`);

                // Cho caller cũng tham gia phòng (nếu cần thiết cho WebRTC)
                socket.join(roomID);
                console.log('Caller joined room:', roomID);
            } else {
                console.log('User not found or offline:', toUserId);
                socket.emit('call-failed', {
                    message: 'User is not online'
                });
            }
        });


    socket.on('call-ended', (data) => {
        const { fromUserId, toUserId } = data;  
        const callId = `${fromUserId}-${toUserId}`;
        const roomID = `room_${fromUserId}_${toUserId}`;
        activeCalls.delete(callId);
        console.log('Call ended:', { fromUserId, toUserId, callId, roomID });
        console.log('Updated active calls:', Array.from(activeCalls));
    });

    // Khi receiver chấp nhận cuộc gọi
    socket.on('call-accepted', (data) => {
        const { fromUserId, toUserId } = data;

        // Lấy socketId của caller
        const fromUserSocketId = activeUsers.get(fromUserId);
        const roomID = `room_${fromUserId}_${toUserId}`;

        console.log('Call accepted:', { fromUserId, toUserId, roomID });

        if (fromUserSocketId) {
            // Gán targetUserSocketId cho receiver
            const targetUserSocketId = socket.id; // Socket ID của receiver
            console.log(`Target user socket ID for receiver (${toUserId}):`, targetUserSocketId);

            // Thêm cả hai người dùng vào phòng
            socket.join(roomID);
            io.to(fromUserSocketId).emit('call-accepted', {
                fromUserId,
                toUserId,
                roomID
            });
            io.sockets.sockets.get(fromUserSocketId).join(roomID);
            console.log('Room joined:', roomID);
        }
    });

    // Handle call rejection or end
    socket.on('call-rejected', (data) => {
        const { fromUserId, toUserId } = data;
        const callId = `${fromUserId}-${toUserId}`;
        const fromUserSocketId = activeUsers.get(fromUserId);
        const roomID = `room_${fromUserId}_${toUserId}`;

        console.log('Call rejected:', { fromUserId, toUserId, callId, roomID });

        // Remove call from active calls
        activeCalls.delete(callId);
        console.log('Updated active calls:', Array.from(activeCalls));

        if (fromUserSocketId) {
            socket.leave(roomID);
            io.sockets.sockets.get(fromUserSocketId).leave(roomID);
            io.to(fromUserSocketId).emit('call-rejected', {
                fromUserId,
                toUserId,
                roomID
            });
            console.log('Users left room:', roomID);
        }
    });

    // Handle disconnection
    socket.on('disconnect', () => {
        console.log('User disconnecting:', socket.id);
        for (let [userId, socketId] of activeUsers.entries()) {
            if (socketId === socket.id) {
                console.log('Found disconnecting user:', userId);
                // Clean up active calls for this user
                activeCalls.forEach(callId => {
                    if (callId.includes(userId)) {
                        activeCalls.delete(callId);
                        console.log('Removed call:', callId);
                    }
                });
                activeUsers.delete(userId);
                socket.rooms.forEach(room => {
                    socket.leave(room);
                    console.log('Left room:', room);
                });
                console.log(`User ${userId} disconnected`);
                console.log('Remaining active users:', Array.from(activeUsers));
                break;
            }
        }
    });
});

const PORT = process.env.PORT || 3000;
http.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});