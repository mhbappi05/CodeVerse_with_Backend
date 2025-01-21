<?php
session_start();  // Start the session to access session variables
include 'db.php'; // Make sure this file includes your database connection

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the logged-in user's ID from the session
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page if the user is not logged in
        header('Location: login.php');
        exit();
    }
    $user_id = $_SESSION['user_id']; // Assume user_id is stored in session after login

    // Get data from form
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $created_at = date('Y-m-d H:i:s'); // Current timestamp

    // Insert new announcement into the database with the user_id
    $query = "INSERT INTO announcements (title, content, created_at, is_pinned, user_id) 
              VALUES ('$title', '$content', '$created_at', 1, '$user_id')";
    if (mysqli_query($conn, $query)) {
        // Redirect to the pinned posts page after success
        header('Location: pinned-notes.php');
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
