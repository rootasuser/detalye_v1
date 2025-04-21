<?php
/**
 * DEPENDENCIES STORED
 */

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
    <!-- Dashboard Container -->
    <div class="container mt-5">
        <h2>Welcome, <?php echo $user_data['first_name'] . ' ' . $user_data['last_name']; ?></h2>
        <div class="row">
            <div class="col-md-6">
                <h3>Your Profile Information</h3>
                <ul class="list-group">
                    <li class="list-group-item">Username: <?php echo $user_data['username']; ?></li>
                    <li class="list-group-item">Email: <?php echo $user_data['email']; ?></li>
                    <li class="list-group-item">Contact: <?php echo $user_data['contact_number']; ?></li>
                    <li class="list-group-item">Address: <?php echo $user_data['complete_address']; ?></li>
                    
                </ul>
            </div>
            <div class="col-md-6">
                <h3>Update Profile</h3>
                <form action="update_profile.php" method="POST">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" name="first_name" id="first_name" value="<?php echo $user_data['first_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" name="last_name" id="last_name" value="<?php echo $user_data['last_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" class="form-control" name="contact_number" id="contact_number" value="<?php echo $user_data['contact_number']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="complete_address">Address</label>
                        <input type="text" class="form-control" name="complete_address" id="complete_address" value="<?php echo $user_data['complete_address']; ?>" required>
                    </div>
                    
                    <button type="submit" name="submit_update" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>

  
</body>
</html>
