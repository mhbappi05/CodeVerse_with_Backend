<?php
include 'templates/header.php';
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['thread_name'];
    $description = $_POST['thread_description'];
    $created_by = $_SESSION['user_id'];

    $sql = "INSERT INTO discussions (title, description, created_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $title, $description, $created_by);

    if ($stmt->execute()) {
        header("Location: discussions.php");
        exit;
    } else {
        $error = "Failed to create discussion. Try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Discussion</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <!--Header-->
    <header class="dashboard-header">
        <div class="container">
            <h1>Add a New Discussion</h1>
        </div>
    </header>

    <div class="question-container">
        <div class="create-team-container">
            <?php if (isset($error)): ?>
                <p class="error-message"><?= $error; ?></p>
            <?php endif; ?>
            <form action="discussion-create.php" method="POST" class="create-team-form">
                <div class="form-group">
                    <label for="team-name">Thread Title:</label>
                    <input type="text" id="team-name" name="thread_name" placeholder="Enter your Thread Title" required>
                </div>

                <div class="form-group">
                    <label for="team-description">Description:</label>
                    <textarea id="team-description" name="thread_description" placeholder="Explain your discussions"
                        required></textarea>
                </div>

                <button type="submit" class="button">Create Discussion</button>
            </form>
            <a href="discussions.php" style="text-decoration: none; color: black;">Go back to the Discussion page</a>
        </div>
    </div>
</body>

</html>
<?php include 'templates/footer.php'; ?>