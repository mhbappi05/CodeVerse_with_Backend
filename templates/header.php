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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body>
    <nav class="navbar">
        <div class="head">
            <a href="index.php" class="logo">
                <img src="/templates/icons/CodeVerse.png" alt="CodeVerse Logo" class="logo-image">
                CodeVerse
            </a>
            <ul class="nav-links">
                <li><a href="dashboard.php"><img src="/templates/icons/Home.png" alt="CodeVerse Logo" class="icons-image"></a></li>
                <li><a href="discussions.php"><img src="/templates/icons/Discussion.png" alt="CodeVerse Logo" class="icons-image"></a></li>
                <li><a href="questions.php"><img src="/templates/icons/question.png" alt="CodeVerse Logo" class="icons-image"></a></li>
                <li><a href="tech-jobs.php"><img src="/templates/icons/jobs.png" alt="CodeVerse Logo" class="icons-image"></a></li>
                <li><a href="teams.php"><img src="/templates/icons/Teams.png" alt="CodeVerse Logo" class="icons-image"></a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="notifications.php"><img src="/templates/icons/notifications.png" alt="CodeVerse Logo" class="icons-image"></a></li>
                    <li><a href="private-messaging.php"><img src="/templates/icons/chat.png" alt="CodeVerse Logo" class="icons-image"></a></li>
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