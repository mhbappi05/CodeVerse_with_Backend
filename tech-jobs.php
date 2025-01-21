<?php
include 'db.php'; // Ensure db.php contains the connection to your database
include 'templates/header.php';

// Get filter values from GET request (search term, role, and location)
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : 'all';
$location = isset($_GET['location']) ? $_GET['location'] : 'all';

// Start building the SQL query
$query = "SELECT * FROM jobs WHERE 1"; // Base query

// Apply filters if they are set
if (!empty($search)) {
    $query .= " AND (title LIKE '%" . $conn->real_escape_string($search) . "%' OR company LIKE '%" . $conn->real_escape_string($search) . "%')";
}

if ($role !== 'all') {
    $query .= " AND role = '" . $conn->real_escape_string($role) . "'";
}

if ($location !== 'all') {
    $query .= " AND location = '" . $conn->real_escape_string($location) . "'";
}

$result = $conn->query($query);
?>

<!--Header-->
<header class="discussion-header">
    <div class="container">
        <h1>Find Your Next Tech Job</h1>
    </div>
</header>

<!-- Tech Jobs Content -->
<main class="tech-jobs">
    <div class="container">
        <!-- Add GET method to the form for submitting filters -->
        <form method="GET" action="" class="filtersJob">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search by job title or company..." class="search-bar">
            <select name="role">
                <option value="all" <?php echo $role === 'all' ? 'selected' : ''; ?>>All Roles</option>
                <option value="developer" <?php echo $role === 'developer' ? 'selected' : ''; ?>>Developer</option>
                <option value="designer" <?php echo $role === 'designer' ? 'selected' : ''; ?>>Designer</option>
                <option value="manager" <?php echo $role === 'manager' ? 'selected' : ''; ?>>Manager</option>
            </select>
            <select name="location">
                <option value="all" <?php echo $location === 'all' ? 'selected' : ''; ?>>Location</option>
                <option value="remote" <?php echo $location === 'remote' ? 'selected' : ''; ?>>Remote</option>
                <option value="onsite" <?php echo $location === 'onsite' ? 'selected' : ''; ?>>On-Site</option>
            </select>
            <button type="submit" class="button">Apply Filters</button>
        </form>


        <!-- Job Listings -->
        <div class="job-listings">
            <?php
            // Dynamic Job Listings from Database
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="job-card">';
                    echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
                    echo '<p>Company: ' . htmlspecialchars($row['company']) . '</p>';
                    echo '<p>Location: ' . htmlspecialchars($row['location']) . '</p>';
                    echo '<p>Skills: ' . htmlspecialchars($row['skills']) . '</p>';
                    // Corrected Apply button HTML with PHP inside
                    echo '<button class="button">
                <a href="tech-jobs-application.php?job_id=' . htmlspecialchars($row['id']) . '" style="text-decoration: none; color: white;">Apply</a>
            </button>';
                    echo '</div>';
                }
            } else {
                echo '<p>No job listings found.</p>';
            }
            ?>
        </div>

    </div>
</main>

<?php include 'templates/footer.php'; ?>