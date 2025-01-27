<?php
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeVerse</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">CodeVerse</a>
            <ul class="nav-links">
                <li><a href="#">Home</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a> </li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero">
        <div class="container">
            <h1>Collaborate, Code, Conquer!</h1>
            <p>Ask questions, join teams, and grow your career in tech.</p>
            <div class="cta-buttons">
                <a href="#ask-question" class="cta">Ask Your First Question</a>
                <a href="login.html" class="cta">Join Now</a>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <h2>Why Choose CollabConnect?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <h3>Start a Discussion</h3>
                    <p>Get expert answers to your programming queries.</p>
                </div>
                <div class="feature-card">
                    <h3>Ask Questions</h3>
                    <p>Get expert answers to your programming queries.</p>
                </div>
                <div class="feature-card">
                    <h3>Join Teams</h3>
                    <p>Collaborate with like-minded developers and build projects.</p>
                </div>
                <div class="feature-card">
                    <h3>Find Jobs</h3>
                    <p>Discover career opportunities tailored to your skills.</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'templates/footer.php'; ?>