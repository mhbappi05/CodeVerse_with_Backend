<?php
include 'db.php';
include 'templates/header.php';

// Fetch discussions from the database
$sql = "SELECT discussions.id, discussions.title, discussions.description, users.name AS created_by, 
               (SELECT COUNT(*) FROM replies WHERE replies.discussion_id = discussions.id) AS replies_count
        FROM discussions
        JOIN users ON discussions.created_by = users.id
        ORDER BY discussions.created_at DESC";
$result = $conn->query($sql);
?>

<!--Header-->
<header class="dashboard-header">
    <div class="container">
        <h1>Discussion Forum</h1>
    </div>
</header>

<!-- Discussion Forum Content -->
<main class="discussion-page">
    <div class="container">
        <button id="create-thread-btn">
            <a href="discussion-create.php" style="text-decoration: none; color: white;">+ Add a New Discussion</a>
        </button>
        <hr>

        <!-- List of Discussion Threads -->
        <div class="thread-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="thread-card">
                        <h3><?= htmlspecialchars($row['title']); ?></h3>
                        <p>
                            Started by: <strong><?= htmlspecialchars($row['created_by']); ?></strong> | 
                            Replies: <?= $row['replies_count']; ?>
                        </p>
                        <button class="btn-secondary">
                            <a href="discussion-details.php?id=<?= $row['id']; ?>" style="text-decoration: none; color: white;">
                                View Thread
                            </a>
                        </button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No discussions found. Be the first to start one!</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>
