<?php
include_once './../php/dependencies.php';
include_once './../php/web-title.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once './../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ./../index.php');
    exit();
}

$conn = dbConnection();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
} else {
    echo "User not found.";
    exit();
}

$success_message = $error_message = "";

// Handle form submission
if (isset($_POST['send'])) {
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $insert = "INSERT INTO contact_messages (user_id, subject, message, date_sent)
               VALUES ('$user_id', '$subject', '$message', NOW())";

    if (mysqli_query($conn, $insert)) {
        $success_message = "Your message has been sent successfully!";
    } else {
        $error_message = "Failed to send your message. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .contact-card {
            max-width: 600px;
            margin: auto;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="card shadow contact-card border-0">
        <div class="card-body">
            <h3 class="text-center mb-4">Contact Us</h3>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" value="<?= htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user_data['email']) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="subject" name="subject" required>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>

                <div class="d-grid">
                    <button type="submit" name="send" class="btn btn-primary btn-lg">
                        <i class="bi bi-send-fill me-1"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


</body>
</html>

