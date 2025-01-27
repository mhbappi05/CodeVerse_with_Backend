<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeVerse</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dropdown-menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">CodeVerse</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="discussions.php">Discussion</a></li>
                <li><a href="questions.php">Questions</a></li>
                <li><a href="tech-jobs.php">Jobs</a></li>
                <li><a href="teams.php">Teams</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="notifications.php">Notifications</a></li>
                    <li><a href="private-messaging.php">Messages</a></li>
                    <li class="dropdown">
                        <button class="dropdown-btn">
                            <span class="user-name"><?= $_SESSION['user_name']; ?></span>
                            <span class="dropdown-icon">▼</span>
                        </button>
                        <div class="dropdown-content">
                            <div class="profile-section">
                                <div class="profile-item">
                                    <span><a href="user-profile.php">Profiles</a></span>
                                </div>
                                <div class="profile-item">
                                    <span><a href="pinned-notes.php">Pin Posts</a></span>
                                </div>
                            </div>
                            <hr>
                            <ul class="menu-options">
                                <li><a href="#">Settings</a></li>
                                <li><a href="logout.php">Log Out</a></li>
                            </ul>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>