<?php
include 'db.php';
session_start();

if (!isset($_POST['receiver_id']) || !isset($_POST['message'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
    exit;
}

$sender_id = $_SESSION['user_id']; // Ensure this session variable is set
$receiver_id = $_POST['receiver_id'];
$message = trim($_POST['message']); // Trim to remove unnecessary whitespace

if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Message cannot be empty.']);
    exit;
}

// Insert the message into the database
$query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("iis", $sender_id, $receiver_id, $message);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Message sent.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Message could not be sent: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
