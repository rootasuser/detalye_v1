<?php
/**
 * DEPENDENCIES STORED:: CDNLINKS
 */
include_once './../php/dependencies.php';

/**
 * WEB TAB TITLE STORED
 */
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

// Fetch products for the slideshow
$slideshowQuery = "SELECT * FROM products_tbl ORDER BY id DESC LIMIT 5"; // Get 5 latest products
$slideshowResult = mysqli_query($conn, $slideshowQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $web_title; ?></title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">

    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }

        .carousel-item {
            height: 500px;
            min-height: 300px;
            background: no-repeat center center scroll;
            background-size: cover;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .slide-content {
            max-width: 1000px;
            padding: 20px;
        }

        .slide-content h3 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .carousel-item {
                height: 300px;
            }
            .slide-content h3 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Slideshow -->
    <div id="productCarousel" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php $i = 0; while ($row = mysqli_fetch_assoc($slideshowResult)) { ?>
                <li data-target="#productCarousel" data-slide-to="<?php echo $i ?>" <?php echo $i == 0 ? 'class="active"' : '' ?>></li>
            <?php $i++; } ?>
        </ol>

        <div class="carousel-inner mt-2 mb-4">
            <?php
            $slideshowResult = mysqli_query($conn, $slideshowQuery); 
            $i = 0;
            while ($row = mysqli_fetch_assoc($slideshowResult)) {
                $active = $i == 0 ? 'active' : '';
                $imageUrl = '../admin/' . $row['product_image'];
            ?>
                <div class="carousel-item <?php echo $active ?>">
                    <img class="d-block w-100" src="<?php echo $imageUrl; ?>" alt="<?php echo $row['product_name'] ?>">
                    <div class="overlay">
                        <div class="slide-content">
                            <h3><?php echo $row['product_name'] ?></h3>
                            <p class="lead"><?php echo $row['product_category'] ?></p>
                            <p class="lead"><?php echo $row['product_sub_category'] ?? 'N/A' ?></p>
                            <p class="lead">₱<?php echo number_format($row['product_price'], 2) ?></p>
                            <a href="#" class="btn btn-warning btn-lg"><i class="bi bi-cart4"></i> Shop Now</a>
                        </div>
                    </div>
                </div>
            <?php $i++; } ?>
        </div>

        <a class="carousel-control-prev" href="#productCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#productCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

  

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>