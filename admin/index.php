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

    <!-- Custom Style Css -->
    <link rel="stylesheet" href="../assets/css/index.admin.style.css">
    
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

</head>
<body class="bg-white">

<!-- SIDEBAR -->
<div class="bg-dark text-white position-fixed vh-100 sidebar d-flex flex-column p-3" id="sidebar">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div class="fw-bold fs-4">
    <div class="navbar-logo-title d-flex align-items-center gap-2">
        <!--- DETALYE LOGO:: CHANGE IF WANT -->
        <img src="../assets/images/detalye_logo.png" alt="Logo" class="rounded-circle mx-2">
                                    <!--- WEB TITLE CONVERTED INTO UPPERCASE -->
        <span class="fw-bold fs-5"><?php echo strtoupper($web_title); ?></span>
      </div>
    </div>
  </div>
  <ul class="nav nav-pills flex-column gap-2">
  <li>
    <a href="?page=home" class="nav-link text-white" data-bs-toggle="tooltip" data-bs-placement="right" title="Home">
      <i class="bi bi-speedometer2"></i>
      <span class="link-text">Dashboard</span>
    </a>
  </li>
  <li>
  <a href="?page=products" class="nav-link text-white" data-bs-toggle="tooltip" data-bs-placement="right" title="Products">
    <i class="bi bi-box-seam"></i>
    <span class="link-text">Products</span>
  </a>
</li>

<li>
  <a href="?page=orders" class="nav-link text-white" data-bs-toggle="tooltip" data-bs-placement="right" title="Orders">
    <i class="bi bi-cart4"></i>
    <span class="link-text">Orders</span>
  </a>
</li>

<li>
  <a href="?page=sales" class="nav-link text-white" data-bs-toggle="tooltip" data-bs-placement="right" title="Sales">
    <i class="bi bi-cash-stack"></i>
    <span class="link-text">Sales</span>
  </a>
</li>

  <li>
    <a href="?page=account" class="nav-link text-white" data-bs-toggle="tooltip" data-bs-placement="right" title="Account">
      <i class="bi bi-person-fill"></i>
      <span class="link-text">Account</span>
    </a>
  </li>
</ul>

</div>


<style>
  #navbarUser {
    margin-right: 250px;
  }
  @media (max-width: 768px) {
    #navbarUser {
      margin-right: 0;
    }
  }
</style>

<!-- MAIN CONTENT  -->
<div class="content" id="mainContent">

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg bg-light shadow sticky-top">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <button class="toggle-btn" id="toggleSidebar"><i class="bi bi-list"></i></button>
      
      <div class="d-flex align-items-center gap-3" id="navbarUser">
        <!--- DIPLAY USERERNAME OF ADMIN -->
        <div class="fw-semibold mx-3"><?php echo $user_data['username'];?></div>
        <!--- LOGOUT ADMIN -->
        <a href="logout.php" class="text-decoration-none text-dark">
        <i class="bi bi-box-arrow-right"></i>
        </a>
      </div>
    </div>
  </nav>


  <!-- PAGE ROUTE CONTENT -->
  <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <?php
      $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'home';
      $allowedPages = ['home', 'products', 'orders', 'sales', 'account', '404'];
      if (!in_array($page, $allowedPages, true)) { $page = '404'; }
      $viewFile = __DIR__ . '/templates/' . $page . '.php';
      if (is_readable($viewFile)) {
          include $viewFile;
      } else {
          http_response_code(404);
          echo '<h2 class="text-center">404 - Page Not Found</h2>';
      }
    ?>
  </main>
  
</div>


<script src="../assets/js/index.admin.style.js"></script>

</body>
</html>
