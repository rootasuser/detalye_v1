<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../config/database.php';
$conn = dbConnection();
$user_id = $_SESSION['user_id'] ?? 0;


$orders_per_page = 1;
$current_orders_page = isset($_GET['orders_page']) ? (int)$_GET['orders_page'] : 1;
$current_orders_page = max($current_orders_page, 1);
$offset = ($current_orders_page - 1) * $orders_per_page;

$total_orders_stmt = $conn->prepare("SELECT COUNT(DISTINCT order_number) as total FROM combined_orders_tbl WHERE user_id = ?");
$total_orders_stmt->bind_param("i", $user_id);
$total_orders_stmt->execute();
$total_orders_result = $total_orders_stmt->get_result();
$total_orders = $total_orders_result->fetch_assoc()['total'];
$total_orders_stmt->close();

$total_pages = $total_orders > 0 ? ceil($total_orders / $orders_per_page) : 0;


$order_numbers = [];
if ($total_orders > 0) {
    $order_numbers_stmt = $conn->prepare("
        SELECT DISTINCT order_number 
        FROM combined_orders_tbl 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $order_numbers_stmt->bind_param("iii", $user_id, $orders_per_page, $offset);
    $order_numbers_stmt->execute();
    $order_numbers_result = $order_numbers_stmt->get_result();
    
    while ($row = $order_numbers_result->fetch_assoc()) {
        $order_numbers[] = $row['order_number'];
    }
    $order_numbers_stmt->close();
}

$groupedOrders = [];
if (!empty($order_numbers)) {
    $placeholders = rtrim(str_repeat('?,', count($order_numbers)), ',');
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
            o.payment_method,
            o.reference_number,
            p.product_name,
            p.product_image,
            p.product_category,
            p.product_sub_category
        FROM combined_orders_tbl o
        INNER JOIN products_tbl p ON o.product_id = p.id
        WHERE o.user_id = ? AND o.order_number IN ($placeholders)
        ORDER BY o.created_at DESC
    ";
    $stmt = $conn->prepare($orderQuery);
    
    $types = 'i' . str_repeat('s', count($order_numbers));
    $params = [$user_id];
    $params = array_merge($params, $order_numbers);
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $orderResult = $stmt->get_result();
    
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
    $stmt->close();
}
?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

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
            background-color: #fff; 
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .page-item {
            margin: 0 5px;
        }

        .page-link {
            border-radius: 5px;
            padding: 8px 16px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .page-link:hover {
            background-color: #e9ecef;
        }

        .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }
    </style>

    <div class="card shadow mt-5 border-0">
        <div class="card-body">
        <h3 class="mb-4 text-start">My Orders</h3>

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
                                <th>Payment Method</th>
                                <th>Reference Number</th>
                                <th>Product Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
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
                                    <td>
                                        <span class="badge badge-pill badge-primary">
                                        <?= htmlspecialchars($item['payment_method']) ?></td>
                                        </span>    
                                    <td><?= htmlspecialchars($item['reference_number']) ?></td>
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

    <!-- Pagination Controls -->
    <nav aria-label="Orders pagination">
        <ul class="pagination align-items-end d-flex justify-content-end">
            <?php if ($current_orders_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=orders&orders_page=<?= $current_orders_page - 1 ?>">
                        Previous
                    </a>
                </li>
            <?php endif; ?>

            <li class="page-item active">
                <span class="page-link">
                    Page <?= $current_orders_page ?> of <?= $total_pages ?>
                </span>
            </li>

            <?php if ($current_orders_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=orders&orders_page=<?= $current_orders_page + 1 ?>">
                        Next
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php else: ?>
    <div class="alert alert-info text-center">
        You have no orders yet.
    </div>
<?php endif; ?>
        </div>
    </div>

