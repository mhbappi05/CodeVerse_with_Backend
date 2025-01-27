<?php
require_once 'admin-auth.php';
checkAdminAuth();
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



// Fetch data dynamically
$totalUsers = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$totalDiscussions = $conn->query("SELECT COUNT(*) AS count FROM discussions")->fetch_assoc()['count'];
$totalQuestions = $conn->query("SELECT COUNT(*) AS count FROM questions")->fetch_assoc()['count'];
$totalTeams = $conn->query("SELECT COUNT(*) AS count FROM teams")->fetch_assoc()['count'];
$totalJobs = $conn->query("SELECT COUNT(*) AS count FROM jobs")->fetch_assoc()['count'];

// Fetch table data
$discussions = $conn->query("
    SELECT d.*, u.name as user_name 
    FROM discussions d 
    LEFT JOIN users u ON d.created_by = u.id
");

$questions = $conn->query("
    SELECT q.*, u.name as user_name 
    FROM questions q 
    LEFT JOIN users u ON q.created_by = u.id
");

$teams = $conn->query("
    SELECT t.*, u.name as user_name 
    FROM teams t 
    LEFT JOIN users u ON t.created_by = u.id
");

$jobs = $conn->query("
    SELECT j.*, u.name as user_name 
    FROM jobs j 
    LEFT JOIN users u ON j.created_by = u.id
");
$users = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="../css/styles.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --background-color: #f5f6fa;
            --card-color: #ffffff;
            --text-color: #2c3e50;
            --text-light: #ffffff;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .navbar .logo {
            color: var(--text-light);
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-links li a {
            color: var(--text-light);
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .nav-links li a:hover {
            background-color: var(--secondary-color);
            border-radius: 4px;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--text-light);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .admin-card {
            background-color: var(--card-color);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .admin-card:hover {
            transform: translateY(-5px);
        }

        .admin-card h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .admin-card p {
            color: var(--secondary-color);
            font-size: 2rem;
            font-weight: bold;
        }

        .admin-table {
            background-color: var(--card-color);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .admin-table th {
            background-color: var(--primary-color);
            color: var(--text-light);
            padding: 1rem;
        }

        .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .delete-btn {
            background-color: var(--accent-color);
            color: var(--text-light);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .footer {
            background-color: var(--primary-color);
            color: var(--text-light);
            padding: 1rem 0;
            margin-top: 2rem;
        }

        /* Responsive design improvements */
        @media (max-width: 768px) {
            .admin-sections {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .admin-sections {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <a href="#" class="logo">CollabConnect Admin</a>
            <ul class="nav-links">
                <li><a href="#dashboard">Dashboard</a></li>
                <li><a href="#dismoderation">Discussions Moderation</a></li>
                <li><a href="#qusmoderation">Questions Moderation</a></li>
                <li><a href="#teammoderation">Team Moderation</a></li>
                <li><a href="#jobmoderation">Job Moderation</a></li>
                <li><a href="#user-management">User Management</a></li>
                <li><a href="admin-logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </div>
    </nav>

    <header class="dashboard-header">
        <div class="container">
            <h1>Admin Dashboard</h1>
            <p>Welcome, Admin! Manage users, moderate content, and oversee platform activity.</p>
        </div>
    </header>
    <br><br>

    <section class="dashboard-content">
        <div class="container">
            <div class="admin-sections">
                <div class="admin-card">
                    <h3>Total Users</h3>
                    <p><?= $totalUsers; ?></p>
                </div>
                <div class="admin-card">
                    <h3>Total Discussions</h3>
                    <p><?= $totalDiscussions; ?></p>
                </div>
                <div class="admin-card">
                    <h3>Total Questions</h3>
                    <p><?= $totalQuestions; ?></p>
                </div>
                <div class="admin-card">
                    <h3>Total Teams</h3>
                    <p><?= $totalTeams; ?></p>
                </div>
                <div class="admin-card">
                    <h3>Total Jobs</h3>
                    <p><?= $totalJobs; ?></p>
                </div>
            </div>


            <hr>

            <div id="dismoderation">
                <h2>Discussions Moderation</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $discussions->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $row['title']; ?></td>
                                <td><?= $row['description']; ?></td>
                                <td><?= isset($row['user_name']) ? htmlspecialchars($row['user_name']) : 'Unknown User'; ?>
                                </td>
                                <td>
                                    <button class="delete-btn" data-id="<?= $row['id']; ?>"
                                        data-table="discussions">Delete</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Similar tables for Questions, Teams, Jobs, and User Management -->
            <hr>

            <div id="qusmoderation">
                <h2>Questions Moderation</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $questions->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $row['title']; ?></td>
                                <td><?= $row['description']; ?></td>
                                <td><?= isset($row['user_name']) ? htmlspecialchars($row['user_name']) : 'Unknown User'; ?>
                                </td>
                                <td>
                                    <button class="delete-btn" data-id="<?= $row['id']; ?>"
                                        data-table="questions">Delete</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <hr>

            <div id="teammoderation">
                <h2>Teams Moderation</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Team Name</th>
                            <th>Description</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $teams->fetch_assoc()) { ?>
                            <tr>
                                <td><?= $row['name']; ?></td>
                                <td><?= $row['description']; ?></td>
                                <td><?= isset($row['user_name']) ? htmlspecialchars($row['user_name']) : 'Unknown User'; ?>
                                </td>
                                <td>
                                    <button class="delete-btn" data-id="<?= $row['id']; ?>"
                                        data-table="teams">Delete</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <hr>

            <div id="jobmoderation">
                <h2>Job Applications Moderation</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Applicant Name</th>
                            <th>Applicant Email</th>
                            <th>Resume</th>
                            <th>Message</th>
                            <th>Applied On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $job_applications = $conn->query("SELECT * FROM job_applications");
                        if ($job_applications && $job_applications->num_rows > 0) {
                            while ($row = $job_applications->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?= isset($row['job_title']) ? htmlspecialchars($row['job_title']) : ''; ?></td>
                                    <td><?= isset($row['applicant_name']) ? htmlspecialchars($row['applicant_name']) : ''; ?>
                                    </td>
                                    <td><?= isset($row['applicant_email']) ? htmlspecialchars($row['applicant_email']) : ''; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($row['resume']) && !empty($row['resume'])): ?>
                                            <a href="../uploads/resumes/<?= htmlspecialchars($row['resume']) ?>"
                                                target="_blank">View Resume</a>
                                        <?php else: ?>
                                            No Resume
                                        <?php endif; ?>
                                    </td>
                                    <td><?= isset($row['message']) ? htmlspecialchars($row['message']) : ''; ?></td>
                                    <td><?= isset($row['created_at']) ? date('Y-m-d H:i', strtotime($row['created_at'])) : ''; ?>
                                    </td>
                                    <td>
                                        <button class="delete-btn" data-id="<?= $row['id']; ?>"
                                            data-table="job_applications">Delete</button>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="7">No job applications found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <hr>

            <div id="user-management">
                <h2>User Management</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Skills</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $users->fetch_assoc()) { ?>
                            <tr>
                                <td><?= isset($row['name']) ? $row['name'] : ''; ?></td>
                                <td><?= isset($row['email']) ? $row['email'] : ''; ?></td>
                                <td><?= isset($row['skills']) ? $row['skills'] : ''; ?></td>
                                <td>
                                    <button class="delete-btn" data-id="<?= $row['id']; ?>"
                                        data-table="users">Delete</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 CollabConnect Admin. All rights reserved.</p>
        </div>
    </footer>


    <script>
        $(document).ready(function () {
            $('.delete-btn').on('click', function () {
                const button = $(this);
                const table = button.data('table');

                let confirmMessage;
                switch (table) {
                    case 'teams':
                        confirmMessage = 'Are you sure you want to delete this team? This will also delete all team member associations.';
                        break;
                    case 'questions':
                        confirmMessage = 'Are you sure you want to delete this question? This will also delete all answers, votes, and related content.';
                        break;
                    default:
                        confirmMessage = 'Are you sure you want to delete this record?';
                }

                if (!confirm(confirmMessage)) {
                    return;
                }

                const id = button.data('id');

                // Disable the button while processing
                button.prop('disabled', true);

                $.ajax({
                    url: 'delete-handler.php',
                    type: 'POST',
                    data: {
                        id: id,
                        table: table
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            button.closest('tr').fadeOut(400, function () {
                                $(this).remove();
                            });
                            alert('Record deleted successfully');
                        } else {
                            alert('Error: ' + response.message);
                            button.prop('disabled', false);
                        }
                    },
                    error: function (xhr, status, error) {
                        alert('Error occurred while deleting the record: ' + error);
                        console.error('Delete request failed:', xhr.responseText);
                        button.prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>