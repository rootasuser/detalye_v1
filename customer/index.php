<?php
/**
 * DEPENDENCIES STORED:: CDNLINKS
 */
include_once '../php/dependencies.php';

/**
 * WEB TAB TITLE STORED
 */
include_once '../php/web-title.php';



if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$conn = dbConnection();

if (!$conn) {
    die("Conn failed: " . mysqli_connect_error());
}

/**
 * * CHECK IF USER IS LOGGED IN
 */
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $web_title; ?></title>

    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="../assets/css/index.customer.style.css">

     <!-- Bootstrap CSS -->
     <link rel="stylesheet" href="<?= $bootstrap_dependencies['bootstrap_css'] ?>">

    <!-- BS Icon -->
    <link rel="stylesheet" href="<?= $bootstrap_dependencies['bootstrap_icon'] ?>">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= $bootstrap_dependencies['fontawesome'] ?>">

    <!-- jQuery -->
    <script src="<?= $bootstrap_dependencies['jquery'] ?>"></script>

    <!-- jQuery Slim -->
    <script src="<?= $bootstrap_dependencies['jquery_slim'] ?>"></script>

    <!-- Bootstrap JS -->
    <script src="<?= $bootstrap_dependencies['bootstrap_js'] ?>"></script>
</head>
<body>
    
  <!-- NAVBAR FOR ICONS & COLLAPSED DROPDOWN USER ICON -->   
  <nav class="navbar navbar-light bg-white">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fab fa-facebook-f social-icon"></i>
                <i class="fab fa-twitter social-icon"></i>
                <i class="fab fa-instagram social-icon"></i>
            </a>
            <div class="ml-auto">
                <div class="dropdown">
                    <a class="nav-link" href="#" id="userDropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item text-center" href="#">My Account</a>
                        <div class="dropdown-divider"></div>
                        <div class="btn-container">
                        <a class="dropdown-item btn btn-sm btn-danger" id="btn-danger" href="#">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
  

    <!-- NAVBAR USER ICON DROPDOWN FUNCTIONALITY -->
     <script src="../assets/js/index.customer.navdropdown.js"></script>
     
</body>
</html>
