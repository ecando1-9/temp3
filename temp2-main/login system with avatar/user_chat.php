
<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Add new query to fetch security key with error handling
$verify_query = "SELECT security_key FROM user_form WHERE id = ?";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("i", $chat_user_id);
$stmt->execute();
$key_result = $stmt->get_result();
$security_data = $key_result->fetch_assoc();
$stored_key = isset($security_data['security_key']) ? $security_data['security_key'] : '';
$stmt->close();

// Mark messages as read
$mark_read_query = "UPDATE chat_messages SET is_read = 1 WHERE user1_id = ? AND user2_id = ? AND is_read = 0";
$stmt = $conn->prepare($mark_read_query);
$stmt->bind_param("ii", $chat_user_id, $user_id);
$stmt->execute();
$stmt->close();

// Fetch chat history
$query = "
    SELECT cm.message, cm.sender_id, cm.timestamp 
    FROM chat_messages cm 
    WHERE (cm.user1_id = ? AND cm.user2_id = ?) 
    OR (cm.user1_id = ? AND cm.user2_id = ?)
    ORDER BY cm.timestamp ASC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $user_id, $chat_user_id, $chat_user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch chat user details
$query_user = "SELECT name, image FROM user_form WHERE id = ?";
$stmt = $conn->prepare($query_user);
$stmt->bind_param("i", $chat_user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$chat_user = $result_user->fetch_assoc();
$stmt->close();

if (!$chat_user) {
    echo '<p>User not found. <a href="chat.php">Go back to chats</a></p>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($chat_user['name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hidden { display: none; }
        #moveButton {
            position: fixed;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: move;
            z-index: 1000;
        }
        .verify-dialog {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            z-index: 1001;
        }
    </style>
</head>
<body>
    <div class="chat-container7">
        <div class="profile-section">
            <div class="profile-icon">
                <?php if (empty($chat_user['image'])): ?>
                    <img src="images/default-avatar.png" class="profile-pic">
                <?php else: ?>
                    <img src="uploaded_img/<?php echo htmlspecialchars($chat_user['image']); ?>" class="profile-pic">
                <?php endif; ?>
            </div>
            <h4>Chat with <?php echo htmlspecialchars($chat_user['name']); ?></h4>
        </div>

        <button id="verifyButton" class="btn">Verify to View Messages</button>

        <div id="verifyDialog" class="verify-dialog hidden">
            <h3>Verify Access</h3>
            <select id="keyOption">
                <option value="">Select Verification Method</option>
                <option value="security_key">Security Key</option>
            </select>
            <input type="password" id="securityKey" placeholder="Enter Security Key" class="hidden">
            <button id="submitVerification">Verify</button>
        </div>

        <div id="moveButton" class="hidden">Show Chat</div>

        <div id="chatContent" class="hidden">
            <div class="back-section">
                <a href="chat.php">Go Back</a>
            </div>

            <div class="chat-area7">
                <div id="messages">
                    <?php foreach ($messages as $message): ?>
                        <div class="message <?php echo $message['sender_id'] === $user_id ? 'sent' : 'received'; ?>">
                            <span><?php echo htmlspecialchars($message['message']); ?></span>
                            <small class="timestamp"><?php echo htmlspecialchars(date('H:i, Y-m-d', strtotime($message['timestamp']))); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>

                <input type="text" id="messageInput" placeholder="Type a message">
                <button id="sendMessage">Send</button>
            </div>
            <button id="floatButton">To Message</button>
        </div>
    </div>

    <script>
        const storedKey = <?php echo json_encode($stored_key); ?>;
        let clickCount = 0;
        let isDragging = false;
        let currentX;
        let currentY;
        let initialX;
        let initialY;
        let xOffset = 0;
        let yOffset = 0;

        // Verification handling
        document.getElementById('verifyButton').addEventListener('click', () => {
            document.getElementById('verifyDialog').classList.remove('hidden');
        });

        document.getElementById('keyOption').addEventListener('change', function() {
            document.getElementById('securityKey').classList.toggle('hidden', this.value !== 'security_key');
        });

        document.getElementById('submitVerification').addEventListener('click', () => {
            const enteredKey = document.getElementById('securityKey').value;
            
            if (enteredKey === storedKey) {
                document.getElementById('verifyDialog').classList.add('hidden');
                document.getElementById('verifyButton').classList.add('hidden');
                document.getElementById('moveButton').classList.remove('hidden');
                initializeDraggableButton();
            } else {
                alert('Invalid security key!');
            }
        });

        function initializeDraggableButton() {
            const moveButton = document.getElementById('moveButton');
            const chatContent = document.getElementById('chatContent');

            moveButton.addEventListener('mousedown', dragStart);
            document.addEventListener('mousemove', drag);
            document.addEventListener('mouseup', dragEnd);

            moveButton.addEventListener('click', () => {
                clickCount++;
                
                if (clickCount === 1) {
                    chatContent.classList.remove('hidden');
                    chatContent.style.opacity = '0.5';
                } else if (clickCount === 2) {
                    chatContent.style.opacity = '1';
                } else if (clickCount === 3) {
                    chatContent.classList.add('hidden');
                    moveButton.classList.add('hidden');
                    document.getElementById('verifyButton').classList.remove('hidden');
                    clickCount = 0;
                }
            });
        }

        function dragStart(e) {
            initialX = e.clientX - xOffset;
            initialY = e.clientY - yOffset;
            if (e.target === document.getElementById('moveButton')) {
                isDragging = true;
            }
        }

        function drag(e) {
            if (isDragging) {
                e.preventDefault();
                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;
                xOffset = currentX;
                yOffset = currentY;
                setTranslate(currentX, currentY, document.getElementById('moveButton'));
            }
        }

        function setTranslate(xPos, yPos, el) {
            el.style.transform = `translate(${xPos}px, ${yPos}px)`;
        }

        function dragEnd() {
            initialX = currentX;
            initialY = currentY;
            isDragging = false;
        }

        const currentUserId = <?php echo json_encode($user_id); ?>;
        const chatUserId = <?php echo json_encode($chat_user_id); ?>;
        const messageInput = document.getElementById('messageInput');
        const sendMessageButton = document.getElementById('sendMessage');
        const messagesContainer = document.getElementById('messages');

        // Function to send a message
        function sendMessage() {
            const message = messageInput.value.trim();

            if (message.length > 0) {
                fetch('send_message.php', {
                    method: 'POST',
                    body: JSON.stringify({ message, user_id: chatUserId }),
                    headers: { 'Content-Type': 'application/json' }
                }).then(() => {
                    messagesContainer.innerHTML += `<div class="message sent">
                                                        <span>${message}</span>
                                                        <small class="timestamp">${new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })}, ${new Date().toLocaleDateString('en-GB')}</small>
                                                    </div>`;
                    messageInput.value = ''; // Clear the input field
                    messagesContainer.scrollTop = messagesContainer.scrollHeight; // Scroll to the bottom
                });
            }
        }

        // Event listener for 'Enter' key press
        messageInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Prevent the default behavior of Enter key
                sendMessage(); // Trigger the send message function
            }
        });

        // Event listener for 'Send' button click
        sendMessageButton.addEventListener('click', function() {
            sendMessage(); // Trigger the send message function when button is clicked
        });

        // Load chat messages initially
        function loadChat() {
            fetch(`get_chat.php?user_id=${chatUserId}`)
                .then(response => response.json())
                .then(messages => {
                    let chatHtml = '';
                    messages.forEach(message => {
                        const messageClass = message.sender_id === currentUserId ? 'sent' : 'received';
                        chatHtml += `<div class="message ${messageClass}">
                                        <span>${message.message}</span>
                                        <small class="timestamp">${new Date(message.timestamp).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })}, ${new Date(message.timestamp).toLocaleDateString('en-GB')}</small>
                                     </div>`;
                    });
                    messagesContainer.innerHTML = chatHtml;
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                });
        }

        // Prevent message from being sent when clicking anywhere else on the screen
        document.addEventListener('click', function(event) {
            if (!messageInput.contains(event.target) && !sendMessageButton.contains(event.target)) {
                messageInput.blur(); // Remove focus from the input when clicking outside
            }
        });
        setInterval(loadChat, 2000);

        // Initial chat load
        loadChat();
        document.addEventListener('DOMContentLoaded', function() {
    const floatButton = document.getElementById('floatButton');
    const chatArea = document.querySelector('.chat-area7');
    const messageInput = document.getElementById('messageInput');

    if (!chatArea || !messageInput) {
        console.error('Chat area or message input not found!');
        return;
    }

    floatButton.addEventListener('click', function() {
        chatArea.scrollTo({
            top: chatArea.scrollHeight,
            behavior: 'smooth'
        });

        setTimeout(() => {
            messageInput.focus();
        }, 100);
    });
});
    </script>
</body>
</html>
