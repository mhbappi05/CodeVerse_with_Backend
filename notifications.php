<?php
include 'db.php';
include 'templates/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/dropdown-menu.css">
  <style>
    .notification-item.unread {
        background-color: #f0f7ff;
        border-left: 3px solid #0066cc;
    }
  </style>
</head>

<body>
  <!--Header-->
  <header class="dashboard-header">
    <div class="container">
      <h1>Your Notifications</h1>
    </div>
  </header>

  <!-- Notifications Content -->
  <main class="notifications-page">
    <div class="container">
      <ul class="notifications-list">
        <?php
        // Database connection
        require_once 'db.php';
        
        if (!isset($_SESSION['user_id'])) {
            // Redirect to login if user is not logged in
            header('Location: login.php');
            exit();
        }

        $user_id = $_SESSION['user_id'];

        // Function to create a notification
        function createNotification($conn, $receiver_id, $sender_id, $type, $content_id, $action = null) {
            $query = "INSERT INTO notifications (receiver_id, sender_id, type, content_id, action, is_read) 
                      VALUES (?, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iisis", $receiver_id, $sender_id, $type, $content_id, $action);
            return $stmt->execute();
        }

        // Function to get random user ID (for testing)
        function getRandomUser($conn, $exclude_id) {
            $query = "SELECT id FROM users WHERE id != ? ORDER BY RAND() LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $exclude_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc()['id'] ?? null;
        }

        // Create a new test notification every time the page is loaded (for testing)
        $random_user = getRandomUser($conn, $user_id);
        if ($random_user) {
            $notification_types = [
                ['type' => 'answer', 'action' => null],
                ['type' => 'vote', 'action' => 'upvote'],
                ['type' => 'team_join', 'action' => null]
            ];
            
            $random_type = $notification_types[array_rand($notification_types)];
            createNotification($conn, $user_id, $random_user, $random_type['type'], 1, $random_type['action']);
        }

        // Fetch notifications with actual user information
        $query = "SELECT n.*, 
                  CASE 
                    WHEN u.role = 'admin' THEN 'Admin'
                    ELSE u.name 
                  END as sender_name,
                  n.created_at
                  FROM notifications n
                  LEFT JOIN users u ON n.sender_id = u.id
                  WHERE n.receiver_id = ?
                  ORDER BY n.created_at DESC
                  LIMIT 10";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $notification_text = '';
                $time_ago = time_elapsed_string($row['created_at']);
                $sender_name = $row['sender_name'] ?? 'Someone';
                
                switch ($row['type']) {
                    case 'answer':
                        $notification_text = "<strong>{$sender_name}</strong> answered your question";
                        break;
                    case 'vote':
                        $vote_type = $row['action'] == 'upvote' ? 'upvoted' : 'downvoted';
                        $notification_text = "<strong>{$sender_name}</strong> {$vote_type} your post";
                        break;
                    case 'team_join':
                        $notification_text = "<strong>{$sender_name}</strong> wants to join your team";
                        break;
                }
                ?>
                <li class="notification-item <?php echo !$row['is_read'] ? 'unread' : ''; ?>" 
                    data-notification-id="<?php echo $row['id']; ?>">
                    <div class="notification-content">
                        <p><?php echo $notification_text; ?></p>
                        <span class="timestamp"><?php echo $time_ago; ?></span>
                    </div>
                </li>
                <?php
            }
        } else {
            ?>
            <li class="notification-item">
                <div class="notification-content">
                    <p>No notifications yet!</p>
                </div>
            </li>
            <?php
        }

        function time_elapsed_string($datetime) {
            $now = new DateTime;
            $ago = new DateTime($datetime);
            $diff = $now->diff($ago);

            if ($diff->d == 0) {
                if ($diff->h == 0) {
                    if ($diff->i == 0) {
                        return "Just now";
                    }
                    return $diff->i . " minutes ago";
                }
                return $diff->h . " hours ago";
            }
            if ($diff->d == 1) {
                return "Yesterday";
            }
            return $diff->d . " days ago";
        }
        ?>
      </ul>
    </div>
  </main>

  <!-- Add JavaScript to mark notifications as read when clicked -->
  <script>
  document.querySelectorAll('.notification-item').forEach(item => {
      item.addEventListener('click', function() {
          const notificationId = this.dataset.notificationId;
          if (notificationId) {
              fetch('mark_notification_read.php', {
                  method: 'POST',
                  body: JSON.stringify({ notification_id: notificationId }),
                  headers: {
                      'Content-Type': 'application/json'
                  }
              });
              this.classList.remove('unread');
          }
      });
  });
  </script>

  <!-- Add custom styles -->
  <style>
    .notification-item {
        padding: 15px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .notification-item:hover {
        background-color: #f8f9fa;
    }
    
    .notification-item.unread {
        background-color: #f0f7ff;
        border-left: 3px solid #0066cc;
    }
    
    .notification-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    
    .notification-content p {
        margin: 0;
        flex-grow: 1;
    }
    
    .timestamp {
        color: #6c757d;
        font-size: 0.85em;
        white-space: nowrap;
        margin-left: 15px;
    }
  </style>
</body>

</html>
  
  <?php include 'templates/footer.php'; ?>