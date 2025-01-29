<?php
// Database connection
$link = mysqli_connect('localhost', 'root', '', 'collab_connect');
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

session_start();

// Initialize variables
$new_password = $confirm_password = $email = "";
$new_password_err = $confirm_password_err = $email_err = "";

// Process form data when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email address.";
    } else {
        $email = trim($_POST["email"]);
        // Check if email exists in database
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 0) {
                    $email_err = "No account found with that email address.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter the new password.";     
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have at least 6 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before updating the database
    if (empty($new_password_err) && empty($confirm_password_err) && empty($email_err)) {
        // Prepare an update statement
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_password, $email);
            
            // Set parameters
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Send confirmation email
                $to = $email;
                $subject = "Password Reset Confirmation";
                $message = "Your password has been successfully reset. If you did not make this change, please contact support immediately.";
                $headers = "From: your-email@domain.com";

                mail($to, $subject, $message, $headers);

                // Password updated successfully. Redirect to login page
                session_destroy();
                header("location: login.php");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-form">
            <h1><i class="fas fa-lock"></i> Reset Password</h1>
            <p class="reset-intro">Please fill out this form to reset your password.</p>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $email; ?>">
                    <span class="error-message"><?php echo $email_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($new_password_err)) ? 'has-error' : ''; ?>">
                    <label>New Password</label>
                    <div class="password-input-group">
                        <input type="password" name="new_password" class="form-control" value="<?php echo $new_password; ?>">
                        <i class="fas fa-eye password-toggle"></i>
                    </div>
                    <span class="error-message"><?php echo $new_password_err; ?></span>
                </div>
                
                <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                    <label>Confirm Password</label>
                    <div class="password-input-group">
                        <input type="password" name="confirm_password" class="form-control">
                        <i class="fas fa-eye password-toggle"></i>
                    </div>
                    <span class="error-message"><?php echo $confirm_password_err; ?></span>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="auth-btn">Reset Password</button>
                    <hr>
                    <button type="button" class="auth-btn" onclick="window.location.href='index.php'">Cancel</button>
                </div>
            </form>
        </div>


    </div>

    <script>
        // Toggle password visibility
        document.querySelectorAll('.password-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>

</html>
