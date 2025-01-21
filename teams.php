<?php
include 'db.php';
include 'templates/header.php'; // Session is already started here

// Get the user ID from the session (it should be set after login)
$user_id = $_SESSION['user_id'] ?? null; // If the user is not logged in, $user_id will be null

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team_id = $_POST['team_id'];

    // Check if the user is logged in
    if ($user_id === null) {
        $error_message = "You must be logged in to join a team!";
    } else {
        // Check if $conn is a valid MySQL connection
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Step 1: Check if the user is already a member of the team
        $check_sql = "SELECT * FROM team_members WHERE team_id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);

        // Check if the prepare statement was successful
        if ($check_stmt === false) {
            die("Failed to prepare statement: " . $conn->error); // Output detailed error
        }

        // Bind parameters and execute the query
        $check_stmt->bind_param("ii", $team_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // User is already a member
            $error_message = "You are already a member of this team!";
        } else {
            // Step 2: Add the user to the team
            $insert_sql = "INSERT INTO team_members (team_id, user_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);

            // Check if the insert statement was successful
            if ($insert_stmt === false) {
                die("Failed to prepare insert statement: " . $conn->error); // Output detailed error
            }

            // Bind parameters and execute the insert query
            $insert_stmt->bind_param("ii", $team_id, $user_id);

            if ($insert_stmt->execute()) {
                // Step 3: Update the members count in the teams table
                $update_sql = "UPDATE teams SET members_count = members_count + 1 WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $team_id);
                $update_stmt->execute();

                // Redirect to the teams page after joining
                header("Location: teams.php");
                exit;
            } else {
                // Debugging: Output detailed error message from the MySQL query
                $error_message = "Failed to join the team. MySQL Error: " . $insert_stmt->error;
            }
        }
    }
}

// Fetch all teams from the database
$sql = "SELECT * FROM teams";
$result = $conn->query($sql);

if ($result === false) {
    die("Error fetching teams: " . $conn->error); // Handle potential query errors
}
?>

<!-- Header Section -->
<header class="discussion-header">
    <div class="container">
        <h1>Explore Teams & Groups</h1>
    </div>
</header>

<div class="container">
    <h1>Teams</h1>
    <button class="button"><a href="create-team.php" style="text-decoration: none; color: white;">+ Create a Team</a></button>
    <hr>

    <?php if (isset($error_message)): ?>
        <p class="error-message"><?= $error_message; ?></p>
    <?php endif; ?>

    <div class="team-list">
        <?php while ($team = $result->fetch_assoc()): ?>
            <div class="team-card">
                <h3><?= $team['name']; ?></h3>
                <p><?= $team['description']; ?></p>
                <span class="team-members">Members: <?= $team['members_count']; ?></span>
                <!-- Pass team_id to join the team -->
                <form action="teams.php" method="POST">
                    <input type="hidden" name="team_id" value="<?= $team['id']; ?>">
                    <button type="submit" class="btn btn-secondary">Join Team</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
