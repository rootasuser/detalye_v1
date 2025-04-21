<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// === WEBSITE TITLE ===
include_once 'php/web-title.php';

// === DATABASE CONNECTION ===
include_once 'config/database.php';

$conn = dbConnection();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// === LOAD DEPENDENCIES ===
include_once 'php/dependencies.php';


/**
 * LOGIN AUTHENTICATION
 */
if (isset($_POST['submit_login'])) {

    $usernameOrEmail = mysqli_real_escape_string($conn, $_POST['usernameOrEmail']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE (username = '$usernameOrEmail' OR email = '$usernameOrEmail') AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        $_SESSION['toast_success'] = "Login successful!";
        $_SESSION['user_id'] = $user['id']; 
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['middle_initial'] = $user['middle_initial'];
        $_SESSION['contact_number'] = $user['contact_number'];
        $_SESSION['complete_address'] = $user['complete_address'];
        $_SESSION['password'] = $user['password'];
        $_SESSION['role'] = $user['role'];

    /**
     *  ROLE BASED REDIRECTION
     */
    if ($user['role'] == 'Admin') {
            header('Location: admin/index.php'); 
        } else {
            header('Location: customer/index.php'); 
        }
    } else {
        $_SESSION['toast_error'] = "Invalid username/email or password.";
    }
}

/**
 * CUSTOMER REGISTRTION @_@
 */
