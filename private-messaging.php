<?php
include 'templates/header.php';
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!-- Add this at the top to make currentUserId available to JavaScript -->
<script>
    const currentUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
</script>

<!--Header-->
<header class="dashboard-header">
    <div class="container">
        <h1>Messages</h1>
    </div>
</header>

<!-- Private Messaging Content -->
<main class="messaging-page">
    <div class="container">
        <div class="messaging-container">
            <!-- Users List -->
            <div class="chat-sidebar">
                <h3>Users</h3>
                <ul class="contact-list">
                    <?php
                    // Get all users except current user and admins
                    $query = "SELECT id, name FROM users 
                             WHERE role != 'admin' 
                             AND id != ? 
                             ORDER BY name ASC";

                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        echo "<li class='contact' data-receiver-id='{$row['id']}'>";
                        echo "<span class='contact-name'>{$row['name']}</span>";
                        echo "</li>";
                    }
                    ?>
                </ul>
            </div>

            <!-- Chat Area -->
            <div class="chat-area">
                <div class="chat-header">
                    <h2>Select a user to start chatting</h2>
                </div>
                <div class="chat-messages">
                    <!-- Messages will be loaded here -->
                </div>
                <div class="chat-input">
                    <div class="attachment-preview"></div>
                    <div class="input-container">
                        <label for="file-input" class="attachment-btn">
                            <i class="fas fa-paperclip"></i>
                        </label>
                        <input type="file" id="file-input" name="file" accept="image/*,.pdf,.doc,.docx"
                            style="display: none;">
                        <textarea placeholder="Type your message..." disabled></textarea>
                        <button class="send-button" disabled>
                            <i class="fas fa-paper-plane"></i>send
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Add your CSS -->
<style>
    /* Modern Chat Container */
    .messaging-container {
        display: flex;
        gap: 24px;
        margin: 20px auto;
        max-width: 1200px;
        height: 80vh;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    /* Sidebar Styling */
    .chat-sidebar {
        width: 300px;
        background: #f8f9fa;
        border-right: 1px solid #eaeaea;
        padding: 20px 0;
    }

    .chat-sidebar h3 {
        padding: 0 20px 20px;
        color: #1a1a1a;
        font-size: 1.2rem;
        border-bottom: 1px solid #eaeaea;
        margin: 0;
    }

    .contact-list {
        list-style: none;
        padding: 0;
        margin: 0;
        overflow-y: auto;
        height: calc(100% - 60px);
    }

    .contact {
        padding: 15px 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        border-bottom: 1px solid #eaeaea;
    }

    .contact:hover {
        background-color: #e9ecef;
        transform: translateX(5px);
    }

    .contact.active {
        background-color: #007bff;
        color: white;
        position: relative;
    }

    .contact.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: #0056b3;
    }

    /* Chat Area Styling */
    .chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    .chat-header {
        padding: 20px;
        background: #fff;
        border-bottom: 1px solid #eaeaea;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    }

    .chat-header h2 {
        margin: 0;
        color: #1a1a1a;
        font-size: 1.2rem;
    }

    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
    }

    /* Message Bubbles */
    .message {
        margin: 8px 0;
        padding: 12px 16px;
        border-radius: 16px;
        max-width: 75%;
        position: relative;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .sent {
        background: #1a1a1a;
        color: white;
        margin-left: auto;
        border-bottom-right-radius: 4px;
    }

    .received {
        background: white;
        color: #1a1a1a;
        margin-right: auto;
        border-bottom-left-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Input Area */
    .chat-input {
        padding: 20px;
        background: #fff;
        border-top: 1px solid #eaeaea;
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .chat-input textarea {
        flex: 1;
        padding: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 24px;
        resize: none;
        font-size: 0.95rem;
        line-height: 1.4;
        max-height: 100px;
        transition: all 0.3s ease;
    }

    .chat-input textarea:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .send-button {
        padding: 12px 24px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 24px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .send-button:hover:not(:disabled) {
        background: #0056b3;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
    }

    .send-button:disabled {
        background: #e0e0e0;
        cursor: not-allowed;
        transform: none;
    }

    /* Scrollbar Styling */
    .chat-messages::-webkit-scrollbar {
        width: 6px;
    }

    .chat-messages::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .chat-messages::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .chat-messages::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Status Indicators */
    .contact {
        display: flex;
        align-items: center;
        position: relative;
    }

    .contact-name {
        margin-left: 10px;
    }

    .contact::after {
        content: '';
        width: 8px;
        height: 8px;
        background: #28a745;
        border-radius: 50%;
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .messaging-container {
            flex-direction: column;
            height: 90vh;
        }

        .chat-sidebar {
            width: 100%;
            height: 30%;
        }

        .chat-area {
            height: 70%;
        }
    }

    .input-container {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        background: #fff;
        padding: 10px;
        border-radius: 8px;
    }

    .attachment-btn {
        padding: 10px;
        cursor: pointer;
        color: #007bff;
        transition: color 0.3s ease;
    }

    .attachment-btn:hover {
        color: #0056b3;
    }

    .attachment-preview {
        padding: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .preview-item {
        position: relative;
        display: inline-block;
        margin: 5px;
    }

    .preview-item img {
        max-width: 100px;
        max-height: 100px;
        border-radius: 8px;
        border: 2px solid #eaeaea;
    }

    .preview-item .remove-file {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(0, 0, 0, 0.5);
        color: white;
        border-radius: 50%;
        padding: 5px;
        cursor: pointer;
        font-size: 12px;
    }

    .file-message {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        margin: 5px 0;
    }

    .file-message i {
        font-size: 24px;
    }

    .file-message a {
        color: #007bff;
        text-decoration: none;
    }

    .file-preview {
        display: flex;
        align-items: center;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        margin: 5px 0;
    }

    .file-preview i {
        margin-right: 10px;
        font-size: 20px;
        color: #007bff;
    }

    .message .file-content {
        margin-bottom: 5px;
    }

    .message .file-content img {
        max-width: 200px;
        border-radius: 8px;
        margin-bottom: 5px;
    }

    .message .file-content .file-attachment {
        display: flex;
        align-items: center;
        padding: 10px;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        margin-bottom: 5px;
    }

    .message .file-content .file-attachment i {
        margin-right: 10px;
    }

    .message .file-content .file-attachment a {
        color: #007bff;
        text-decoration: none;
    }

    .error-message {
        color: red;
        text-align: center;
        padding: 10px;
        margin: 10px;
    }

    .message-time {
        font-size: 0.8em;
        opacity: 0.7;
        margin-top: 4px;
    }
</style>

<!-- Add your JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatMessages = document.querySelector('.chat-messages');
        const messageInput = document.querySelector('.chat-input textarea');
        const sendButton = document.querySelector('.send-button');
        const chatHeader = document.querySelector('.chat-header h2');
        let selectedReceiverId = null;

        // Handle user selection
        document.querySelectorAll('.contact').forEach(contact => {
            contact.addEventListener('click', function () {
                // Update selected user
                selectedReceiverId = this.dataset.receiverId;
                const userName = this.querySelector('.contact-name').textContent;

                // Update UI
                document.querySelectorAll('.contact').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                chatHeader.textContent = `Chat with ${userName}`;

                // Enable input and button
                messageInput.disabled = false;
                sendButton.disabled = false;

                // Load messages
                loadMessages(selectedReceiverId);
            });
        });

        // Send message handler
        sendButton.addEventListener('click', sendMessage);

        // Enter key handler
        messageInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        function sendMessage() {
            if (!selectedReceiverId) return;

            const message = messageInput.value.trim();
            if (!message) return;

            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `receiver_id=${selectedReceiverId}&message=${encodeURIComponent(message)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        messageInput.value = '';
                        loadMessages(selectedReceiverId);
                    } else {
                        console.error('Server error:', data.message);
                        // Only show alert if there's actually an error message
                        if (data.message) {
                            alert('Error sending message: ' + data.message);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Only show alert for actual errors
                    if (!messageInput.value.trim()) {
                        alert('Error sending message');
                    }
                });
        }

        function loadMessages(receiverId) {
            fetch(`get_messages.php?receiver_id=${receiverId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(messages => {
                    if (!Array.isArray(messages)) {
                        throw new Error('Invalid response format: expected an array');
                    }
                    chatMessages.innerHTML = '';
                    messages.forEach(msg => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `message ${msg.sender_id == currentUserId ? 'sent' : 'received'}`;
                        // Add message timestamp and sanitize message content
                        const messageContent = document.createElement('div');
                        messageContent.textContent = msg.message; // This safely escapes HTML
                        messageDiv.appendChild(messageContent);
                        
                        // Add timestamp if available
                        if (msg.timestamp) {
                            const timeDiv = document.createElement('div');
                            timeDiv.className = 'message-time';
                            timeDiv.textContent = new Date(msg.timestamp).toLocaleTimeString();
                            messageDiv.appendChild(timeDiv);
                        }
                        
                        chatMessages.appendChild(messageDiv);
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                    chatMessages.innerHTML = `<div class="error-message">Error loading messages: ${error.message}</div>`;
                });
        }

        // Auto-refresh messages every 5 seconds
        setInterval(() => {
            if (selectedReceiverId) {
                loadMessages(selectedReceiverId);
            }
        }, 5000);
    });
</script>

<?php include 'templates/footer.php'; ?>