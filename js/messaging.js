document.addEventListener("DOMContentLoaded", function () {
    // Fetch current user ID securely
    const currentUserId = "<?php echo htmlspecialchars(json_encode($_SESSION['user_id'] ?? null), ENT_QUOTES, 'UTF-8'); ?>";
    console.log("Current User ID:", currentUserId);  // Debugging log

    if (!currentUserId || currentUserId === "null") {
        console.error("Current user ID is not defined. Ensure the user is logged in.");
        return;
    }

    // DOM Elements
    const chatMessages = document.querySelector(".chat-messages");
    const sendButton = document.querySelector(".btn-primary");
    const textArea = document.querySelector(".chat-input textarea");
    const chatHeader = document.querySelector(".chat-header h2");

    if (!chatMessages || !sendButton || !textArea || !chatHeader) {
        console.error("Required DOM elements are missing.");
        return;
    }

    // Fetch Receiver ID
    const receiverId = chatHeader?.getAttribute("data-id");
    console.log("Receiver ID:", receiverId);  // Debugging log
    if (!receiverId) {
        console.error("Receiver ID is not defined in chat header.");
        return;
    }

    // Fetch and display messages
    async function fetchMessages() {
        console.log("Fetching messages..."); // Debugging log
        try {
            const response = await fetch("fetch_messages.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `receiver_id=${encodeURIComponent(receiverId)}`,
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const messages = await response.json();
            if (Array.isArray(messages)) {
                renderMessages(messages);
            } else {
                console.error("Error fetching messages:", messages.error || "Unknown error");
            }
        } catch (error) {
            console.error("Fetch error:", error);
        }
    }

    // Render messages to the chat window
    function renderMessages(messages) {
        console.log("Rendering messages:", messages); // Debugging log
        chatMessages.innerHTML = ""; // Clear previous messages
        messages.forEach((message) => {
            const messageElement = document.createElement("p");
            messageElement.classList.add("message", message.sender_id == currentUserId ? "sent" : "received");
            messageElement.textContent = message.message;
            chatMessages.appendChild(messageElement);
        });
        chatMessages.scrollTop = chatMessages.scrollHeight; // Scroll to the bottom
    }

    // Send message
    sendButton.addEventListener("click", async function () {
        const message = textArea.value.trim();
        console.log("Sending message:", message); // Debugging log
        if (!message) {
            alert("Message cannot be empty.");
            return;
        }

        try {
            const response = await fetch("send_message.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `receiver_id=${encodeURIComponent(receiverId)}&message=${encodeURIComponent(message)}`,
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log("Response from send_message.php:", data); // Debugging log

            if (data.status === "success") {
                await fetchMessages(); // Refresh chat after sending
                textArea.value = ""; // Clear input
            } else {
                console.error("Error sending message:", data.message);
            }
        } catch (error) {
            console.error("Send error:", error);
        }
    });

    // Poll for new messages every 3 seconds
    let pollingInterval = setInterval(fetchMessages, 3000);

    // Stop polling if the page is unloaded
    window.addEventListener("beforeunload", function () {
        clearInterval(pollingInterval);
    });

    // Initial fetch
    fetchMessages();
});
