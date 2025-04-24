<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$orderQuery = "
SELECT 
    o.product_id,
    o.order_number,
    o.quantity,
    o.size,
    o.custom_sizes,
    o.price,
    o.created_at,
    o.status,
    p.product_name,
    p.product_image,
    p.product_category,
    p.product_sub_category
FROM combined_orders_tbl o
INNER JOIN products_tbl p ON o.product_id = p.id
WHERE o.user_id = ?
ORDER BY o.created_at DESC
";


$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orderResult = $stmt->get_result();

$groupedOrders = [];
while ($row = $orderResult->fetch_assoc()) {
    $order_number = $row['order_number'];
    if (!isset($groupedOrders[$order_number])) {
        $groupedOrders[$order_number] = [
            'order_date' => $row['created_at'],
            'status' => $row['status'],
            'total_amount' => 0,
            'items' => [],
        ];
    }

    $groupedOrders[$order_number]['total_amount'] += $row['price'] * $row['quantity'];
    $groupedOrders[$order_number]['items'][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once './../php/dependencies.php'; ?>
    <title>My Orders</title>
    <style>
        th {
            text-wrap: nowrap;
        }

        .table-wrapper {
            max-height: 400px; 
            overflow-y: auto; 
            overflow-x: auto; 
            touch-action: none;
        }

        .table th {
            position: sticky;
            top: 0;
            z-index: 1;
            background-color: #f8f9fa; 
        }

        .table {
            transition: transform 0.3s ease-in-out;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h3 class="mb-4 text-center">My Orders</h3>

    <?php if (!empty($groupedOrders)): ?>
        <?php foreach ($groupedOrders as $orderNumber => $orderData): ?>
            <div class="card mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #<?= htmlspecialchars($orderNumber) ?></strong><br>
                        <small>Ordered on: <?= htmlspecialchars(date("Y-m-d H:i A", strtotime($orderData['order_date']))) ?></small>
                    </div>
                    <span class="badge 
                        <?= $orderData['status'] === 'Pending' ? 'bg-warning' : ($orderData['status'] === 'On Process' ? 'bg-info' : 'bg-success') ?>">
                        <?= htmlspecialchars($orderData['status']) ?>
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-wrapper">
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>Product Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Size</th>
                                    <th>Custom Sizes</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody style="overflow-x: auto;">
                                <?php foreach ($orderData['items'] as $item): ?>
                                    <tr class="text-center align-middle">
                                        <td>
                                            <img src="../admin/<?= htmlspecialchars($item['product_image']) ?>" width="60" height="60" style="object-fit: cover;" alt="Product">
                                        </td>
                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                        <td><?= htmlspecialchars($item['product_category']) ?><?= $item['product_sub_category'] ? ' - ' . htmlspecialchars($item['product_sub_category']) : '' ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= htmlspecialchars($item['size']) ?></td>
                                        <td>
                                            <?php
                              
                                            $customSizes = json_decode($item['custom_sizes'], true);
                                        
                                            if (is_array($customSizes)) {
                                                $customText = [];
                                                foreach ($customSizes as $key => $value) {
                                                    $customText[] = '<strong>' . ucfirst(str_replace('_', ' ', $key)) . ':</strong> ' . htmlspecialchars($value);
                                                }
                                                echo implode('<br>', $customText); 
                                            } else {
                                                echo 'No custom sizes available';
                                            }
                                            ?>
                                        </td>
                                        <td>₱<?= number_format($item['price'], 2) ?></td>
                                        <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end p-3">
                        <strong>Total Amount: ₱<?= number_format($orderData['total_amount'], 2) ?></strong>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">
            You have no orders yet.
        </div>
    <?php endif; ?>
</div>


</body>
</html>
