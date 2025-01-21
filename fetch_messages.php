<?php
include 'db.php';
session_start();

$receiver_id = $_POST['receiver_id'];
$sender_id = $_SESSION['user_id'];

$query = "SELECT * FROM messages WHERE 
          (sender_id = ? AND receiver_id = ?) OR 
          (sender_id = ? AND receiver_id = ?) 
          ORDER BY created_at ASC";

$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['error' => "Prepare Failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    echo json_encode($messages); // Output JSON
} else {
    echo json_encode(['error' => "Execution Failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
