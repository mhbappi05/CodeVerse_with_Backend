<?php
include 'db.php';
session_start();

if (!isset($_GET['receiver_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Receiver ID not provided']);
    exit;
}

$user_id = $_SESSION['user_id'];
$receiver_id = $_GET['receiver_id'];

$query = "SELECT * FROM messages 
          WHERE (sender_id = ? AND receiver_id = ?)
          OR (sender_id = ? AND receiver_id = ?)
          ORDER BY created_at ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);

$stmt->close();
$conn->close();
?> 