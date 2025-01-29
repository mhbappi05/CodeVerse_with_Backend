function sendMessage(messageData) {
    return fetch('send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(messageData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .catch(error => {
        console.error('Error sending message:', error);
        throw error; // Re-throw to handle it in the calling function
    });
}

function handleFileUpload(file, receiverId) {
    const formData = new FormData();
    formData.append('file', file);

    return fetch('upload_file.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Send message with file attachment
            return sendMessage({
                message: `Shared file: ${data.file.name}`,
                receiver_id: receiverId,
                file: data.file
            });
        } else {
            throw new Error(data.error);
        }
    });
}

// Add file input to your chat interface
const fileInput = document.createElement('input');
fileInput.type = 'file';
fileInput.style.display = 'none';
document.body.appendChild(fileInput);

// Add click handler for file attachment button
document.getElementById('attach-file-button').addEventListener('click', () => {
    fileInput.click();
});

fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
        handleFileUpload(file, currentReceiverId) // Replace currentReceiverId with your receiver ID variable
            .then(() => {
                fileInput.value = ''; // Clear the input
            })
            .catch(error => {
                console.error('Error uploading file:', error);
                alert('Failed to upload file: ' + error.message);
            });
    }
}); 