<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
session_start();

// Debug logging
error_log("Message sending attempt started");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("No user session found");
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

// Validate input
if (!isset($_POST['receiver_id']) || !isset($_POST['message'])) {
    error_log("Missing required fields");
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$sender_id = $_SESSION['user_id'];
$receiver_id = intval($_POST['receiver_id']);
$message = trim($_POST['message']);
$file_path = isset($_POST['file_path']) ? $_POST['file_path'] : null;

// Log the values
error_log("Sender ID: " . $sender_id);
error_log("Receiver ID: " . $receiver_id);
error_log("Message: " . $message);

// Validate message
if (empty($message)) {
    error_log("Empty message");
    echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty']);
    exit;
}

// Validate user IDs
if ($sender_id <= 0 || $receiver_id <= 0) {
    error_log("Invalid user IDs");
    echo json_encode(['status' => 'error', 'message' => 'Invalid user IDs']);
    exit;
}

// Check if a file was uploaded
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/'; // Make sure this directory exists and is writable
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $fileName = time() . '_' . basename($_FILES['file']['name']);
    $filePath = $uploadDir . $fileName;
    $fileType = $_FILES['file']['type'];
    $fileSize = $_FILES['file']['size'];

    // Move uploaded file
    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        $query = "INSERT INTO messages (sender_id, receiver_id, message, file_path, file_name, file_type, file_size) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iissssi", 
            $sender_id, 
            $receiver_id, 
            $message,
            $filePath,
            $fileName,
            $fileType,
            $fileSize
        );
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload file']);
        exit;
    }
} else {
    // Regular message without file
    $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
}

try {
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    error_log("Message sent successfully");
    echo json_encode([
        'status' => 'success',
        'message' => 'Message sent',
        'debug' => [
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'message_id' => $conn->insert_id
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error sending message: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>

<style>
#file-input {
    margin: 10px 0;
}
</style>

<button id="attach-file-button">
    <i class="fas fa-paperclip"></i> <!-- If using Font Awesome -->
</button>