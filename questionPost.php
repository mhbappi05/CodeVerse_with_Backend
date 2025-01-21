<?php
include 'templates/header.php';
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['qus_name'];
    $question = $_POST['qus_description'];
    $tags = $_POST['question-tags'];
    $created_by = $_SESSION['user_id'];

    // Insert the discussion (thread) into the database
    $sql = "INSERT INTO questions (title, description, created_by) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $title, $question, $created_by);

    if ($stmt->execute()) {
        // Get the last inserted discussion ID
        $question_id = $stmt->insert_id;

        // Insert the tags into the database
        $tags_array = explode(',', $tags);
        foreach ($tags_array as $tag) {
            $tag = trim($tag); // Remove extra spaces
            // Check if the tag already exists in the tags table
            $tag_sql = "SELECT id FROM tags WHERE name = ?";
            $tag_stmt = $conn->prepare($tag_sql);
            $tag_stmt->bind_param("s", $tag);
            $tag_stmt->execute();
            $tag_result = $tag_stmt->get_result();

            if ($tag_result->num_rows > 0) {
                // Tag exists, get the tag ID
                $tag_row = $tag_result->fetch_assoc();
                $tag_id = $tag_row['id'];
            } else {
                // Insert new tag
                $insert_tag_sql = "INSERT INTO tags (name) VALUES (?)";
                $insert_tag_stmt = $conn->prepare($insert_tag_sql);
                $insert_tag_stmt->bind_param("s", $tag);
                $insert_tag_stmt->execute();
                $tag_id = $insert_tag_stmt->insert_id;
            }

            // Link the tag to the question
            $insert_question_tag_sql = "INSERT INTO question_tags (question_id, tag_id) VALUES (?, ?)";
            $insert_question_tag_stmt = $conn->prepare($insert_question_tag_sql);
            $insert_question_tag_stmt->bind_param("ii", $question_id, $tag_id);
            $insert_question_tag_stmt->execute();
        }

        header("Location: questions.php");
        exit;
    } else {
        $error = "Failed to make question. Try again.";
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
    <header class="discussion-header">
        <div class="container">
            <h1>Ask a Programming Question</h1>
        </div>
    </header>

    <div class="question-container">
        <div class="create-team-container">
            <?php if (isset($error)): ?>
                <p class="error-message"><?= $error; ?></p>
            <?php endif; ?>
            <form action="questionPost.php" method="POST" class="create-team-form">
                <div class="form-group">
                    <label for="question-title">Question Title</label>
                    <input type="text" id="team-name" name="qus_name" placeholder="Enter a concise title" required>
                </div>

                <div class="form-group">
                    <label for="question-description">Description</label>
                    <textarea id="team-description" name="qus_description"
                        placeholder="Describe your question in detail" required></textarea>
                </div>

                <div class="form-group">
                    <label for="question-tags">Tags</label>
                    <input type="text" id="question-tags" name="question-tags"
                        placeholder="e.g., JavaScript, Python, HTML" required>
                </div>

                <button type="submit" class="button">Post Question</button>
            </form>
            <a href="questions.php" style="text-decoration: none; color: black;">Go back to the Question page</a>
        </div>
    </div>
</body>

</html>

<?php include 'templates/footer.php'; ?>