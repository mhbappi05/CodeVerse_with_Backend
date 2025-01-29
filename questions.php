<?php
include 'db.php';
include 'templates/header.php';

// Fetch questions with their vote counts
$sql = "SELECT q.id, q.title, q.description, q.created_at, u.name, 
        GROUP_CONCAT(t.name SEPARATOR ', ') as tags, 
        COALESCE(SUM(v.vote), 0) as vote_count
        FROM questions q
        JOIN users u ON q.created_by = u.id
        LEFT JOIN question_tags qt ON q.id = qt.question_id
        LEFT JOIN tags t ON qt.tag_id = t.id
        LEFT JOIN votes v ON q.id = v.question_id
        GROUP BY q.id, q.title, q.description, q.created_at, u.name
        ORDER BY q.created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Error in SQL query: " . $conn->error);
}
?>

<!-- Header -->
<header class="dashboard-header">
    <div class="container">
        <h1>Questions</h1>
    </div>
</header>

<!-- Question List Section -->
<div class="dashboard-page">
    <div class="container">
        <button id="create-thread-btn">
            <a href="questionPost.php" style="text-decoration: none; color: white;">Ask your Questions!</a>
        </button>
        <hr>
        
        <div class="thread-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="thread-card" id="question-<?= $row['id'] ?>">
                        <h1><a href="questionDetails.php?id=<?= $row['id'] ?>"
                                style="text-decoration: none; color: black; font-size: 20px; "><?= htmlspecialchars($row['title']) ?></a>
                        </h1>
                        <div class="question-meta">
                            <span>Asked by: <strong><?= htmlspecialchars($row['name']) ?></strong></span> |
                            <span>Posted on: <strong><?= date("M d, Y", strtotime($row['created_at'])) ?></strong></span>
                        </div>
                        <div class="question-tags">
                            <?php
                            $tags = explode(',', $row['tags']);
                            foreach ($tags as $tag): ?>
                                <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <p></p>
                        <button class="btn-secondary">
                            <a href="questions-details.php?id=<?= $row['id']; ?>" style="text-decoration: none; color: white;">
                                View Questions
                            </a>
                        </button>
                        <hr>
                        <div class="question-votes">
                            <button class="vote-button upvote" data-id="<?= $row['id'] ?>">⬆ Upvote</button>
                            <span class="vote-count"><?= $row['vote_count'] ?></span>
                            <button class="vote-button downvote" data-id="<?= $row['id'] ?>">⬇ Downvote</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No questions have been asked yet. Be the first to ask one!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.vote-button').forEach(button => {
        button.addEventListener('click', function () {
            const questionId = this.getAttribute('data-id');
            const isUpvote = this.classList.contains('upvote');

            fetch('vote.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: questionId, vote: isUpvote ? 1 : -1 })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const voteCountElement = document.querySelector(`#question-${questionId} .vote-count`);
                        voteCountElement.textContent = data.newVoteCount;
                    } else {
                        alert('Failed to cast vote.');
                    }
                });
        });
    });
</script>

<?php include 'templates/footer.php'; ?>