<?php
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }
  include_once '../config/database.php';

  $conn = dbConnection();
  if (!$conn) {
    die("Conn failed: " . mysqli_connect_error());
  }

  // CARD TITLES:: CHANGE IF WANT
  $dashboard_overview = "Dashboard Overview";
  $customers = "Customers";
  $orders = "Orders";
  $sales = "Sales";

?>
<!-- CUSTOM CSS STYLE -->
<style> .card-stats { border: none; border-radius: 15px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); transition: 0.3s ease-in-out; } .card-stats:hover { transform: translateY(-3px); } .text-end { text-align: right;} .icon-circle {width: 50px;height: 50px;background-color: rgba(0, 123, 255, 0.1);color: #007bff;display: flex;align-items: center;justify-content: center;border-radius: 50%;font-size: 1.4rem;} @media (max-width: 768px) {main.col-md-9 {width: 100% !important;margin-left: 0 !important;padding-left: 1rem !important;padding-right: 1rem !important;} .container {padding-left: 0;padding-right: 0;}} </style>

<div class="container py-4">
        <h3 class="mb-4 text-center"><?php echo  $dashboard_overview; ?></h3>
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
                  <h4 class="fw-bold">540</h4>
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
                  <h4 class="fw-bold">89</h4>
                </div>
              </div>
            </div>
          </div>

          <!-- Sales Card -->
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card card-stats p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div class="icon-circle">
                  <i class="bi bi-cash-stack"></i>
                </div>
                <div class="text-end">
                  <h6 class="text-muted mb-1"><?php echo $sales; ?></h6>
                  <h4 class="fw-bold">₱4,300</h4>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

