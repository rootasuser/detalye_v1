<?php
include_once '../php/dependencies.php';
include_once '../php/web-title.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

$conn = dbConnection();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$userDetails = [];
$userQuery = "SELECT first_name, last_name, middle_initial, contact_number, complete_address FROM users WHERE id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $userDetails = $result->fetch_assoc();
}

// Fetch cart items
$cartItems = [];
$totalItems = 0;
$totalPrice = 0.0;

$cartQuery = "SELECT 
                cart.id AS cart_id,
                products_tbl.id AS product_id,
                products_tbl.product_name,
                products_tbl.product_image,
                products_tbl.product_price,
                cart.quantity,
                cart.size,
                cart.custom_sizes
              FROM customer_add_cart_tbl AS cart
              JOIN products_tbl ON cart.product_id = products_tbl.id
              WHERE cart.user_id = ?
              ORDER BY cart.created_at DESC";

$stmt = $conn->prepare($cartQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $row['product_price'] = floatval($row['product_price']);
    $row['subtotal'] = $row['product_price'] * $row['quantity'];
    $cartItems[] = $row;
    $totalItems += $row['quantity'];
    $totalPrice += $row['subtotal'];
}

// Handle Checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Generate order number
    $orderNumber = 'ORD-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        $orderSuccess = true;
        
        foreach ($cartItems as $item) {
            $customSizes = $item['custom_sizes'] ?? null;
            $status = 'Pending';
            $itemSubtotal = $item['subtotal'];

            $orderStmt = $conn->prepare("INSERT INTO combined_orders_tbl 
                (user_id, order_number, product_id, quantity, price, size, custom_sizes, total_amount, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $orderStmt->bind_param("isidsssss", 
                $user_id, 
                $orderNumber, 
                $item['product_id'], 
                $item['quantity'], 
                $item['product_price'], 
                $item['size'], 
                $customSizes, 
                $itemSubtotal, 
                $status
            );

            if (!$orderStmt->execute()) {
                $orderSuccess = false;
                throw new Exception("Error inserting order: " . $orderStmt->error);
            }
        }
        

        if ($orderSuccess) {
            $clearCartStmt = $conn->prepare("DELETE FROM customer_add_cart_tbl WHERE user_id = ?");
            $clearCartStmt->bind_param("i", $user_id);
            $clearCartStmt->execute();
            
   
            $conn->commit();
            
            $_SESSION['toast_message'] = 'Order #' . $orderNumber . ' placed successfully!';
            $_SESSION['toast_type'] = 'success';
      
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['toast_message'] = 'Error placing order: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .product-image {
            height: 80px;
            width: 80px;
            object-fit: cover;
        }
        .cart-table {
            margin-top: 20px;
        }
        .total-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 1050;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
<?php if (isset($_SESSION['toast_message'])): ?>
    <div class="toast-container">
        <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="mr-auto"><?= ucfirst($_SESSION['toast_type']) ?></strong>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body">
                <?= $_SESSION['toast_message'] ?>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('.toast').toast('show');
        });
    </script>
    <?php unset($_SESSION['toast_message']);
          unset($_SESSION['toast_type']);
    ?>
<?php endif; ?>

<div class="container mt-4">
    <h3 class="mb-3">Checkout</h3>
    
    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="?page=shop">Continue Shopping</a>
        </div>
    <?php else: ?>
        <form method="post" id="checkoutForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Shipping Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($userDetails['first_name'] ?? '') ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($userDetails['last_name'] ?? '') ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Middle Initial</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($userDetails['middle_initial'] ?? '') ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($userDetails['contact_number'] ?? '') ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Complete Address</label>
                                <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($userDetails['complete_address'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="cart-table">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../admin/<?= htmlspecialchars($item['product_image']) ?>" class="product-image" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                                <div class="ml-3">
                                                    <h6><?= htmlspecialchars($item['product_name']) ?></h6>
                                                    <?php if (!empty($item['size'])): ?>
                                                        <small>Size: <?= htmlspecialchars($item['size']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>₱<?= number_format($item['product_price'], 2) ?></td>
                                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                                        <td>₱<?= number_format($item['subtotal'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="font-weight-bold">
                                    <td colspan="3" class="text-right">Total</td>
                                    <td>₱<?= number_format($totalPrice, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="total-section">
                        <button type="submit" name="place_order" class="btn btn-primary btn-block">Place Order</button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<!-- Loading Spinner Overlay -->
<div class="loading-overlay">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initially hide the loading overlay
        $('.loading-overlay').hide();
        
        // Show loading spinner on form submit
        $('#checkoutForm').on('submit', function(e) {
            // Don't prevent default here, let the form submit naturally
            // Show loading spinner
            $('.loading-overlay').show();
        });
    });
</script>