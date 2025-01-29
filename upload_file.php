<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Check if file was uploaded
    if (!isset($_FILES['file'])) {
        throw new Exception('No file uploaded');
    }

    $file = $_FILES['file'];
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed with error code: ' . $file['error']);
    }

    // Set maximum file size (5MB)
    $maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $maxFileSize) {
        throw new Exception('File size exceeds maximum limit of 5MB');
    }

    // Create uploads directory if it doesn't exist
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueFilename = uniqid() . '_' . time() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $uniqueFilename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Failed to move uploaded file');
    }

    // Return file information
    echo json_encode([
        'success' => true,
        'file' => [
            'path' => $uploadPath,
            'name' => $file['name'],
            'type' => $file['type'],
            'size' => $file['size']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 