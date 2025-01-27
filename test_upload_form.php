<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test File Upload</title>
</head>
<body>
    <h2>Test File Upload</h2>
    
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="file" accept="image/*,.pdf,.doc,.docx">
        <button type="submit">Upload</button>
    </form>

    <script>
    document.querySelector('form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('upload.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            console.log('Upload result:', result);
            alert(result.status === 'success' ? 'Upload successful!' : 'Upload failed: ' + result.message);
        } catch (error) {
            console.error('Error:', error);
            alert('Upload failed: ' + error.message);
        }
    });
    </script>
</body>
</html> 