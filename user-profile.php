<?php
include 'db.php'; // Include the database connection
include 'templates/header.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Default values for bio, profile picture, and skills
if (empty($user['profile_picture'])) {
    $user['profile_picture'] = 'assets/user.png';
}
if (empty($user['bio'])) {
    $user['bio'] = 'No bio added yet.';
}
if (empty($user['skills'])) {
    $user['skills'] = 'No skills added yet.';
}

// Update profile logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = $_POST['bio'];
    $skills = $_POST['skills'];

    // Handle profile picture upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
        $profile_picture = $target_file;

        // Update database with profile picture
        $update_query = "UPDATE users SET profile_picture = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        if (!$stmt) {
            die("Error preparing query: " . $conn->error);
        }
        $stmt->bind_param("si", $profile_picture, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Update bio and skills
    $update_query = "UPDATE users SET bio = ?, skills = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    if (!$stmt) {
        die("Error preparing query: " . $conn->error);
    }
    $stmt->bind_param("ssi", $bio, $skills, $user_id);
    $stmt->execute();
    $stmt->close();

    // Reload the page to reflect updates
    header("Location: user-profile.php");
    exit;
}

// Fetch announcements saved by the user
$saved_query = "SELECT * FROM announcements WHERE user_id = ? AND is_pinned = 1";
$stmt = $conn->prepare($saved_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$saved_result = $stmt->get_result();
$saved_items = $saved_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch teams created or joined by the user
$teams_query = "SELECT * FROM teams WHERE created_by = ? OR id IN (SELECT team_id FROM team_members WHERE user_id = ?)";
$stmt = $conn->prepare($teams_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$teams_result = $stmt->get_result();
$teams = $teams_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch questions created by the user
$questions_query = "SELECT * FROM questions WHERE created_by = ?";
$stmt = $conn->prepare($questions_query);
$stmt->bind_param("i", $user_id); // Assuming $user_id is the logged-in user's ID
$stmt->execute();
$questions_result = $stmt->get_result();
$questions = $questions_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>


<!-- HTML Content -->
<main class="profile-page">
    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-picture">
                <img src="<?= $user['profile_picture'] ?>" alt="User Profile Picture">
            </div>
            <div class="profile-info">
                <h1><?= htmlspecialchars($user['name']) ?></h1>
                <p><strong>Bio:</strong> <?= htmlspecialchars($user['bio']) ?></p>
                <p><strong>Skills:</strong> <?= htmlspecialchars($user['skills']) ?></p>
                <p><strong>Reputation:</strong> <?= isset($user['reputation']) ? $user['reputation'] : 0 ?></p>
                <button id="edit-profile-btn" class="btn">Edit Profile</button>
            </div>
        </div>

        <!-- Edit Form (Hidden by Default) -->
        <form id="edit-profile-form" action="user-profile.php" method="POST" enctype="multipart/form-data"
            style="display: none;">
            <div class="form-group">
                <label for="profile_picture">Upload Profile Picture</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
            </div>
            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio"
                    placeholder="Add your bio"><?= htmlspecialchars($user['bio']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="skills">Skills</label>
                <textarea id="skills" name="skills"
                    placeholder="Add your skills"><?= htmlspecialchars($user['skills']) ?></textarea>
            </div>
            <button type="submit" class="btn">Save Changes</button>
        </form>

        <!-- Tabs -->
        <div class="profile-tabs">
            <button class="pro-btn active" data-tab="saved-tab">Saved Notes</button>
            <button class="pro-btn" data-tab="teams-tab">Teams</button>
            <button class="pro-btn" data-tab="questions-tab">Questions</button>
        </div>

        <!-- Saved Announcements -->
        <div class="tab-content" id="saved-tab">
            <h2>Saved Notes</h2>
            <ul>
                <?php foreach ($saved_items as $item): ?>
                    <li><a href="view-announcement.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Teams -->
        <div class="tab-content" id="teams-tab" style="display: none;">
            <h2>Teams</h2>
            <ul>
                <?php foreach ($teams as $team): ?>
                    <li><a href="view-team.php?id=<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Questions Tab -->
        <div class="tab-content" id="questions-tab" style="display: none;">
            <h2>Your Questions</h2>
            <ul>
                <?php if (empty($questions)): ?>
                    <li>No questions found.</li>
                <?php else: ?>
                    <?php foreach ($questions as $question): ?>
                        <li>
                            <a href="view-question.php?id=<?= $question['id'] ?>">
                                <h3><?= htmlspecialchars($question['title']) ?></h3>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

    </div>
</main>

<script>
    // Toggle visibility of the edit form
    const editBtn = document.getElementById('edit-profile-btn');
    const editForm = document.getElementById('edit-profile-form');
    editBtn.addEventListener('click', () => {
        editForm.style.display = editForm.style.display === 'none' ? 'block' : 'none';
    });

    document.querySelectorAll('.pro-btn').forEach(button => {
        button.addEventListener('click', function () {
            // Remove active class from all buttons
            document.querySelectorAll('.pro-btn').forEach(btn => btn.classList.remove('active'));
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');

            // Add active class to the clicked button
            button.classList.add('active');
            // Show the corresponding tab content
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).style.display = 'block';
        });
    });
</script>

<?php include 'templates/footer.php'; ?>