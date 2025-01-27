// Add these variables at the top
let selectedFile = null;
const fileInput = document.getElementById('file-input');
const attachmentPreview = document.querySelector('.attachment-preview');

// Add file input handler
fileInput.addEventListener('change', handleFileSelect);

async function handleFileSelect(e) {
    const file = e.target.files[0];
    if (!file) return;

    // Validate file size (e.g., 5MB limit)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        alert('File is too large. Maximum size is 5MB.');
        fileInput.value = '';
        return;
    }

    selectedFile = file;
    attachmentPreview.innerHTML = '';

    try {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                attachmentPreview.innerHTML = `
                    <div class="preview-item">
                        <img src="${e.target.result}" alt="Preview">
                        <span class="remove-file" onclick="removeSelectedFile()">×</span>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            attachmentPreview.innerHTML = `
                <div class="file-message">
                    <i class="fas fa-file"></i>
                    <span>${file.name}</span>
                    <span class="remove-file" onclick="removeSelectedFile()">×</span>
                </div>
            `;
        }
    } catch (error) {
        console.error('Preview error:', error);
        alert('Error creating preview');
    }
}

function removeSelectedFile() {
    selectedFile = null;
    attachmentPreview.innerHTML = '';
    fileInput.value = '';
}

// Add this to your handleFileSelect function
function handleFileSelect(e) {
    console.log('File selected:', e.target.files[0]);
    const file = e.target.files[0];
    if (!file) {
        console.log('No file selected');
        return;
    }

    // Log file details
    console.log('File name:', file.name);
    console.log('File size:', file.size);
    console.log('File type:', file.type);

    // ... rest of your handleFileSelect code ...
}

// Update the file upload part of sendMessage function
async function sendMessage() {
    // ... existing code ...

    if (selectedFile) {
        console.log('Preparing to upload file:', selectedFile);
        const formData = new FormData();
        formData.append('file', selectedFile);

        try {
            console.log('Sending file to server...');
            const uploadResponse = await fetch('upload.php', {
                method: 'POST',
                body: formData
            });

            console.log('Raw upload response:', await uploadResponse.clone().text());
            
            const uploadResult = await uploadResponse.json();
            console.log('Upload result:', uploadResult);

            if (uploadResult.status === 'success') {
                console.log('File uploaded successfully:', uploadResult.file_path);
                filePath = uploadResult.file_path;
            } else {
                throw new Error(uploadResult.message || 'File upload failed');
            }
        } catch (error) {
            console.error('Upload error:', error);
            alert('Error uploading file: ' + error.message);
            return;
        }
    }


// Update sendMessage function
async function sendMessage() {
    if (!selectedReceiverId) {
        alert('Please select a user to chat with first');
        return;
    }

    const message = messageInput.value.trim();
    if (!message && !selectedFile) {
        alert('Please enter a message or select a file');
        return;
    }

    try {
        let filePath = null;

        // Upload file if selected
        if (selectedFile) {
            console.log('Uploading file:', selectedFile.name);
            const formData = new FormData();
            formData.append('file', selectedFile);

            const uploadResponse = await fetch('upload.php', {
                method: 'POST',
                body: formData
            });

            if (!uploadResponse.ok) {
                throw new Error('Network response was not ok');
            }

            const uploadResult = await uploadResponse.json();
            console.log('Upload result:', uploadResult);

            if (uploadResult.status === 'success') {
                filePath = uploadResult.file_path;
            } else {
                throw new Error(uploadResult.message || 'File upload failed');
            }
        }

        // Send message with file
        const messageData = new FormData();
        messageData.append('receiver_id', selectedReceiverId);
        messageData.append('message', message);
        if (filePath) {
            messageData.append('file_path', filePath);
        }

        const response = await fetch('send_message.php', {
            method: 'POST',
            body: messageData
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        console.log('Message send result:', data);

        if (data.status === 'success') {
            messageInput.value = '';
            removeSelectedFile();
            loadMessages(selectedReceiverId);
        } else {
            throw new Error(data.message || 'Failed to send message');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error sending message: ' + error.message);
    }
}
}
// Update the message display function
function createMessageElement(msg) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${msg.sender_id == currentUserId ? 'sent' : 'received'}`;
    
    if (msg.file_path) {
        if (isImageFile(msg.file_path)) {
            messageDiv.innerHTML = `
                <img src="${msg.file_path}" alt="Shared image" style="max-width: 200px; border-radius: 8px;">
                ${msg.message ? `<p>${msg.message}</p>` : ''}
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="file-message">
                    <i class="fas fa-file"></i>
                    <span>${msg.name}</span>
                    <span class="remove-file" onclick="removeSelectedFile()">×</span>
                </div>
            `;
        }
    } else {
        messageDiv.textContent = msg.message;
    }
    
    return messageDiv;
}

function isImageFile(filePath) {
    const extension = filePath.split('.').pop().toLowerCase();
    return ['jpg', 'jpeg', 'png', 'gif'].includes(extension);
}

document.addEventListener('DOMContentLoaded', function() {
    let selectedReceiverId = null;
    const chatMessages = document.querySelector('.chat-messages');
    const messageInput = document.querySelector('.chat-input textarea');
    const sendButton = document.querySelector('.chat-input button');
    const chatHeader = document.querySelector('.chat-header h2');

    // Debug check for elements
    console.log('Chat elements:', {
        chatMessages: !!chatMessages,
        messageInput: !!messageInput,
        sendButton: !!sendButton,
        chatHeader: !!chatHeader
    });

    // Handle user selection
    document.querySelectorAll('.contact').forEach(contact => {
        contact.addEventListener('click', function() {
            console.log('Contact clicked:', this.dataset.receiverId);
            selectedReceiverId = this.dataset.receiverId;
            const userName = this.textContent;
            chatHeader.textContent = `Chat with ${userName}`;
            
            document.querySelectorAll('.contact').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            
            loadMessages(selectedReceiverId);
        });
    });

    // Send button click handler
    sendButton.addEventListener('click', sendMessage);

    // Enter key handler
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    async function loadMessages(receiverId) {
        try {
            console.log('Loading messages for receiver:', receiverId);
            
            const response = await fetch(`get_messages.php?receiver_id=${receiverId}`);
            const messages = await response.json();
            
            console.log('Loaded messages:', messages);

            chatMessages.innerHTML = '';
            messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${msg.sender_id == currentUserId ? 'sent' : 'received'}`;
                
                let content = '';
                
                // Handle file attachments
                if (msg.file_path) {
                    const fileExt = msg.file_path.split('.').pop().toLowerCase();
                    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                        content += `<img src="${msg.file_path}" alt="Shared image" style="max-width: 200px; border-radius: 8px;"><br>`;
                    } else {
                        content += `
                            <div class="file-message">
                                <i class="fas fa-file"></i>
                                <a href="${msg.file_path}" target="_blank" download>Download File</a>
                            </div>
                        `;
                    }
                }
                
                // Add message text if exists
                if (msg.message) {
                    content += `<p>${msg.message}</p>`;
                }
                
                messageDiv.innerHTML = content;
                chatMessages.appendChild(messageDiv);
            });
            
            chatMessages.scrollTop = chatMessages.scrollHeight;
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }
});