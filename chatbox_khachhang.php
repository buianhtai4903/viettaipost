<?php
session_start();
include('config/connectdb.php');
$db = new ConnectDB();
$conn = $db->connectDB1();

// Debug session chi tiết

// Kiểm tra đăng nhập


$userId = $_SESSION['id'];
$userName = $_SESSION['ho_ten'];
$userType = 'khachhang';

// Lấy thông tin shipper đang phụ trách đơn hàng
$sql = "SELECT s.*, d.ma_don_hang, vd.trang_thai as trang_thai_van_don
        FROM shipper s 
        JOIN van_don vd ON s.id = vd.id_shipper
        JOIN don_hang d ON vd.ma_don_hang = d.ma_don_hang
        WHERE vd.ma_don_hang = ? 
        AND vd.trang_thai = 'đợi lấy hàng'
        ORDER BY vd.thoi_gian_cap_nhat DESC
        LIMIT 1";

// Debug thông tin truy vấn
error_log("SQL Query for customer: " . $sql);
error_log("Customer ID in query: " . $userId);

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_GET['ma_don_hang']);
$stmt->execute();
$result = $stmt->get_result();
$chatPartner = $result->fetch_assoc();

// Debug kết quả truy vấn
error_log("Query result: " . print_r($chatPartner, true));
error_log("Shipper status: " . $chatPartner['trang_thai']);

// Tạo ID phòng chat
$chatRoomId = "chat_shipper_" . $chatPartner['id'] . "_customer_" . $userId;
$userRoomId = "user_" . $userId . "_khachhang";

// Debug thông tin
error_log("Customer ID: " . $userId);
error_log("Shipper ID: " . $chatPartner['id']);
error_log("Chat Room ID: " . $chatRoomId);

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - VietTaiPost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="asset/css/chatbox.css">
</head>

<body>
    <div class="chat-container">
        <div class="chat-header">
            <div class="receiver-info">
                <div>
                    <h5 class="mb-0"><?php echo htmlspecialchars($chatPartner['ho_ten']); ?></h5>
                    <span class="user-type">Shipper - Mã đơn:
                        <?php echo htmlspecialchars($chatPartner['ma_don_hang']); ?></span>
                </div>
            </div>
            <div
                class="status-badge <?php echo trim($chatPartner['trang_thai']) === 'Đang hoạt động' ? 'online' : 'offline'; ?>">
                <i class="fas fa-circle"></i>
                <?php echo trim($chatPartner['trang_thai']) === 'Đang hoạt động' ? 'Online' : 'Offline'; ?>
            </div>
        </div>

        <div class="chat-messages" id="chat-messages">
            <!-- Messages will be loaded here -->
        </div>

        <div class="chat-input-container">
            <input type="text" class="chat-input" id="message-input" placeholder="Nhập tin nhắn...">
            <button class="send-button" id="send-button">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
    <script>
        const socket = io('http://localhost:3000');

        // Xử lý thu gọn/mở rộng chat box
        const chatContainer = document.querySelector('.chat-container');
        const chatHeader = document.querySelector('.chat-header');

        chatHeader.addEventListener('click', () => {
            chatContainer.classList.toggle('minimized');
        });

        // Truyền thông tin người dùng vào biến global
        window.userInfo = {
            id: <?php echo json_encode($userId); ?>,
            type: <?php echo json_encode($userType); ?>,
            name: <?php echo json_encode($userName); ?>,
            chatRoom: <?php echo json_encode($chatRoomId); ?>,
            userRoom: <?php echo json_encode($userRoomId); ?>
        };

        // Tham gia phòng chat khi trang được tải
        socket.emit('join_chat', {
            userId: window.userInfo.id,
            userType: window.userInfo.type,
            receiverId: <?php echo json_encode($chatPartner['id']); ?>,
            receiverType: 'shipper',
            chatRoom: window.userInfo.chatRoom,
            userRoom: window.userInfo.userRoom
        });

        // Xử lý lịch sử chat
        socket.on('chat_history', (messages) => {
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.innerHTML = '';
            messages.forEach(message => {
                appendMessage(message);
            });
            scrollToBottom();
        });

        // Xử lý tin nhắn mới
        socket.on('new_message', (message) => {
            appendMessage(message);
            scrollToBottom();
        });

        // Xử lý trạng thái đơn hàng
        socket.on('order_status', (data) => {
            const statusBadge = document.querySelector('.status-badge');
            statusBadge.innerHTML = `<i class="fas fa-box"></i> ${data.status}`;
        });

        // Hàm thêm tin nhắn vào giao diện
        function appendMessage(message) {
            const chatMessages = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${message.sender_id == window.userInfo.id ? 'sent' : 'received'}`;

            const time = new Date(message.created_at).toLocaleTimeString('vi-VN', {
                hour: '2-digit',
                minute: '2-digit'
            });

            messageDiv.innerHTML = `
            <div class="message-content">${message.message}</div>
            <div class="message-time">${time}</div>
        `;

            chatMessages.appendChild(messageDiv);
            scrollToBottom();
        }

        // Hàm cuộn xuống tin nhắn mới nhất
        function scrollToBottom() {
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Xử lý gửi tin nhắn
        document.getElementById('send-button').addEventListener('click', sendMessage);
        document.getElementById('message-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        function sendMessage() {
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value.trim();

            if (message) {
                socket.emit('send_message', {
                    message: message,
                    receiverId: <?php echo json_encode($chatPartner['id']); ?>,
                    receiverType: 'shipper',
                    chatRoom: window.userInfo.chatRoom
                });

                messageInput.value = '';
            }
        }
    </script>
</body>

</html>