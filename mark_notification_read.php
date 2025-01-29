<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$data = json_decode(file_get_contents('php://input'), true);
$notification_id = $data['notification_id'] ?? null;

if ($notification_id) {
    $query = "UPDATE notifications SET is_read = 1 
              WHERE id = ? AND receiver_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);
    $stmt->execute();
} 