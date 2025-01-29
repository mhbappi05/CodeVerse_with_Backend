<?php
// Basic error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start with a clean output
ob_start();

header('Content-Type: application/json');

try {
    // 1. Test database connection
    require_once 'db.php';
    if (!isset($conn)) {
        throw new Exception("Database connection failed");
    }

    // 2. Test session
    if (!isset($_SESSION)) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User not logged in");
    }

    // 3. Get and validate IDs
    $current_user_id = (int)$_SESSION['user_id'];
    $receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;

    if ($receiver_id <= 0) {
        throw new Exception("Invalid receiver ID");
    }

    // 4. Simple test query
    $sql = "SELECT COUNT(*) as count FROM messages";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Cannot access messages table: " . $conn->error);
    }

    // 5. If we get here, try the actual message query
    $query = "SELECT m.id, m.message, m.sender_id, m.receiver_id, m.created_at, 
                     m.file_path, m.file_name, m.file_type, m.file_size 
              FROM messages m
              WHERE (sender_id = ? AND receiver_id = ?) 
                 OR (sender_id = ? AND receiver_id = ?) 
              ORDER BY created_at ASC";

    if (!($stmt = $conn->prepare($query))) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!$stmt->bind_param("iiii", $current_user_id, $receiver_id, $receiver_id, $current_user_id)) {
        throw new Exception("Binding parameters failed: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $messages = [];
    
    while ($row = $result->fetch_assoc()) {
        $fileInfo = null;
        if ($row['file_path']) {
            $fileInfo = [
                'path' => $row['file_path'],
                'name' => $row['file_name'],
                'type' => $row['file_type'],
                'size' => (int)$row['file_size']
            ];
        }
        
        $messages[] = [
            'id' => (int)$row['id'],
            'message' => $row['message'],
            'sender_id' => (int)$row['sender_id'],
            'receiver_id' => (int)$row['receiver_id'],
            'timestamp' => $row['created_at'],
            'file' => $fileInfo
        ];
    }

    // Just return the messages array directly
    echo json_encode($messages);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}

ob_end_flush();
?>