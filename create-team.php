<?php
include 'db.php';
include 'templates/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $created_by = $_SESSION['user_id'];
    $tags = isset($_POST['team_tags']) ? explode(',', $_POST['team_tags']) : [];
    $invites = isset($_POST['invite_members']) ? explode(',', $_POST['invite_members']) : [];

    // Insert team into the database
    $sql = "INSERT INTO teams (name, description, created_by, members_count, created_at) VALUES (?, ?, ?, 0, NOW())";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        // Output error if query fails to prepare
        die('MySQL Error: ' . $conn->error);
    }

    $stmt->bind_param("ssi", $name, $description, $created_by);

    if ($stmt->execute()) {
        // Get the last inserted team ID
        $team_id = $stmt->insert_id;

        // Insert tags into team_tags table
        foreach ($tags as $tag) {
            $tag = trim($tag); // Clean up spaces
            if (!empty($tag)) {
                $tag_sql = "INSERT INTO team_tags (team_id, tag_id) VALUES (?, (SELECT id FROM tags WHERE name = ? LIMIT 1))";
                $tag_stmt = $conn->prepare($tag_sql);
                if ($tag_stmt === false) {
                    // Output error if query fails to prepare
                    die('MySQL Error: ' . $conn->error);
                }
                $tag_stmt->bind_param("is", $team_id, $tag);
                $tag_stmt->execute();
            }
        }

        // Handle invitations
        foreach ($invites as $email) {
            $email = trim($email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Assuming there's a "members" table to store members
                $invite_sql = "INSERT INTO team_members (team_id, email) VALUES (?, ?)";
                $invite_stmt = $conn->prepare($invite_sql);
                if ($invite_stmt === false) {
                    // Output error if query fails to prepare
                    die('MySQL Error: ' . $conn->error);
                }
                $invite_stmt->bind_param("is", $team_id, $email);
                $invite_stmt->execute();
                // Optionally, send email invite here
            }
        }

        // Redirect to teams page
        header("Location: teams.php");
        exit;
    } else {
        // Output error if the execution fails
        die('MySQL Error: ' . $stmt->error);
    }
}
?>

<!--Header-->
<header class="dashboard-header">
    <div class="container">
        <h1>Create a New Team/Group</h1>
    </div>
</header>

<div class="question-container">
    <?php if (isset($error)): ?>
        <p class="error-message"><?= $error; ?></p>
    <?php endif; ?>
    <div class="create-team-container">
        <form action="create-team.php" method="POST" class="create-team-form">
            <div class="form-group">
                <label for="team-name">Team/Group Name:</label>
                <input type="text" id="team-name" name="name" placeholder="Enter your team name" required>
            </div>

            <div class="form-group">
                <label for="team-description">Description:</label>
                <textarea id="team-description" name="description" placeholder="Describe your team/group in 100 letters" required></textarea>
            </div>

            <div class="form-group">
                <label for="team-tags">Tags (comma-separated):</label>
                <input type="text" id="team-tags" name="team_tags" placeholder="e.g., Web Development, AI">
            </div>

            <div class="form-group">
                <label for="invite-members">Invite Members (Emails):</label>
                <input type="text" id="invite-members" name="invite_members" placeholder="Enter emails separated by commas">
            </div>

            <button type="submit" class="btn">Create Team</button>
            <a href="teams.php" style="text-decoration: none; color: black;">Go back to the Teams page</a>
        </form>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
