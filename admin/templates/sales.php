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

// Fetch total sales where status is 'Delivered'
$query = "SELECT SUM(total_amount) AS total_sales FROM combined_orders_tbl WHERE status = 'Delivered'";
$result = $conn->query($query);
$total_sales = 0;

if ($result && $row = $result->fetch_assoc()) {
    $total_sales = $row['total_sales'];
}

// Export to CSV
if (isset($_POST['export_csv'])) {
    // Fetch all orders with status 'Delivered' to export
    $orders_query = "SELECT order_number, user_id, product_id, quantity, size, custom_sizes, total_amount, status, created_at 
                     FROM combined_orders_tbl WHERE status = 'Delivered'";
    $orders_result = $conn->query($orders_query);

    if ($orders_result->num_rows > 0) {
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="delivered_orders.csv"');
        $output = fopen('php://output', 'w');
        
        // Column headings for CSV
        fputcsv($output, ['Order Number', 'User ID', 'Product ID', 'Quantity', 'Size', 'Custom Sizes', 'Total Amount', 'Status', 'Created At']);
        
        // Output data rows
        while ($order = $orders_result->fetch_assoc()) {
            fputcsv($output, $order);
        }
        
        fclose($output);
        exit;
    } else {
        echo '<script>Swal.fire("Error", "No delivered orders to export", "error");</script>';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Summary</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
<div class="container py-5">
    <h3 class="mb-4 text-center text-primary">Sales Summary</h3>
    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-bordered align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>Total Sales (Delivered Orders)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>₱<?= number_format($total_sales, 2); ?></td>
                    <td>
                        <!-- Button to trigger CSV export -->
                        <form method="POST" class="d-inline">
                            <button type="submit" name="export_csv" class="btn btn-sm btn-success">Export to CSV</button>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
