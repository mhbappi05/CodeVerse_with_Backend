<?php
include 'db.php';
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function
function logDebug($message) {
    error_log("[Upload Debug] " . $message);
}

logDebug("Upload process started");

// Check session
if (!isset($_SESSION['user_id'])) {
    logDebug("No user session found");
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['file'])) {
    logDebug("No file in request");
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
logDebug("File details: " . print_r($file, true));

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    logDebug("Upload error code: " . $file['error']);
    echo json_encode(['status' => 'error', 'message' => 'Upload failed with error code: ' . $file['error']]);
    exit;
}

$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];

// Validate file type
if (!in_array($file_extension, $allowed_extensions)) {
    logDebug("Invalid file type: " . $file_extension);
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
    exit;
}

// Create unique filename
$file_name = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);
$upload_dir = 'uploads/';
$file_path = $upload_dir . $file_name;

logDebug("Attempting to move file to: " . $file_path);

// Check directory permissions
logDebug("Upload directory exists: " . (file_exists($upload_dir) ? 'Yes' : 'No'));
logDebug("Upload directory writable: " . (is_writable($upload_dir) ? 'Yes' : 'No'));
logDebug("Upload directory permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4));

// Try to move the file
if (move_uploaded_file($file['tmp_name'], $file_path)) {
    logDebug("File uploaded successfully");
    echo json_encode([
        'status' => 'success',
        'file_name' => $file_name,
        'file_path' => $file_path,
        'file_type' => $file_extension
    ]);
} else {
    $error = error_get_last();
    logDebug("Failed to move file. Error: " . print_r($error, true));
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save file',
        'debug' => $error
    ]);
}
?>