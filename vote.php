<?php
include 'db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['id'], $input['vote'])) {
    $questionId = intval($input['id']);
    $vote = intval($input['vote']);
    $userId = 1; // Replace with session user ID

    // Check if the user has already voted
    $checkVote = $conn->prepare("SELECT vote FROM votes WHERE question_id = ? AND user_id = ?");
    $checkVote->bind_param("ii", $questionId, $userId);
    $checkVote->execute();
    $result = $checkVote->get_result();

    if ($result->num_rows > 0) {
        // Update existing vote
        $updateVote = $conn->prepare("UPDATE votes SET vote = ? WHERE question_id = ? AND user_id = ?");
        $updateVote->bind_param("iii", $vote, $questionId, $userId);
        $updateVote->execute();
    } else {
        // Insert new vote
        $insertVote = $conn->prepare("INSERT INTO votes (question_id, user_id, vote) VALUES (?, ?, ?)");
        $insertVote->bind_param("iii", $questionId, $userId, $vote);
        $insertVote->execute();
    }

    // Get the new vote count
    $voteCountQuery = $conn->prepare("SELECT COALESCE(SUM(vote), 0) as vote_count FROM votes WHERE question_id = ?");
    $voteCountQuery->bind_param("i", $questionId);
    $voteCountQuery->execute();
    $voteCountResult = $voteCountQuery->get_result();
    $newVoteCount = $voteCountResult->fetch_assoc()['vote_count'];

    echo json_encode(['success' => true, 'newVoteCount' => $newVoteCount]);
} else {
    echo json_encode(['success' => false]);
}
?>
