<?php
include 'db.php';
include 'templates/header.php';

// Check if 'id' is passed in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $question_id = intval($_GET['id']); // Get the question ID from the URL and ensure it's an integer
} else {
    echo "<h2>Question ID not provided. <a href='index.php'>Go back</a> and try again.</h2>";
    exit;
}

// Handle the submission of a new answer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer']) && !empty($_POST['answer'])) {
    // Sanitize and assign the answer text
    $answer = htmlspecialchars($_POST['answer']);

    // Get the logged-in user's ID (adjust according to your session or login system)
    $user_id = $_SESSION['user_id']; // Assuming you store user ID in session

    // Prepare the SQL query to insert the answer
    $insert_sql = "INSERT INTO answers (question_id, user_id, answer, created_at) VALUES (?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);

    // Check if prepare failed
    if (!$insert_stmt) {
        die("Answer insertion failed: " . $conn->error);
    }

    // Bind parameters
    $insert_stmt->bind_param("iis", $question_id, $user_id, $answer);

    // Execute the query
    if ($insert_stmt->execute()) {
        // Redirect after successful answer submission
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit(); // Make sure to exit after the redirect
    } else {
        echo "<p>Error posting your answer. Please try again.</p>";
    }
}

// Handle upvote and downvote for answers
if (isset($_POST['vote']) && isset($_POST['answer_id']) && isset($_SESSION['user_id'])) {
    $vote = $_POST['vote']; // 'upvote' or 'downvote'
    $answer_id = $_POST['answer_id'];
    $user_id = $_SESSION['user_id'];

    // Check if the user has already voted on this answer
    $check_vote_sql = "SELECT id FROM answer_votes WHERE answer_id = ? AND user_id = ?";
    $check_vote_stmt = $conn->prepare($check_vote_sql);
    $check_vote_stmt->bind_param("ii", $answer_id, $user_id);
    $check_vote_stmt->execute();
    $existing_vote = $check_vote_stmt->get_result();

    if ($existing_vote->num_rows > 0) {
        // User has already voted, so we update the vote
        $update_vote_sql = "UPDATE answer_votes SET vote = ? WHERE answer_id = ? AND user_id = ?";
        $update_vote_stmt = $conn->prepare($update_vote_sql);
        $update_vote_stmt->bind_param("iii", $vote, $answer_id, $user_id);
        $update_vote_stmt->execute();
    } else {
        // User has not voted, so we insert a new vote record
        $insert_vote_sql = "INSERT INTO answer_votes (answer_id, user_id, vote) VALUES (?, ?, ?)";
        $insert_vote_stmt = $conn->prepare($insert_vote_sql);
        $insert_vote_stmt->bind_param("iii", $answer_id, $user_id, $vote);
        $insert_vote_stmt->execute();
    }

    // Redirect to refresh the page after voting
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Fetch question details
$sql = "SELECT q.id, q.title, q.description, q.created_at, u.name, 
        GROUP_CONCAT(t.name SEPARATOR ', ') as tags, 
        COALESCE(SUM(v.vote), 0) as vote_count
        FROM questions q
        JOIN users u ON q.created_by = u.id
        LEFT JOIN question_tags qt ON q.id = qt.question_id
        LEFT JOIN tags t ON qt.tag_id = t.id
        LEFT JOIN votes v ON q.id = v.question_id
        WHERE q.id = ?
        GROUP BY q.id, q.title, q.description, q.created_at, u.name";

$stmt = $conn->prepare($sql);

// Check if prepare() failed
if (!$stmt) {
    die("SQL preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $question_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $question = $result->fetch_assoc();
} else {
    echo "<h2>Question not found. <a href='index.php'>Go back</a> and try again.</h2>";
    exit;
}

// Fetch the answers related to the question, including the vote count for each answer
$answers_sql = "SELECT a.id, a.answer, a.created_at, u.name, 
                COALESCE(SUM(v.vote), 0) as vote_count
                FROM answers a 
                JOIN users u ON a.user_id = u.id 
                LEFT JOIN answer_votes v ON a.id = v.answer_id
                WHERE a.question_id = ?
                GROUP BY a.id, a.answer, a.created_at, u.name";
$answers_stmt = $conn->prepare($answers_sql);

// Check if prepare() failed for answers query
if (!$answers_stmt) {
    die("Answers SQL preparation failed: " . $conn->error);
}

$answers_stmt->bind_param("i", $question_id);
$answers_stmt->execute();
$answers = $answers_stmt->get_result();
?>

<!-- HTML Section -->
<header class="dashboard-header">
    <div class="container">
        <h1>Question Details</h1>
    </div>
</header>

<div class="thread-container">
    <button id="create-thread-btn">
        <a href="questionPost.php" style="text-decoration: none; color: white;">Ask your Questions!</a>
    </button>
    <button id="create-thread-btn" onclick="window.location.href = 'questions.php';">Back</button>
    
    <hr>

    <div class="thread-card">
        <h1><?= htmlspecialchars($question['title']); ?></h1>
        <div class="question-meta">
            <span>Asked by: <strong><?= htmlspecialchars($question['name']); ?></strong></span> |
            <span>Posted on: <strong><?= date('M d, Y', strtotime($question['created_at'])); ?></strong></span>
        </div>
        <p class="thread-description">
            <?= nl2br(htmlspecialchars($question['description'])); ?>
        </p>
        <div class="question-tags">
            <?php
            $tags = explode(',', $question['tags']);
            foreach ($tags as $tag): ?>
                <span class="tag"><?= htmlspecialchars(trim($tag)); ?></span>
            <?php endforeach; ?>
        </div>
        <p></p>
        <!-- Vote Section for the Question -->
        <div class="question-votes">
            <button class="button" data-id="<?= $question['id'] ?>">⬆ Upvote</button>
            <span class="vote-count"><?= $question['vote_count'] ?></span>
            <button class="button" data-id="<?= $question['id'] ?>">⬇ Downvote</button>
        </div>
    </div>

    <div class="container">
        <h2>Answers</h2>
        <?php if ($answers->num_rows > 0): ?>
            <?php while ($answer = $answers->fetch_assoc()): ?>
                <div class="answer">
                    <p><?= nl2br(htmlspecialchars($answer['answer'])); ?></p> <!-- Display the answer content -->
                    <div class="answer-meta">
                        <span>Answered by: <strong><?= htmlspecialchars($answer['name']); ?></strong></span> |
                        <span>Posted on: <strong><?= date('M d, Y', strtotime($answer['created_at'])); ?></strong></span>
                    </div>
                    <!-- Vote Section for Answers -->
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $question_id; ?>" method="POST">
                        <button type="submit" name="vote" value="1" class="button">⬆ Upvote</button>
                        <span class="vote-count"><?= $answer['vote_count'] ?></span>
                        <button type="submit" name="vote" value="-1" class="button">⬇ Downvote</button>
                        <input type="hidden" name="answer_id" value="<?= $answer['id']; ?>">
                    </form>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No answers yet. Be the first to answer!</p>
        <?php endif; ?>
    </div>

    <!-- Answer Form -->
    <div class="answer-form">
        <h2>Your Answer</h2>
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $question_id; ?>" method="POST">
            <textarea name="answer" rows="8" placeholder="Write your answer here..." required></textarea>
            <button type="submit" class="btn-submit">Post Answer</button>
        </form>
    </div>
</div>

<?php include 'templates/footer.php'; ?>