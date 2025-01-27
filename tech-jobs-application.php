<?php
include 'db.php';
include 'templates/header.php';

// Get the job_id from the URL
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

// Fetch the job title based on job_id
$job_title = '';
if ($job_id > 0) {
    $stmt = $conn->prepare("SELECT title FROM jobs WHERE id = ?");
    $stmt->bind_param('i', $job_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $job_title = htmlspecialchars($row['title']); // Job title for pre-filling the form
    } else {
        die("Job not found.");
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_title = $_POST['job_title'];
    $applicant_name = $_POST['applicant_name'];
    $applicant_email = $_POST['applicant_email'];
    $message = $_POST['message'];

    // Handle file upload
    $resume = $_FILES['resume'];
    $resume_name = time() . '_' . $resume['name']; // Unique filename
    $upload_dir = 'uploads/resumes/';
    $upload_path = $upload_dir . $resume_name;

    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($resume['tmp_name'], $upload_path)) {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO job_applications (job_title, applicant_name, applicant_email, resume, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('sssss', $job_title, $applicant_name, $applicant_email, $resume_name, $message);

        if ($stmt->execute()) {
            echo "<script>alert('Application submitted successfully!'); window.location.href='tech-jobs.php';</script>";
        } else {
            echo "<script>alert('Error submitting application. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Error uploading resume. Please try again.');</script>";
    }
}
?>

<!-- Header -->
<header class="discussion-header">
    <div class="container">
        <h1>Job Application</h1>
        <p>Please fill out the form below to apply for the job.</p>
    </div>
</header>

<!-- Job Application Form -->
<main class="question-container">
    <div class="create-team-container">
        <form id="job-application-form" method="POST"
            action="tech-jobs-application.php?job_id=<?php echo htmlspecialchars($job_id); ?>"
            enctype="multipart/form-data">
            <!-- Job Title -->
            <div class="form-group">
                <label for="job-title">Job Title:</label>
                <!-- Pre-fill the Job Title field with the value from the database -->
                <input type="text" id="job-title" name="job_title" value="<?php echo $job_title; ?>"
                    placeholder="Enter the Job Title" required readonly>
            </div>

            <!-- Applicant Name -->
            <div class="form-group">
                <label for="applicant-name">Your Name:</label>
                <input type="text" id="applicant-name" name="applicant_name" placeholder="Enter your name" required>
            </div>

            <!-- Applicant Email -->
            <div class="form-group">
                <label for="applicant-email">Your Email:</label>
                <input type="email" id="applicant-email" name="applicant_email" placeholder="Enter your email" required>
            </div>

            <!-- Upload Resume -->
            <div class="form-group">
                <label for="resume">Upload Resume:</label>
                <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
            </div>

            <!-- Message to Employer -->
            <div class="form-group">
                <label for="message">Message to Employer:</label>
                <textarea id="message" name="message" rows="5" placeholder="Write a message..." required></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Submit Application</button>
            <a href="tech-jobs.php" style="text-decoration: none; color: black;">Go back to the Tech Job page</a>
        </form>
    </div>
</main>