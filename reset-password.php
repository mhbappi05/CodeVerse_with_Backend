<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CollabConnect</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php
    // Include Composer's autoload to automatically load PHPMailer
    require 'vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // Database connection
    $host = 'localhost';
    $db = 'collab_connect';
    $user = 'root'; // Update with your database username
    $password = ''; // Update with your database password

    $conn = new mysqli($host, $user, $password, $db);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);

        // Validate email
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Generate a unique reset token
                $reset_token = bin2hex(random_bytes(32));
                $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Store the reset token in the database
                $stmt = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
                $stmt->bind_param("sss", $reset_token, $token_expiry, $email);
                $stmt->execute();

                // Send reset link via email using PHPMailer
                $reset_link = "http://yourdomain.com/reset_password.php?token=" . $reset_token;

                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'youremail@gmail.com'; // Your Gmail email
                    $mail->Password = 'yourapppassword';    // Your Gmail app password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Recipients
                    $mail->setFrom('youremail@gmail.com', 'CollabConnect');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request';
                    $mail->Body    = "Click the following link to reset your password: <a href='$reset_link'>$reset_link</a>";

                    $mail->send();
                    echo "<p>A reset link has been sent to your email.</p>";
                } catch (Exception $e) {
                    echo "<p>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
                }
            } else {
                echo "<p>Email not found in our records.</p>";
            }
        } else {
            echo "<p>Invalid email address.</p>";
        }
    }
    ?>

    <div class="auth-container">
        <div class="auth-form">
            <h1>Reset Your Password</h1>
            <form action="" method="POST">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>

                <button type="submit" class="auth-btn">Send Reset Link</button>
            </form>
            <p>Remember your password? <a href="login.html">Login here</a></p>
        </div>
    </div>
</body>

</html>
