const express = require('express');
const app = express();
const http = require('http').createServer(app);
const io = require('socket.io')(http, {
    cors: {
        origin: "http://localhost",
        methods: ["GET", "POST"]
    }
});

// Import Google Cloud Speech
const speech = require('@google-cloud/speech');
// Khởi tạo Speech client (đảm bảo biến môi trường GOOGLE_APPLICATION_CREDENTIALS đã được thiết lập)
const speechClient = new speech.SpeechClient();

const activeUsers = new Map();
const activeCalls = new Set();

io.on('connection', (socket) => {
    console.log('A user connected:', socket.id);
    
    socket.on('register-user', (userId) => {
        if (activeUsers.has(userId)) {
            console.log(`User ${userId} already registered with socket ${activeUsers.get(userId)}`);
            socket.emit('user-already-registered', { socketId: activeUsers.get(userId) });
        } else {
            activeUsers.set(userId, socket.id);
            console.log(`User ${userId} registered with socket ${socket.id}`);
        }
        console.log('Current active users:', Array.from(activeUsers));
    });
    
    socket.on('call-user', (data) => {
        const { fromUserId, toUserId } = data;
        const roomID = `room_${fromUserId}_${toUserId}`;
        const callId = `${fromUserId}-${toUserId}`;

        console.log('Call initiation:', { fromUserId, toUserId, callId, roomID });

        if (activeCalls.has(callId)) {
            console.log('Call already exists:', callId);
            socket.emit('call-failed', { message: 'Call already in progress' });
            return;
        }

        activeCalls.add(callId);
        console.log('Active calls:', Array.from(activeCalls));

        const receiverSocketId = activeUsers.get(toUserId);
        if (receiverSocketId) {
            io.to(receiverSocketId).emit('incoming-call', {
                fromUserId,
                toUserId,
                roomID
            });
            console.log(`Incoming call event sent to receiver (${toUserId}) at socket ${receiverSocketId}`);
            socket.join(roomID);
            console.log('Caller joined room:', roomID);
        } else {
            console.log('User not found or offline:', toUserId);
            socket.emit('call-failed', { message: 'User is not online' });
        }
    });

    socket.on('offer', (data) => {
        io.to(activeUsers.get(data.toUserId)).emit('offer', data);
    });

    socket.on('answer', (data) => {
        io.to(activeUsers.get(data.fromUserId)).emit('answer', data);
    });

    socket.on('ice-candidate', (data) => {
        io.to(activeUsers.get(data.toUserId)).emit('ice-candidate', data);
    });
    
    socket.on('call-ended', (data) => {
        const { fromUserId, toUserId } = data;
        const callId = `${fromUserId}-${toUserId}`;
        const roomID = `room_${fromUserId}_${toUserId}`;
        activeCalls.delete(callId);
        console.log('Call ended:', { fromUserId, toUserId, callId, roomID });
        console.log('Updated active calls:', Array.from(activeCalls));
    });
    
    socket.on('call-accepted', (data) => {
        const { fromUserId, toUserId } = data;
        const fromUserSocketId = activeUsers.get(fromUserId);
        const roomID = `room_${fromUserId}_${toUserId}`;

        console.log('Call accepted:', { fromUserId, toUserId, roomID });

        if (fromUserSocketId) {
            const targetUserSocketId = socket.id;
            console.log(`Target user socket ID for receiver (${toUserId}):`, targetUserSocketId);
            socket.join(roomID);
            io.to(fromUserSocketId).emit('call-accepted', { fromUserId, toUserId, roomID });
            io.sockets.sockets.get(fromUserSocketId).join(roomID);
            console.log('Room joined:', roomID);
        }
    });
    
    socket.on('call-rejected', (data) => {
        const { fromUserId, toUserId } = data;
        const callId = `${fromUserId}-${toUserId}`;
        const fromUserSocketId = activeUsers.get(fromUserId);
        const roomID = `room_${fromUserId}_${toUserId}`;

        console.log('Call rejected:', { fromUserId, toUserId, callId, roomID });

        activeCalls.delete(callId);
        console.log('Updated active calls:', Array.from(activeCalls));

        if (fromUserSocketId) {
            socket.leave(roomID);
            io.sockets.sockets.get(fromUserSocketId).leave(roomID);
            io.to(fromUserSocketId).emit('call-rejected', { fromUserId, toUserId, roomID });
            console.log('Users left room:', roomID);
        }
    });

    // Xử lý sự kiện audio cho Speech-to-Text (Method 2)
    socket.on('speech-audio', async (data) => {
        // Giả sử data.audio chứa chuỗi base64 của âm thanh
        try {
            // Chuyển base64 thành chuỗi (giả sử âm thanh đã được encode theo LINEAR16 với sampleRate 16000)
            const audioBuffer = Buffer.from(data.audio, 'base64');

            // Cấu hình request cho Google Cloud Speech
            const request = {
                config: {
                    encoding: 'LINEAR16',
                    sampleRateHertz: 16000,
                    languageCode: 'vi-VN'
                },
                audio: {
                    content: audioBuffer.toString('base64'),
                },
            };

            // Gọi API Speech-to-Text
            const [response] = await speechClient.recognize(request);
            const transcription = response.results
                .map(result => result.alternatives[0].transcript)
                .join('\n');

            console.log(`Speech transcription from socket ${socket.id}: ${transcription}`);

            // Gửi transcript đến client nếu cần
            io.to(socket.id).emit('speech-transcript', { transcription, socketId: socket.id });
        } catch (err) {
            console.error('Error during speech-to-text processing:', err);
        }
    });

    socket.on('disconnect', () => {
        console.log('User disconnecting:', socket.id);
        for (let [userId, socketId] of activeUsers.entries()) {
            if (socketId === socket.id) {
                console.log('Found disconnecting user:', userId);
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
