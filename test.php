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
        // Redirect to avoid re-posting the answer on refresh
        header("Location: questionDetails.php?id=" . $question_id);
        exit();
    } else {
        echo "<p>Error posting your answer. Please try again.</p>";
    }
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

// Fetch the answers related to the question
$answers_sql = "SELECT a.id, a.answer, a.created_at, u.name 
                FROM answers a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.question_id = ?";
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
<header class="discussion-header">
    <div class="container">
        <h1>Question Details</h1>
    </div>
</header>

<div class="question-details-container">
    <button class="button">
        <a href="questionPost.php" style="text-decoration: none; color: white;">Ask your Questions!</a>
    </button>
    <div class="question-content">
        <h1><?= htmlspecialchars($question['title']); ?></h1>
        <div class="question-meta">
            <span>Asked by: <strong><?= htmlspecialchars($question['name']); ?></strong></span> |
            <span>Posted on: <strong><?= date('M d, Y', strtotime($question['created_at'])); ?></strong></span>
        </div>
        <p class="question-description">
            <?= nl2br(htmlspecialchars($question['description'])); ?>
        </p>
        <div class="question-tags">
            <?php
            $tags = explode(',', $question['tags']);
            foreach ($tags as $tag): ?>
                <span class="tag"><?= htmlspecialchars(trim($tag)); ?></span>
            <?php endforeach; ?>
        </div>
         <!-- Vote Section for the Question -->
         <div class="question-votes">
            <button class="vote-button upvote" data-id="<?= $question['id'] ?>">⬆ Upvote</button>
            <span class="vote-count"><?= $question['vote_count'] ?></span>
            <button class="vote-button downvote" data-id="<?= $question['id'] ?>">⬇ Downvote</button>
        </div>
    </div>

    <div class="answers-section">
        <h2>Answers</h2>
        <?php if ($answers->num_rows > 0): ?>
            <?php while ($answer = $answers->fetch_assoc()): ?>
                <div class="answer">
                    <p><?= nl2br(htmlspecialchars($answer['answer'])); ?></p> <!-- Display the answer content -->
                    <div class="answer-meta">
                        <span>Answered by: <strong><?= htmlspecialchars($answer['name']); ?></strong></span> |
                        <span>Posted on: <strong><?= date('M d, Y', strtotime($answer['created_at'])); ?></strong></span>
                    </div>
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
