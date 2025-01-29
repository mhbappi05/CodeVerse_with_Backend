<?php
include 'db.php';
include 'templates/header.php';

// Get discussion ID from the query parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Invalid discussion ID.</p>";
    exit;
}

$discussion_id = intval($_GET['id']);

// Fetch discussion details
$sql = "SELECT discussions.title, discussions.description, users.name AS created_by, discussions.created_at 
        FROM discussions 
        JOIN users ON discussions.created_by = users.id 
        WHERE discussions.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $discussion_id);
$stmt->execute();
$discussion = $stmt->get_result()->fetch_assoc();

if (!$discussion) {
    echo "<p>Discussion not found.</p>";
    exit;
}

// Handle new reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply_content = trim($_POST['reply']);
    $user_id = $_SESSION['user_id']; // Assuming user authentication is implemented

    if (!empty($reply_content)) {
        $reply_sql = "INSERT INTO replies (discussion_id, user_id, content) VALUES (?, ?, ?)";
        $reply_stmt = $conn->prepare($reply_sql);
        $reply_stmt->bind_param("iis", $discussion_id, $user_id, $reply_content);

        if ($reply_stmt->execute()) {
            header("Location: discussion-details.php?id=$discussion_id");
            exit;
        } else {
            $error = "Failed to post reply. Please try again.";
        }
    } else {
        $error = "Reply content cannot be empty.";
    }
}

// Fetch replies for the discussion
$replies_sql = "SELECT replies.content, replies.created_at, users.name AS replied_by 
                FROM replies 
                JOIN users ON replies.user_id = users.id 
                WHERE replies.discussion_id = ?
                ORDER BY replies.created_at ASC";
$replies_stmt = $conn->prepare($replies_sql);
$replies_stmt->bind_param("i", $discussion_id);
$replies_stmt->execute();
$replies = $replies_stmt->get_result();
?>

<!--Header-->
<header class="dashboard-header">
    <div class="container">
        <h1>Discussions Details</h1>
    </div>
</header>

<!-- Thread Details Section -->
<div class="thread-container">
    <button id="create-thread-btn">
        <a href="discussion-create.php" style="text-decoration: none; color: white;">+ Add a New Discussion</a>
    </button>
    <button id="create-thread-btn" onclick="window.location.href = 'discussions.php';">Back</button>

    <hr>

    <div class="thread-card">
        <div class="thread-header">
            <h1><?= htmlspecialchars($discussion['title']); ?></h1>
            <p><strong>Started by:</strong> <?= htmlspecialchars($discussion['created_by']); ?> on
                <?= date('F j, Y', strtotime($discussion['created_at'])); ?>
            </p>
        </div>

        <div class="thread-description">
            <p><?= nl2br(htmlspecialchars($discussion['description'])); ?></p>
        </div>
    </div>
    <hr>

    <!-- Replies Section -->
    <div class="replies-section">
        <h2>Replies</h2>
        <?php if ($replies->num_rows > 0): ?>
            <?php while ($reply = $replies->fetch_assoc()): ?>
                <div class="reply-card">
                    <div class="reply-content">
                        <p><strong><?= htmlspecialchars($reply['replied_by']); ?>:</strong>
                            <?= nl2br(htmlspecialchars($reply['content'])); ?></p>
                        <p class="reply-timestamp">Posted on <?= date('F j, Y', strtotime($reply['created_at'])); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No replies yet. Be the first to reply!</p>
        <?php endif; ?>
    </div>

    <!-- Post a Reply Form -->
    <div class="reply-form">
        <h2>Post Your Reply</h2>
        <?php if (isset($error)): ?>
            <p class="error-message"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <textarea name="reply" rows="4" placeholder="Write your reply here..." required></textarea>
            <button type="submit" class="btn-submit">Post Reply</button>
        </form>
    </div>
</div>

<?php include 'templates/footer.php'; ?>