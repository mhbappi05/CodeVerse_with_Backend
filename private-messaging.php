<?php
include 'templates/header.php';
include 'db.php';
?>
<script>
    const currentUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
</script>
<script src="js/messaging.js"></script>



<!--Header-->
<header class="discussion-header">
    <div class="container">
        <h1>Messages</h1>
    </div>
</header>

<!-- Private Messaging Content -->
<main class="messaging-page">
    <div class="container">
        <div class="messaging-container">
            <div class="chat-sidebar">
                <ul class="contact-list">
                    <?php
                    $query = "SELECT id, name FROM users"; // Fetch users from the database
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        echo "<li class='contact' data-receiver-id='{$row['id']}'>{$row['name']}</li>";
                    }
                    ?>
                </ul>
            </div>


            <!-- Chat Area -->
            <div class="chat-area" data-receiver-id="123"> <!-- Replace '1' with the default receiver ID -->
                <!-- Example of HTML structure for the chat -->
                <div class="chat-header">
                    <h2 data-id="RECEIVER_USER_ID">Receiver Name</h2>
                </div>
                <div class="chat-messages"></div>
                <div class="chat-input">
                    <textarea></textarea>
                    <button class="btn-primary">Send</button>
                </div>
            </div>

        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>