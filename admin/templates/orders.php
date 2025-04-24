<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once '../config/database.php';
$conn = dbConnection();
if (!$conn) {
    ob_end_clean();
    echo '<script>
        Swal.fire("Error", "Database connection failed: ' . mysqli_connect_error() . '", "error");
    </script>';
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['new_status'];

    $update_sql = "UPDATE combined_orders_tbl SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        echo '<script>
            Swal.fire("Success", "Order status updated to ' . $new_status . '", "success").then(() => {
                window.location.href = "orders.php";
            });
        </script>';
    } else {
        echo '<script>
            Swal.fire("Error", "Failed to update order status", "error");
        </script>';
    }
    $stmt->close();
}

// Fetch orders
$query = "SELECT co.*, u.first_name, u.last_name, u.middle_initial, u.contact_number, u.complete_address, p.product_name 
          FROM combined_orders_tbl AS co 
          JOIN users AS u ON co.user_id = u.id 
          JOIN products_tbl AS p ON co.product_id = p.id 
          ORDER BY co.created_at DESC";

$result = $conn->query($query);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders</title>
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            text-align: center;
        }

        .close {
            color: red;
            float: right;
            justify-content: end;
            align-items: end;
            font-size: 58px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .qr-code {
            max-width: 200px;
            max-height: 200px;
            margin: 20px auto;
        }

        .order-details {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: left;
        }

        .print-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
    <script>
        function searchOrders() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let rows = document.getElementById("ordersTable").getElementsByTagName("tr");

            for (let i = 1; i < rows.length; i++) {
                let cells = rows[i].getElementsByTagName("td");
                let found = false;
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j]) {
                        let text = cells[j].textContent || cells[j].innerText;
                        if (text.toLowerCase().indexOf(input) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                if (found) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        let modal = null;
        let qrImage = null;
        let orderDetails = null;
        let printButton = null;
        let closeButton = null;

        function initModal() {
            modal = document.getElementById("printModal");
            qrImage = document.getElementById("qrImage");
            orderDetails = document.getElementById("orderDetails");
            printButton = document.getElementById("printButton");
            closeButton = document.getElementById("closeButton");

            closeButton.onclick = function() {
                modal.style.display = "none";
            };

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            };

            printButton.onclick = function() {
                window.print();
            };
        }

        function openPrintModal(orderId, orderNumber, customerName, contactNumber, address, productName, qty, size, customSizesFormatted, totalAmount, status) {
    // Create QR code data with Custom Sizes included
    const qrData = encodeURIComponent(`
        Order ID: ${orderId}
        Order Number: ${orderNumber}
        Customer: ${customerName}
        Contact: ${contactNumber}
        Address: ${address}
        Product: ${productName}
        Quantity: ${qty}
        Size: ${size}
        Custom Sizes:
        ${customSizesFormatted.replace(/\\n/g, '\n')}
        Total Amount: ₱${totalAmount}
        Status: ${status}
    `);

    // Generate QR code URL
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${qrData}`;
    qrImage.src = qrUrl;

    // Set order details in the modal
    orderDetails.innerHTML = `
        <h4>Order Details</h4>
        <p style="display: none;"><strong>Order ID:</strong> ${orderId}</p>
        <p><strong>Order Number:</strong> ${orderNumber}</p>
        <p><strong>Customer:</strong> ${customerName}</p>
        <p><strong>Contact:</strong> ${contactNumber}</p>
        <p><strong>Address:</strong> ${address}</p>
        <p><strong>Product:</strong> ${productName}</p>
        <p><strong>Quantity:</strong> ${qty}</p>
        <p><strong>Size:</strong> ${size}</p>
        <p><strong>Custom Sizes:</strong><br>${customSizesFormatted.replace(/\\n/g, '<br>')}</p>
        <p><strong>Total Amount:</strong> ₱${totalAmount}</p>
        <p><strong>Status:</strong> ${status}</p>
    `;

    // Show modal
    modal.style.display = "block";
}

window.onload = function() {
    initModal();
};

    </script>
</head>
<body class="bg-light">
<div class="container py-5">
    <h3 class="mb-4 text-center text-primary">Orders Management</h3>
    
    <!-- Search Input -->
    <div class="mb-4 text-center">
        <input type="text" id="searchInput" onkeyup="searchOrders()" placeholder="Search orders..." class="form-control w-50 mx-auto">
    </div>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-bordered align-middle text-center" id="ordersTable">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Size</th>
                    <th>Custom Sizes</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Update</th>
                    <th>Print</th> 
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= htmlspecialchars($row['order_number'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($row['contact_number'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($row['complete_address'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($row['product_name'] ?? ''); ?></td>
                        <td><?= $row['quantity'] ?? 0; ?></td>
                        <td><?= htmlspecialchars($row['size'] ?? ''); ?></td>
                        <td>
                        <?php
                            $customSizes = $row['custom_sizes'] ?? '';
                            $sizesArray = json_decode($customSizes, true);

                            if (is_array($sizesArray)) {
                                echo '<pre>';
                                foreach ($sizesArray as $key => $value) {
                                    echo htmlspecialchars($key) . ': ' . htmlspecialchars($value) . "\n";
                                }
                                echo '</pre>';
                            } else {
                                echo '<pre>' . htmlspecialchars($customSizes) . '</pre>';
                            }
                        ?>
                    </td>




                        <td>₱<?= number_format($row['total_amount'] ?? 0, 2); ?></td>
                        <td>
                            <?php
                            $badge_class = match($row['status'] ?? '') {
                                'Pending' => 'bg-warning',
                                'On Process' => 'bg-info',
                                'Delivered' => 'bg-success',
                                default => 'bg-secondary',
                            };
                            ?>
                            <span class="badge <?= $badge_class ?>"><?= $row['status'] ?? ''; ?></span>
                        </td>
                        <td>
                            <form method="POST" class="d-flex gap-2 align-items-center justify-content-center">
                                <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
                                <select name="new_status" class="form-select form-select-sm w-auto" required>
                                    <option value="" disabled selected>Choose</option>
                                    <option value="On Process" <?= $row['status'] === 'On Process' ? 'selected' : ''; ?>>On Process</option>
                                    <option value="Delivered" <?= $row['status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-success">Save</button>
                            </form>
                        </td>
                        <td>
                        <?php
                            $customSizes = $row['custom_sizes'] ?? '';

                         
                            $customSizesArray = is_array($customSizes) ? $customSizes : json_decode($customSizes, true);

                           
                            if (is_array($customSizesArray)) {
                                $formattedCustomSizes = implode("\\n", array_map(
                                    fn($key, $val) => htmlspecialchars("$key: $val"),
                                    array_keys($customSizesArray),
                                    $customSizesArray
                                ));
                            } else {
                                $formattedCustomSizes = htmlspecialchars((string) $customSizes);
                            }
                            ?>


                        <button class="btn btn-sm btn-primary" 
                            onclick="openPrintModal(
                                '<?= htmlspecialchars($row['id'] ?? '') ?>',
                                '<?= htmlspecialchars($row['order_number'] ?? '') ?>',
                                '<?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name'] ?? '') ?>',
                                '<?= htmlspecialchars($row['contact_number'] ?? '') ?>',
                                '<?= htmlspecialchars($row['complete_address'] ?? '') ?>',
                                '<?= htmlspecialchars($row['product_name'] ?? '') ?>',
                                '<?= htmlspecialchars($row['quantity'] ?? 0) ?>',
                                '<?= htmlspecialchars($row['size'] ?? '') ?>',
                                '<?= $formattedCustomSizes ?>',
                                '<?= htmlspecialchars(number_format($row['total_amount'] ?? 0, 2)) ?>',
                                '<?= htmlspecialchars($row['status'] ?? '') ?>'
                            )">
                            <i class="bi bi-printer"></i> Print
                        </button>

                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="12" class="text-center">No orders found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Print Modal -->
<div id="printModal" class="modal">
    <div class="modal-content">

    <div>
    <span id="closeButton" class="close" style="float: right;">&times;</span>
    </div>
           
        <h2>Order Receipt</h2>
        <img id="qrImage" class="qr-code" src="" alt="QR Code">
        <div id="orderDetails" class="order-details"></div>
        <div>
            <p class="text-center">Thank you for your order!</p>
        </div>
        <button id="printButton" class="print-button"><i class="bi bi-printer"></i> Print</button>
    </div>
</div>

</body>
</html>