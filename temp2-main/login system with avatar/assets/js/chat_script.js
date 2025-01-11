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
const floatButton = document.getElementById('floatButton');

// Scroll to the message input section when the button is clicked
floatButton.addEventListener('click', function() {
const chatArea = document.querySelector('.chat-area7');
const messageInput = document.getElementById('messageInput');

// Scroll the chat area to the bottom (where the message input is)
chatArea.scrollTo({
top: chatArea.scrollHeight, // Scroll to the bottom of the chat area
behavior: 'smooth' // Enable smooth scrolling
});

// Focus on the input field after scrolling
setTimeout(() => {
messageInput.focus();
}, 500); // Delay focus to allow the scroll to finish
});
