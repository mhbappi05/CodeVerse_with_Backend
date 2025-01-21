<?php
include 'templates/header.php';
include 'db.php'; // Make sure to include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  die("User not logged in.");
}

$user_id = $_SESSION['user_id']; // Get logged-in user's ID

// Fetch notifications for the logged-in user
$query = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY timestamp DESC";
$result = mysqli_query($conn, $query);

// Check if the query executed successfully
if (!$result) {
  die("Error executing query: " . mysqli_error($conn));
}

// Fetch the notifications as an associative array
$notifications = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Function to convert timestamp to "time ago" format
function time_ago($timestamp)
{
  $time_difference = time() - strtotime($timestamp);
  $seconds = $time_difference;

  if ($seconds <= 60) {
    return "Just now";
  } else if ($seconds <= 3600) {
    return round($seconds / 60) . " minutes ago";
  } else if ($seconds <= 86400) {
    return round($seconds / 3600) . " hours ago";
  } else {
    return round($seconds / 86400) . " days ago";
  }
}
?>

<!-- Header -->
<header class="discussion-header">
  <div class="container">
    <h1>Your Notifications</h1>
  </div>
</header>

<!-- Notifications Content -->
<main class="notifications-page">
  <div class="container">
    <ul class="notifications-list">
      <?php if (empty($notifications)): ?>
        <li class="notification-item">No notifications available.</li>
      <?php else: ?>
        <?php foreach ($notifications as $notification): ?>
          <li class="notification-item">
            <p>
              <strong><?php echo htmlspecialchars($notification['sender_id']); ?></strong>
              <?php
              // Display the message based on action type
              switch ($notification['action_type']) {
                case 'reply':
                  echo "replied to your thread.";
                  break;
                case 'answer':
                  echo "answered your question.";
                  break;
                case 'upvote':
                  echo "upvoted your answer.";
                  break;
                case 'downvote':
                  echo "downvoted your answer.";
                  break;
                case 'join_team':
                  echo "joined your team.";
                  break;
                case 'message':
                  echo "sent you a message.";
                  break;
                default:
                  echo "performed an action.";
                  break;
              }
              ?>
            </p>
            <span class="timestamp"><?php echo time_ago($notification['timestamp']); ?></span>
            <a href="mark_as_read.php?id=<?php echo $notification['id']; ?>" class="mark-as-read">Mark as Read</a>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>
  </div>
</main>

<?php include 'templates/footer.php'; ?>