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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 4.6 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="card shadow border-0 mb-5">
        <div class="card-body">
            <h2 class="text-center section-title">About Us</h2>
            <p class="lead text-center">
                Welcome to <strong><?php echo $web_title; ?></strong>! We're a platform dedicated to making your digital experience smooth, secure, and enjoyable.
            </p>
            <hr>
            <h4 class="mt-4">Our Mission</h4>
            <p>
                Our mission is to provide top-notch services to our users by combining cutting-edge technology with user-friendly design. Whether you're here to explore, connect, or learn, we're here to help you every step of the way.
            </p>

            <h4 class="mt-4">Why Choose Us?</h4>
            <ul>
                <li>✔ Secure and seamless user experience</li>
                <li>✔ Dedicated support and ongoing improvements</li>
                <li>✔ Community-driven with your needs in mind</li>
            </ul>
        </div>
    </div>
</div>

</body>
</html>