if (isset($_POST['submit_registration'])) {

    $usernameOrEmail = mysqli_real_escape_string($conn, $_POST['usernameOrEmail']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $middleInitial = mysqli_real_escape_string($conn, $_POST['middleInitial']);
    $contactNumber = mysqli_real_escape_string($conn, $_POST['contactNumber']);
    $completeAddress = mysqli_real_escape_string($conn, $_POST['completeAddress']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirmPassword']);

    if ($password !== $confirmPassword) {
        $_SESSION['toast_error'] = "Passwords do not match.";
    } else {

        $role = "Customer";

        $query = "SELECT * FROM users WHERE username = '$usernameOrEmail' OR email = '$usernameOrEmail'";
        $result = mysqli_query($conn, $query);

        
    if (mysqli_num_rows($result) > 0) {

        $_SESSION['toast_error'] = "Username or Email is already taken.";
        
    } else {
         
        $insertQuery = "INSERT INTO users (username, email, first_name, last_name, middle_initial, contact_number, complete_address, password, role) 
                            VALUES ('$usernameOrEmail', '$usernameOrEmail', '$firstName', '$lastName', '$middleInitial', '$contactNumber', '$completeAddress', '$password', '$role')";
    if (mysqli_query($conn, $insertQuery)) {
        $_SESSION['toast_success'] = "Registration successful!";
        } else {
                $_SESSION['toast_error'] = "An error occurred. Please try again.";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Barong Tagalog Details">
    <meta name="keywords" content="Barong Tagalog, Details, Authentic, Premium, Philippine Quality">
    <meta name="author" content="WinDev">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta name="google" content="notranslate">
    <meta name="theme-color" content="#ffffff">

    <!-- Favicon and Apple Touch Icons -->
    <link rel="icon" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="192x192" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="512x512" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="192x192" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="167x167" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="152x152" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="120x120" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="76x76" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="60x60" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="57x57" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="32x32" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="16x16" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="128x128" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="96x96" href="assets/images/detalye_logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="72x72" href="assets/images/detalye_logo.png" type="image/png">


    <link rel="stylesheet" href="assets/css/index.style.css" type="text/css" />

    <title><?php echo $web_title; ?></title>
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
    

<!-- Toast Notifications  Register/Login-->
<div aria-live="polite" aria-atomic="true" class="toast-container position-fixed top-0 right-0 p-3">
  <!-- Success Toast -->
  <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <strong class="mr-auto text-success">Success</strong>
      <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="toast-body">
      <?php echo $_SESSION['toast_success'] ?? ''; ?>
    </div>
  </div>

  <!-- Error Toast Register/Login -->
  <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <strong class="mr-auto text-danger">Error</strong>
      <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="toast-body">
      <?php echo $_SESSION['toast_error'] ?? ''; ?>
    </div>
  </div>
</div>



<!--- NAVBAR FOR TITLE AND LOGO ONLY -->
<nav class="navbar">
    <div class="container-fluid">
    <a class="navbar-brand" href="#">
    <img src="assets/images/detalye_logo.png" width="30" height="30" class="d-inline-block align-top rounded-circle" alt="">
    <?php echo $web_title; ?>
  </a>
    </div>
</nav>


<!--- NAVBAR FOR CONTACT AND SIGN IN -->
<nav class="navbar navbar-expand-lg navbar-second">
  <a class="navbar-brand navbar-text" href="#">Authentic & Premium Philippine Quality @ <?php echo $web_title; ?></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" 
  aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
  <span class="navbar-toggler-icon">
    <div class="navbar-menu"></div>
    <div class="navbar-menu"></div>
    <div class="navbar-menu"></div>
  </span>
</button>
<div class="collapse navbar-collapse justify-content-end" id="navbarNav">
  <ul class="navbar-nav align-items-center">
    <li class="nav-item">
      <span class="nav-link mb-0 text-white"><i class="bi bi-telephone"></i> +63 9207 - 432 - 421</span>
    </li>
    <li class="nav-item">
      <a href="#" class="btn btn-warning ml-3 border-0" data-toggle="modal" data-target="#portalModal">Sign In <i class="bi bi-box-arrow-in-right"></i> </a>
    </li>
  </ul>
</div>
</nav>


<!-- MODAL FOR SIGN IN / REGISTER -->
<div class="modal fade" id="portalModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="portalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="float-right">
        <button type="button" class="close float-right mr-2 font-weight-bolder text-dark" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body px-4 py-4">
        <!-- Login Form -->
        <div id="loginForm">
          <h5 class="text-center mb-4">Welcome Back!</h5>
          <form method="POST">
            <div class="form-group">
              <label>Username or Email</label>
              <input type="text" name="usernameOrEmail" class="form-control" placeholder="Enter username or email" required>
            </div>
            <div class="form-group">
              <label>Password</label>
              <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-warning btn-block" name="submit_login">Login</button>
            <p class="text-center mt-3">
              Not yet have an account? <a href="#" onclick="toggleForms()">Register</a>
            </p>
          </form>
        </div>

        <!-- Register Form -->
        <div id="registerForm" style="display: none;">
          <h5 class="text-center mb-4">Register</h5>
          <form method="POST">
          <div class="form-group">
              <label>Username or Email</label>
              <input type="text" name="usernameOrEmail" class="form-control" placeholder="Username or Email" required>
            </div>
            <div class="form-group">
              <label>First Name</label>
              <input type="text" name="firstName" class="form-control" placeholder="Enter first name" required>
            </div>
            <div class="form-group">
              <label>Last Name</label>
              <input type="text" name="lastName" class="form-control" placeholder="Enter last name" required>
            </div>
            <div class="form-group">
              <label>Middle Initial</label>
              <input type="text" name="middleInitial" class="form-control" maxlength="1" placeholder="M" required>
            </div>
            <div class="form-group">
              <label>Contact</label>
              <input type="text" name="contactNumber" class="form-control" placeholder="Enter contact number" required>
            </div>
            <div class="form-group">
              <label>Complete Address</label>
              <input type="text" name="completeAddress" class="form-control" placeholder="Enter address" required>
            </div>
            <div class="form-group">
              <label>Password</label>
              <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <div class="form-group">
              <label>Confirm Password</label>
              <input type="password" name="confirmPassword" class="form-control" placeholder="Confirm password" required>
            </div>
            <input type="hidden" name="role" class="form-control" value="<?php echo $role; ?>" required>
            <div class="form-group form-check">
              <input type="checkbox" name="termsCheck" value="Agree" class="form-check-input" id="termsCheck" required>
              <label class="form-check-label" for="termsCheck">I agree to the terms and conditions</label>
            </div>
            <div class="form-group form-check">
              <input type="checkbox" name="privacyCheck" value="Agree" class="form-check-input" id="privacyCheck" required>
              <label class="form-check-label" for="privacyCheck">I agree to the privacy policy</label>
            </div>
            
            <button type="submit" class="btn btn-success btn-block" name="submit_registration">Register</button>
            <p class="text-center mt-3">
              Already have an account? <a href="#" onclick="toggleForms()">Sign In</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>


<!--- NAVBR FOR CATEGORIES -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container d-flex flex-column align-items-center navbar-center-wrapper">

    <!-- Centered Nav -->
    <ul class="navbar-nav flex-row flex-wrap justify-content-center text-center">
      <li class="nav-item mx-2">
        <a class="nav-link font-weight-bolder text-dark" href="#">Home</a>
      </li>
      <li class="nav-item mx-2">
        <a class="nav-link font-weight-bolder text-dark" href="#">Features</a>
      </li>
      <li class="nav-item mx-2">
        <a class="nav-link font-weight-bolder text-dark" href="#">Pricing</a>
      </li>

      <!-- Collapsible Toggle -->
      <li class="nav-item mx-2">
        <button class="btn btn-link nav-link font-weight-bolder text-dark" type="button" id="dropdownToggleMen">
          Men &#9662;
        </button>
      </li>
      <li class="nav-item mx-2">
        <button class="btn btn-link nav-link font-weight-bolder text-dark" type="button" id="dropdownToggleWomen">
          Women &#9662;
        </button>
      </li>
      <li class="nav-item mx-2">
        <button class="btn btn-link nav-link font-weight-bolder text-dark" type="button" id="dropdownToggleKids">
          Kids &#9662;
        </button>
      </li>
    </ul>

    <!-- MEN Dropdown -->
    <div class="floating-dropdown" id="collapseDropdownMen" style="display: none;">
      <a class="dropdown-item" href="#">Budget Barongs</a>
      <a class="dropdown-item" href="#">White Barong Tagalog</a>
      <a class="dropdown-item" href="#">Black Barong Tagalogs</a>
      <a class="dropdown-item" href="#">Barong Coats / Barong Jackets</a>
      <a class="dropdown-item" href="#">Short Sleeve Barong</a>
      <a class="dropdown-item" href="#">Colored Modern Barong</a>
    </div>

    <!-- WOMEN Dropdown -->
    <div class="floating-dropdown" id="collapseDropdownWomen" style="display: none;">
      <a class="dropdown-item" href="#">Modern Filipiniana</a>
      <a class="dropdown-item" href="#">Bolero Filipiniana Sleeves</a>
      <a class="dropdown-item" href="#">Traditional Filipiniana</a>
      <a class="dropdown-item" href="#">Alampay & Wrap Around</a>
    </div>

    <!-- KIDS Dropdown -->
    <div class="floating-dropdown" id="collapseDropdownKids" style="display: none;">
      <a class="dropdown-item" href="#">Boys Barong Tagalog</a>
      <a class="dropdown-item" href="#">Girls Filipiniana</a>
    </div>

  </div>
</nav>


<!-- CAROUSEL SECTION TO SLIDESHOW THE BARONGS -->
<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel" data-interval="3000">
  <ol class="carousel-indicators">
    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
    <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
    <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
  </ol>
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="assets/images/slides_img/slide_1.jpg.jpg" class="d-block w-100 carousel-img" alt="...">
      <div class="carousel-caption">
        <h3 class="carousel-title font-weight-bolder">Barong Tagalog 1</h3>
        <p class="carousel-description font-weight-bolder">Elegant design for any occasion. Get yours today!</p>
        <a href="#shop-now" class="btn btn-warning font-weight-bolder"><i class="bi bi-cart-plus"></i> Shop Now</a>
      </div>
    </div>
    <div class="carousel-item">
      <img src="assets/images/slides_img/slide_2.jpg" class="d-block w-100 carousel-img" alt="...">
      <div class="carousel-caption">
        <h3 class="carousel-title font-weight-bolder">Barong Tagalog 2</h3>
        <p class="carousel-description font-weight-bolder">Classic style with modern fit. Shop now and look great!</p>
        <a href="#shop-now" class="btn btn-warning font-weight-bolder"><i class="bi bi-cart-plus"></i> Shop Now</a>
      </div>
    </div>
    <div class="carousel-item">
      <img src="assets/images/slides_img/men-large.jpg" class="d-block w-100 carousel-img" alt="...">
      <div class="carousel-caption">
        <h3 class="carousel-title font-weight-bolder">Barong Tagalog 3</h3>
        <p class="carousel-description font-weight-bolder">Traditional craftsmanship meets contemporary elegance.</p>
        <a href="#shop-now" class="btn btn-warning font-weight-bolder"><i class="bi bi-cart-plus"></i> Shop Now</a>
      </div>
    </div>
  </div>
  <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div>


<!-- DESCRIPTION SALE SECTION -->
<div class="barong-sale-section py-5">
  <div class="container">
    <h2 class="text-center mb-4">Barong Tagalog For Sale</h2>
    <p class="text-center"><span style="text-decoration: underline; font-weight: bold;"><?php echo $web_title; ?>® Official Store</span> - We sell handmade Barong Tagalog, Filipiniana, and other Philippine design clothing on our website. Browse through our big collection and place your order. We are a quality seller of Barong Tagalog, and we want you to really get good quality, <span style="text-decoration: underline; font-weight: bold;">100% brand new</span> products with an excellent experience. This is why we <span style="text-decoration: underline; font-weight: bold;">HANDMADE your products with love and care</span>, ensuring that you are satisfied. We also offer <span style="text-decoration: underline; font-weight: bold;">24/7 customer support</span> for your convenience.</p>
  </div>
</div>



<!-- MAIN CATEGORY: MEN -->
<section class="py-5">
  <div class="container">
    <h2 class="mb-4 font-weight-bold text-uppercase">Men's Collection</h2>
    <div class="row">
      <!-- Subcategory 1 -->
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
          <img src="assets/images/categories/men-barong.jpg" class="card-img-top" alt="Barong Tagalog">
          <div class="card-body text-center">
            <h5 class="card-title font-weight-bold">Barong Tagalog</h5>
            <p class="card-text">Classic, hand-embroidered Barong Tagalog for all occasions.</p>
            <a href="#" class="btn btn-outline-primary">Shop Now</a>
          </div>
        </div>
      </div>
      <!-- Subcategory 2 -->
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
          <img src="assets/images/categories/men-formal.jpg" class="card-img-top" alt="Men's Formal Wear">
          <div class="card-body text-center">
            <h5 class="card-title font-weight-bold">Formal Wear</h5>
            <p class="card-text">Modern Filipino formalwear with traditional craftsmanship.</p>
            <a href="#" class="btn btn-outline-primary">Shop Now</a>
          </div>
        </div>
      </div>
      <!-- Subcategory 3 -->
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
          <img src="assets/images/categories/men-accessories.jpg" class="card-img-top" alt="Accessories">
          <div class="card-body text-center">
            <h5 class="card-title font-weight-bold">Accessories</h5>
            <p class="card-text">Complete your look with traditional Filipino accessories.</p>
            <a href="#" class="btn btn-outline-primary">Shop Now</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Repeat for WOMEN -->
<section class="py-5 bg-light">
  <div class="container">
    <h2 class="mb-4 font-weight-bold text-uppercase">Women's Collection</h2>
    <div class="row">
      <!-- Subcategory: Filipiniana -->
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
          <img src="assets/images/categories/women-filipiniana.jpg" class="card-img-top" alt="Filipiniana">
          <div class="card-body text-center">
            <h5 class="card-title font-weight-bold">Filipiniana</h5>
            <p class="card-text">Elegant traditional outfits for modern Filipinas.</p>
            <a href="#" class="btn btn-outline-primary">Shop Now</a>
          </div>
        </div>
      </div>
      <!-- Add more subcategories as needed -->
    </div>
  </div>
</section>

<!-- Repeat for KIDS -->
<section class="py-5">
  <div class="container">
    <h2 class="mb-4 font-weight-bold text-uppercase">Kids Collection</h2>
    <div class="row">
      <!-- Subcategory: Kids Barong -->
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm h-100">
          <img src="assets/images/categories/kids-barong.jpg" class="card-img-top" alt="Kids Barong">
          <div class="card-body text-center">
            <h5 class="card-title font-weight-bold">Kids Barong</h5>
            <p class="card-text">Adorable and elegant Barong for boys of all ages.</p>
            <a href="#" class="btn btn-outline-primary">Shop Now</a>
          </div>
        </div>
      </div>
      <!-- Add more subcategories if you want -->
    </div>
  </div>
</section>


<!-- CUSTOMER FEEDBACK SECTION -->
<section class="py-5 bg-light">
  <div class="container">
    <h2 class="text-center mb-5 font-weight-bold text-uppercase">Customer Feedbacks</h2>

    <div id="feedbackCarousel" class="carousel slide" data-ride="carousel" data-interval="5000">
      <div class="carousel-inner">

        <!-- Feedback 1 -->
        <div class="carousel-item active">
          <div class="row justify-content-center">
            <div class="col-md-8 text-center">
              <img src="assets/images/testimonials/user1.jpg" class="rounded-circle mb-3" width="100" height="100" alt="User 1">
              <h5 class="font-weight-bold">Juan Dela Cruz</h5>
              <small class="text-muted">March 10, 2025 | 2:30 PM</small>
              <div class="mb-2">
                <span class="text-warning">&#9733;&#9733;&#9733;&#9733;&#9734;</span>
              </div>
              <p class="lead">"Absolutely love the quality of the Barong I ordered. The stitching is amazing and it fits perfectly. Highly recommended!"</p>
            </div>
          </div>
        </div>

        <!-- Feedback 2 -->
        <div class="carousel-item">
          <div class="row justify-content-center">
            <div class="col-md-8 text-center">
              <img src="assets/images/testimonials/user2.jpg" class="rounded-circle mb-3" width="100" height="100" alt="User 2">
              <h5 class="font-weight-bold">Maria Santos</h5>
              <small class="text-muted">March 12, 2025 | 10:15 AM</small>
              <div class="mb-2">
                <span class="text-warning">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
              </div>
              <p class="lead">"Amazing craftsmanship! The Filipiniana dress I bought is beautiful. Customer service was great too!"</p>
            </div>
          </div>
        </div>

        <!-- Feedback 3 -->
        <div class="carousel-item">
          <div class="row justify-content-center">
            <div class="col-md-8 text-center">
              <img src="assets/images/testimonials/user3.jpg" class="rounded-circle mb-3" width="100" height="100" alt="User 3">
              <h5 class="font-weight-bold">Pedro Gonzales</h5>
              <small class="text-muted">April 5, 2025 | 6:45 PM</small>
              <div class="mb-2">
                <span class="text-warning">&#9733;&#9733;&#9733;&#9733;&#9734;</span>
              </div>
              <p class="lead">"The attention to detail in the Barong is outstanding. It arrived on time and looks exactly like the pictures!"</p>
            </div>
          </div>
        </div>

      </div>

      <!-- Controls -->
      <a class="carousel-control-prev" href="#feedbackCarousel" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#feedbackCarousel" role="button" data-slide="next">
        <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </a>
    </div>
  </div>
</section>


<!--- FOOTER SECTION -->
<footer class="bg-dark text-light pt-5 pb-4">
  <div class="container text-md-left">
    <div class="row text-md-left">

      <!-- Logo & About -->
      <div class="col-md-3 col-lg-3 col-xl-3 mx-auto mt-3">
        <h5 class="text-uppercase mb-4 font-weight-bold text-warning"><?php echo $web_title; ?></h5>
        <p>
          Handmade Barong Tagalog, Filipiniana, and Philippine designs crafted with love and detail. Proudly Filipino.
        </p>
      </div>

      <!-- Quick Links -->
      <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mt-3">
        <h5 class="text-uppercase mb-4 font-weight-bold text-warning">Quick Links</h5>
        <p><a href="#" class="text-light" style="text-decoration: none;">Home</a></p>
        <p><a href="#" class="text-light" style="text-decoration: none;">Shop</a></p>
        <p><a href="#" class="text-light" style="text-decoration: none;">About</a></p>
        <p><a href="#" class="text-light" style="text-decoration: none;">Contact</a></p>
      </div>

      <!-- Contact -->
      <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mt-3">
        <h5 class="text-uppercase mb-4 font-weight-bold text-warning">Contact</h5>
        <p><i class="fas fa-home mr-3"></i> Manila, Philippines</p>
        <p><i class="fas fa-envelope mr-3"></i> support@detalyebarong.com</p>
        <p><i class="fas fa-phone mr-3"></i> +63 912 345 6789</p>
        <p><i class="fas fa-clock mr-3"></i> 24/7 Support</p>
      </div>

      <!-- Newsletter & Social -->
      <div class="col-md-3 col-lg-4 col-xl-3 mx-auto mt-3">
        <h5 class="text-uppercase mb-4 font-weight-bold text-warning">Stay Connected</h5>
        <form>
          <div class="input-group mb-3">
            <input type="email" class="form-control" placeholder="Email address" aria-label="Email address">
            <div class="input-group-append">
              <button class="btn btn-warning" type="submit">Subscribe</button>
            </div>
          </div>
        </form>
        <div>
        <!--- LINK FB URL HERE -->
          <a href="#" class="text-light me-4"><i class="fab fa-facebook fa-lg"></i></a>
           <!--- LINK IG URL HERE -->
          <a href="#" class="text-light me-4"><i class="fab fa-instagram fa-lg"></i></a>
           <!--- LINK TWITTER URL HERE -->
          <a href="#" class="text-light me-4"><i class="fab fa-twitter fa-lg"></i></a>
           <!--- LINK YT URL HERE -->
          <a href="#" class="text-light"><i class="fab fa-youtube fa-lg"></i></a>
        </div>
      </div>

    </div>

    <hr class="mb-4">

    <div class="row align-items-center">
      <div class="col-md-7 col-lg-8">
        <p>© 2025 <?php echo $web_title; ?>®. All rights reserved.</p>
      </div>
      <div class="col-md-5 col-lg-4">
        <p class="text-end">Designed with ❤️ in PH</p>
      </div>
    </div>
  </div>
</footer>








    <!--- DROPDOWN THIRD NAVBAR FUNCTIONALITY -->
    <script src="assets/js/index.dropdown.thirdnav.js"></script>
    <!--- FORM SWITCHING FUNCTIONALITY -->
    <script src="assets/js/index.toggle.form.switch.js"></script>

    <script>

        // TOAST NOTIFICATION FUNCTIONALITY
        $(document).ready(function() {
            <?php if (isset($_SESSION['toast_success'])): ?>
            $('#successToast').toast('show');
            <?php unset($_SESSION['toast_success']); ?>
            <?php endif; ?>
    
            <?php if (isset($_SESSION['toast_error'])): ?>
            $('#errorToast').toast('show');
            <?php unset($_SESSION['toast_error']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>