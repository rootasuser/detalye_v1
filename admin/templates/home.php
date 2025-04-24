<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
include_once '../config/database.php';

$conn = dbConnection();
if (!$conn) {
  die("Conn failed: " . mysqli_connect_error());
}

// Titles for cards
$dashboard_overview = "Dashboard Overview";
$customers = "Customers";
$orders = "Orders";
$sales = "Products";

// Count number of customers
$customer_count = 0;
$customer_sql = "SELECT COUNT(*) as total FROM users WHERE role = 'Customer'";
$customer_result = mysqli_query($conn, $customer_sql);
if ($row = mysqli_fetch_assoc($customer_result)) {
  $customer_count = $row['total'];
}

// Count number of orders
$order_count = 0;
$order_sql = "SELECT COUNT(*) as total FROM combined_orders_tbl";
$order_result = mysqli_query($conn, $order_sql);
if ($row = mysqli_fetch_assoc($order_result)) {
  $order_count = $row['total'];
}

// Count number of products
$product_count = 0;
$product_sql = "SELECT COUNT(*) as total FROM products_tbl";
$product_result = mysqli_query($conn, $product_sql);
if ($row = mysqli_fetch_assoc($product_result)) {
  $product_count = $row['total'];
}
?>
<!-- CUSTOM CSS STYLE -->
<style>
.card-stats { border: none; border-radius: 15px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); transition: 0.3s ease-in-out; }
.card-stats:hover { transform: translateY(-3px); }
.text-end { text-align: right;}
.icon-circle {
  width: 50px; height: 50px; background-color: rgba(0, 123, 255, 0.1); color: #007bff;
  display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 1.4rem;
}
@media (max-width: 768px) {
  main.col-md-9 { width: 100% !important; margin-left: 0 !important; padding-left: 1rem !important; padding-right: 1rem !important; }
  .container { padding-left: 0; padding-right: 0; }
}
</style>

<div class="container py-4">
  <h3 class="mb-4 text-center"><?php echo $dashboard_overview; ?></h3>
  <div class="row g-4">

    <!-- Customers Card -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
      <div class="card card-stats p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div class="icon-circle">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="text-end">
            <h6 class="text-muted mb-1"><?php echo $customers; ?></h6>
            <h4 class="fw-bold"><?php echo $customer_count; ?></h4>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Card -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
      <div class="card card-stats p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div class="icon-circle">
            <i class="bi bi-cart4"></i>
          </div>
          <div class="text-end">
            <h6 class="text-muted mb-1"><?php echo $orders; ?></h6>
            <h4 class="fw-bold"><?php echo $order_count; ?></h4>
          </div>
        </div>
      </div>
    </div>

    <!-- Products Card -->
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
      <div class="card card-stats p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div class="icon-circle">
            <i class="bi bi-box-seam"></i>
          </div>
          <div class="text-end">
            <h6 class="text-muted mb-1"><?php echo $sales; ?></h6>
            <h4 class="fw-bold"><?php echo $product_count; ?></h4>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
