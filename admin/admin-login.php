<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "collab_connect";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Check if user exists and is an admin
    $sql = "SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_role'] = $user['role'];
            
            // Redirect to admin panel
            header("Location: admin-panel.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Invalid email or you don't have admin privileges";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CollabConnect</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #212529;
        }

        .admin-login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)),
                url('images/admin-bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        

        .admin-login-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .admin-login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .admin-login-header h1 {
            color: #fff;
            /* White text for better contrast */
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            /* Subtle text shadow */
        }

        .admin-login-header p {
            color: rgba(255, 255, 255, 0.9);
            /* Slightly transparent white */
            font-size: 1rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .admin-login-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: #fff;
            /* White text */
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .admin-login-card {
            background: rgba(23, 32, 42, 0.8);
            /* Dark background with transparency */
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-login-form input {
            width: 93%;
            padding: 0.75rem;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .admin-login-form input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .admin-login-form input:focus {
            border-color: #4CAF50;
            /* Professional green */
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
            outline: none;
        }

        .admin-login-btn {
            width: 100%;
            padding: 0.75rem;
            background: #4CAF50;
            /* Professional green */
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 20px;
        }

        .admin-login-btn:hover {
            background: #45a049;
            /* Darker green on hover */
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }

        .error-message {
            background: rgba(255, 82, 82, 0.95);
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .back-to-home {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-to-home a {
            color: #fff;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            backdrop-filter: blur(5px);
        }

        .back-to-home a:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .admin-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .admin-logo img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 3px;
            background: white;
            border: 2px solid #1a237e;
        }

        /* Add animation for the card */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .admin-login-card {
            animation: fadeIn 0.5s ease-out;
        }

        /* Add glass morphism effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
    </style>
</head>

<body>
    <div class="admin-login-container">
        <div class="admin-login-card">
            <div class="admin-logo">
                <img src="images/admin-logo.jpg" alt="Admin Logo">
            </div>
            <div class="admin-login-header">
                <h1>Admin Login</h1>
                <p>Enter your credentials to access the admin panel</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form class="admin-login-form" method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>

                <button type="submit" class="admin-login-btn">Login to Dashboard</button>
            </form>

            <div class="back-to-home">
                <a href="../index.php">← Back to Home</a>
            </div>
        </div>
    </div>
</body>

</html>