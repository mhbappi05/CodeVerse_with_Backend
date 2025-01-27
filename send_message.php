<?php
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

try {
    // Insert message
    $query = "INSERT INTO messages (sender_id, receiver_id, message, file_path) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $message, $file_path);
    
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