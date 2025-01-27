<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "collab_connect";

$conn = new mysqli($servername, $username, $password, $database);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $table = $_POST['table'];
    
    // Validate table name
    $allowed_tables = ['discussions', 'questions', 'teams', 'jobs', 'users', 'job_applications'];
    if (!in_array($table, $allowed_tables)) {
        die(json_encode(['status' => 'error', 'message' => 'Invalid table name']));
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Special handling for different tables
        switch ($table) {
            case 'teams':
                // Delete team members first
                $delete_members = "DELETE FROM team_members WHERE team_id = ?";
                $stmt_members = $conn->prepare($delete_members);
                if (!$stmt_members) {
                    throw new Exception('Failed to prepare team members deletion: ' . $conn->error);
                }
                $stmt_members->bind_param('i', $id);
                $stmt_members->execute();
                $stmt_members->close();
                break;

            case 'questions':
                // Delete votes associated with the question first
                $delete_question_votes = "DELETE FROM votes WHERE question_id = ?";
                $stmt_votes = $conn->prepare($delete_question_votes);
                if (!$stmt_votes) {
                    throw new Exception('Failed to prepare votes deletion: ' . $conn->error);
                }
                $stmt_votes->bind_param('i', $id);
                $stmt_votes->execute();
                $stmt_votes->close();

                // Get all answer IDs for this question
                $get_answers = "SELECT id FROM answers WHERE question_id = ?";
                $stmt_answers = $conn->prepare($get_answers);
                if (!$stmt_answers) {
                    throw new Exception('Failed to prepare answers query: ' . $conn->error);
                }
                $stmt_answers->bind_param('i', $id);
                $stmt_answers->execute();
                $result = $stmt_answers->get_result();
                $answer_ids = [];
                while ($row = $result->fetch_assoc()) {
                    $answer_ids[] = $row['id'];
                }
                $stmt_answers->close();

                // Delete answer_votes for all answers of this question
                if (!empty($answer_ids)) {
                    $answer_ids_str = implode(',', $answer_ids);
                    $delete_answer_votes = "DELETE FROM answer_votes WHERE answer_id IN ($answer_ids_str)";
                    if (!$conn->query($delete_answer_votes)) {
                        throw new Exception('Failed to delete answer votes: ' . $conn->error);
                    }
                }

                // Now delete the answers
                $delete_answers = "DELETE FROM answers WHERE question_id = ?";
                $stmt_del_answers = $conn->prepare($delete_answers);
                if (!$stmt_del_answers) {
                    throw new Exception('Failed to prepare answers deletion: ' . $conn->error);
                }
                $stmt_del_answers->bind_param('i', $id);
                $stmt_del_answers->execute();
                $stmt_del_answers->close();
                break;
        }

        // Finally delete the main record
        $sql = "DELETE FROM $table WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare main deletion: ' . $conn->error);
        }
        
        $stmt->bind_param('i', $id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute deletion: ' . $stmt->error);
        }

        $stmt->close();
        
        // If we got here, commit the transaction
        $conn->commit();
        
        echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully']);
        
    } catch (Exception $e) {
        // Something went wrong, rollback the transaction
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

$conn->close();
?>