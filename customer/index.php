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
 <!-- Google Font for logo text -->
 <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
 <style>
        .navbar-brand {
            font-family: 'Pacifico', cursive, sans-serif;
            color: #ff5945 !important;
            font-size: 3.8rem;
        }
        
        .search-button {
            background-color: #ff5945;
            border-color: #ff5945;
        }
        
        .search-button:hover {
            background-color: #e84c3d;
            border-color: #e84c3d;
        }
        
        .search-form {
            width: 100%;
            max-width: 450px;
        }
        
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.5rem;
            }
            
            .search-form {
                margin-top: 10px;
                max-width: 100%;
            }
        }
        .social-icon {
            color: #000;
        }

        .dropdown-toggle::after {
        margin-left: 10px;
    }
    
    .nav-link {
        color: white !important;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    @media (min-width: 992px) {
        .navbar-nav .nav-item {
            min-width: 120px;
            text-align: center;
        }
    }
    </style>
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
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-user text-dark rounded"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item text-center" href="#">My Account</a>
                    <div class="dropdown-divider"></div>
                    <div class="btn-container">
                        <a class="dropdown-item btn btn-sm btn-danger" style="background-color: red; color: #fff; text-align: center;" href="#">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
    
<nav class="navbar navbar-expand-lg navbar-light bg-white py-3">
    <div class="container">
        <!-- Logo/Brand -->
        <a class="navbar-brand" href="#" style="font-size: 40px;">Detalye Barong</a>
        
        <!-- Toggler for mobile -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" 
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navbar content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Pushing search to the right -->
            <ul class="navbar-nav mr-auto">
                <!-- You can add navigation items here if needed -->
            </ul>
            
            <!-- Search form -->
            <form class="form-inline my-2 my-lg-0 search-form">
                <div class="input-group w-100">
                    <input type="text" class="form-control" placeholder="Search product here..." aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-primary search-button" type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</nav>

<!-- Secondary Navigation Bar with Nested Categories -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-0">
    <div class="container-fluid px-0">  
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link px-4" href="?page=home">HOME</a>
            </li>
            <li class="nav-item">
                <a class="nav-link px-4" href="?page=shop">SHOP</a>
            </li>
            <li class="nav-item">
                <a class="nav-link px-4" href="?page=contact">CONTACT</a>
            </li>
        </ul>
    </div>
</nav>

<!-- Display the products -->

<div class="container-fluid mt-2">
    <?php
        $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'home';
        $allowedPages = ['home', 'shop', 'contact', 'account'];
        if (!in_array($page, $allowedPages, true)) { $page = '404'; }
        $viewFile = __DIR__ . '/templates/' . $page . '.php';
        if (is_readable($viewFile)) { include $viewFile; } else { http_response_code(404); echo '<h2>404 - Page Not Found</h2>'; }
    ?>        
</div>


<!-- Footer -->
<?php include('templates/footer.php'); ?>

    <!-- NAVBAR USER ICON DROPDOWN FUNCTIONALITY -->
    <script>
        document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
          // clear all
          document.querySelectorAll('.nav-link').forEach(l => {
            l.classList.remove('bg-danger');
            l.classList.remove('text-white');
          });
          // highlight this one
          link.classList.add('bg-danger', 'text-white');
        });
      });
    </script>
</body>
</html>