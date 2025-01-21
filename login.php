<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "No user found with this email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CollabConnect</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-form">
            <h1>Login to Your Account</h1>
            <?php if (isset($error)): ?>
                <p class="error-message"><?= $error; ?></p>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>

                <button type="submit" class="auth-btn"><a href="dashboard.php" id="logina">Login</a></button>
            </form>
            <p>Forgot your password? <a href="reset-password.php">Reset it here</a></p>
            <p>Don't have an account? <a href="register.php">Register now</a></p>
            <p>Going back to the home <a href="index.php">Back</a></p>
        </div>
    </div>
</body>

</html>