<?php
include 'db.php'; // Database connection file
include 'templates/header.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit;
}

$user_id = $_SESSION['user_id']; // Retrieve user ID from session

// Fetch user data from the database
$query = $conn->prepare("SELECT name FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

// Check if user data is found
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $name = $user['name'];
} else {
    // If no user data is found, redirect to login
    header('Location: login.php');
    exit;
}

$query->close();
$conn->close();
?>

<!-- Dashboard Header -->
<header class="dashboard-header">
    <div class="container">
        <div class="user-info">
            <!-- Display user name -->
            <h1>Welcome, <?= htmlspecialchars($name); ?>!</h1>
        </div>
        <p>Your one-stop hub for questions, discussions, and collaboration.</p>
    </div>
</header>

<!-- Features Section -->
<section id="features" class="features">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card">
                <a href="discussions.php">
                    <h3>Start a Discussion</h3>
                </a>
                <p>Get expert answers to your programming queries.</p>
            </div>
            <div class="feature-card">
                <a href="questions.php">
                    <h3>Ask Questions</h3>
                </a>
                <p>Get expert answers to your programming queries.</p>
            </div>
            <div class="feature-card">
                <a href="teams.php">
                    <h3>Join Teams</h3>
                </a>
                <p>Collaborate with like-minded developers and build projects.</p>
            </div>
            <div class="feature-card">
                <a href="tech-jobs.php">
                    <h3>Find Jobs</h3>
                </a>
                <p>Discover career opportunities tailored to your skills.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'templates/footer.php'; ?>