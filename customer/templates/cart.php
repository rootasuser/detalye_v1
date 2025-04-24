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

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $cart_id = intval($_POST['cart_id']);
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First, get the item from cart to verify it exists and belongs to the user
        $verifyStmt = $conn->prepare("SELECT * FROM customer_add_cart_tbl WHERE id = ? AND user_id = ?");
        $verifyStmt->bind_param("ii", $cart_id, $user_id);
        $verifyStmt->execute();
        $result = $verifyStmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update product quantity - add back the removed quantity
            $updateProductStmt = $conn->prepare("UPDATE products_tbl SET product_quantity = product_quantity + ? WHERE id = ?");
            $updateProductStmt->bind_param("ii", $quantity, $product_id);
            $updateProductStmt->execute();
            
            // Now remove the item from cart
            $deleteStmt = $conn->prepare("DELETE FROM customer_add_cart_tbl WHERE id = ? AND user_id = ?");
            $deleteStmt->bind_param("ii", $cart_id, $user_id);
            $deleteStmt->execute();
            
            // If everything is successful, commit the transaction
            $conn->commit();
            $_SESSION['toast_message'] = 'Item removed from cart and quantity restored';
            $_SESSION['toast_type'] = 'success';
        } else {
            throw new Exception("Cart item not found or doesn't belong to the user");
        }
    } catch (Exception $e) {
        // If there's an error, roll back the transaction
        $conn->rollback();
        $_SESSION['toast_message'] = 'Error removing item: ' . $e->getMessage();
        $_SESSION['toast_type'] = 'danger';
    }
}

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
              FROM 
                customer_add_cart_tbl AS cart
              JOIN 
                products_tbl ON cart.product_id = products_tbl.id
              WHERE 
                cart.user_id = ?
              ORDER BY 
                cart.created_at DESC";

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
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

<style>
    th {
        text-wrap: nowrap;
    }
</style>

<div class="container mt-4">
    <h3 class="mb-3">Your Shopping Cart</h3>
    
    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info">
            Your cart is empty.
        </div>
    <?php else: ?>
        <div class="cart-table">
            <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Size</th>
                        <th>Custom Sizes</th>
                        <th>Subtotal</th>
                        <th>Action</th>
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
                                    </div>
                                </div>
                            </td>
                            <td>₱<?= number_format($item['product_price'], 2) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td><?= htmlspecialchars($item['size'] ?? 'N/A') ?></td>
                            <td>
                                <?php if (!empty($item['custom_sizes'])): ?>
                                    <?php
                                    $customSizes = json_decode($item['custom_sizes'], true);
                                    if (is_array($customSizes) && !empty($customSizes)) {
                                        foreach ($customSizes as $key => $value) {
                                            echo ucfirst(str_replace('_', ' ', $key)) . ': ' . htmlspecialchars($value) . '<br>';
                                        }
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>₱<?= number_format($item['subtotal'], 2) ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                    <input type="hidden" name="quantity" value="<?= $item['quantity'] ?>">
                                    <input type="hidden" name="remove_item" value="1">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            
            <div class="total-section">
                <div class="row">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <table class="table table-borderless">
                            <tr>
                                <td>Total Items:</td>
                                <td><?= $totalItems ?></td>
                            </tr>
                            <tr>
                                <td>Total Price:</td>
                                <td>₱<?= number_format($totalPrice, 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="?page=checkout" class="btn btn-primary btn-block">Proceed to Checkout</a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>