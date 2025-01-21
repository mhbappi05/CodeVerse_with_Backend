<?php
include 'templates/header.php';
include 'db.php';  // Make sure this file includes your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch pinned posts for the logged-in user from database
$query = "SELECT * FROM announcements WHERE is_pinned = 1 AND user_id = $user_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Check for query error
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!--Header-->
<header class="discussion-header">
    <div class="container">
        <h1>Pinned Posts</h1>
    </div>
</header>

<!-- Pinned Posts Content -->
<main class="pinned-posts-page">
    <div class="container">
      <section class="pinned-section">
        <h2>Announcements</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
          <?php while($post = mysqli_fetch_assoc($result)): ?>
            <div class="pinned-post">
              <h3><?php echo htmlspecialchars($post['title']); ?></h3>
              <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
              <span class="timestamp">Pinned: <?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p>No pinned announcements yet.</p>
        <?php endif; ?>

      </section>

      <!-- Admin Control for Group Announcements -->
      <section class="create-announcement">
        <h2>Create New Announcement</h2>
        <form action="create_announcement.php" method="POST">
          <label for="announcement-title">Title:</label>
          <input type="text" id="announcement-title" name="title" required>

          <label for="announcement-content">Content:</label>
          <textarea id="announcement-content" name="content" rows="5" required></textarea>

          <button type="submit" class="btn btn-primary">Pin Announcement</button>
        </form>
      </section>
    </div>
</main>

<?php include 'templates/footer.php'; ?>
