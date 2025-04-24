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
    die("Connf failed: " . mysqli_connect_error());
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


if (isset($_POST['update'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $middle_initial = isset($_POST['middle_initial']) ? mysqli_real_escape_string($conn, $_POST['middle_initial']) : '';
    $contact_number = isset($_POST['contact_number']) ? mysqli_real_escape_string($conn, $_POST['contact_number']) : '';
    $complete_address = mysqli_real_escape_string($conn, $_POST['complete_address']);

    $update_query = "UPDATE users 
                     SET username = '$username', email = '$email', first_name = '$first_name', last_name = '$last_name', 
                         middle_initial = '$middle_initial', contact_number = '$contact_number', complete_address = '$complete_address'
                     WHERE id = '$user_id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "Account updated successfully!";
        $_SESSION['message_type'] = "success"; 
    } else {
        // Error message
        $_SESSION['message'] = "Error updating account. Please try again.";
        $_SESSION['message_type'] = "error"; 
    }
 
}
?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="container mt-5">
<div class="card shadow border-0 rounded-3">
    <div class="card-body">
    <h3 class="mb-4 text-center">Update Account Information</h3>
<?php
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-' . ($message_type == 'success' ? 'success' : 'danger') . '">';
    echo $message;
    echo '</div>';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<form method="POST">
    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user_data['username']) ?>" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>
    </div>
    <div class="mb-3">
        <label for="first_name" class="form-label">First Name</label>
        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user_data['first_name']) ?>" required>
    </div>
    <div class="mb-3">
        <label for="last_name" class="form-label">Last Name</label>
        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user_data['last_name']) ?>" required>
    </div>
    <div class="mb-3">
        <label for="middle_initial" class="form-label">Middle Initial</label>
        <input type="text" class="form-control" id="middle_initial" name="middle_initial" value="<?= htmlspecialchars($user_data['middle_initial']) ?>">
    </div>
    <div class="mb-3">
        <label for="contact_number" class="form-label">Contact Number</label>
        <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?= htmlspecialchars($user_data['contact_number']) ?>">
    </div>
    <div class="mb-3">
        <label for="complete_address" class="form-label">Complete Address</label>
        <textarea class="form-control" id="complete_address" name="complete_address" rows="3"><?= htmlspecialchars($user_data['complete_address']) ?></textarea>
    </div>
    <div class="d-flex align-items-end justify-content-end">
    <button type="submit" name="update" class="btn btn-primary">Update</button>
    </div>
    
</form>
    </div>
</div>
</div>


