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
    <title>Privacy Policy - Detalye Barong</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 4.6 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background-color: #f9f9f9;
        }
        .section-title {
            font-weight: 600;
            font-size: 28px;
            margin-bottom: 30px;
        }
        .policy-content p {
            text-align: justify;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="card shadow border-0">
        <div class="card-body policy-content">
            <h2 class="text-center section-title">Privacy Policy</h2>

            <p><strong>Effective Date:</strong> April 24, 2025</p>

            <p>
                At <strong>Detalye Barong</strong>, your privacy is important to us. This Privacy Policy outlines how we collect, use, disclose, and safeguard your information when you visit our website or use our services.
            </p>

            <h5 class="mt-4">1. Information We Collect</h5>
            <p>
                We may collect personal information such as your name, email address, contact number, and billing details when you create an account, place an order, or contact us for support.
            </p>

            <h5 class="mt-4">2. How We Use Your Information</h5>
            <p>
                The information we collect is used to:
                <ul>
                    <li>Process transactions and deliver your orders</li>
                    <li>Improve our website and services</li>
                    <li>Send order confirmations and service updates</li>
                    <li>Respond to your inquiries and customer service requests</li>
                </ul>
            </p>

            <h5 class="mt-4">3. Data Security</h5>
            <p>
                We implement industry-standard security measures to protect your personal data. However, no online transmission is 100% secure. We encourage users to keep their account information confidential.
            </p>

            <h5 class="mt-4">4. Sharing of Information</h5>
            <p>
                We do not sell, rent, or trade your personal data to third parties. We may share data with service providers who support our operations (e.g., payment processors) under strict confidentiality agreements.
            </p>

            <h5 class="mt-4">5. Cookies and Tracking</h5>
            <p>
                Detalye Barong uses cookies to enhance user experience and analyze website traffic. You can choose to disable cookies through your browser settings.
            </p>

            <h5 class="mt-4">6. Your Rights</h5>
            <p>
                You have the right to access, update, or delete your personal data. To make such requests, contact us through the details provided below.
            </p>

            <h5 class="mt-4">7. Changes to This Policy</h5>
            <p>
                We may update our privacy policy from time to time. Changes will be posted on this page with an updated effective date.
            </p>

            <h5 class="mt-4">8. Contact Us</h5>
            <p>
                If you have any questions about this Privacy Policy, please contact us at:
                <br>Email: <a href="mailto:Detalye.Barong@gmail.com">Detalye.Barong@gmail.com</a>
                <br>Phone: 09177741968
            </p>

            <hr>
            <p class="text-muted text-center">© <?= date('Y') ?> Detalye Barong. All rights reserved.</p>
        </div>
    </div>
</div>

</body>
</html>
