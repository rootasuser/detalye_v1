<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../config/database.php';

$conn = dbConnection();
if (!$conn) {
    die("Conn failed: " . mysqli_connect_error());
}
?>

<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once '../config/database.php';

$conn = dbConnection();
if (!$conn) {
    die("Conn failed: " . mysqli_connect_error());
}
?>

<div class="container-fluid mt-5 p-0 justify-content-center align-items-center">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Products</h4>
    <button class="btn" style="background-color: #000; color: #fff;"><i class="bi bi-plus"></i> New</button>
  </div>

  <div class="card shadow-sm p-3 bg-white rounded border-0">
    <div class="card-body">
  <div class="table-responsive">
    <table id="myTable" class="table w-100 border table-striped table-bordered table-hover">
      <thead style="background-color: #0d1b2a; color: #fff;">
        <tr>
          <th class="text-nowrap">#</th>
          <th class="text-nowrap">Product Image</th>
          <th class="text-nowrap">Product Name</th>
          <th class="text-nowrap">Product Category</th>
          <th class="text-nowrap">Product Sub Category</th>
          <th class="text-nowrap">Product Quantity</th>
          <th class="text-nowrap">Product Price</th>
          <th class="text-nowrap">Product Description</th>
          <th class="text-nowrap">Product Size</th>
          <th class="text-nowrap">Product Custom Size Collar</th>
          <th class="text-nowrap">Product Custom Size Shoulder</th>
          <th class="text-nowrap">Product Custom Size Chest</th>
          <th class="text-nowrap">Product Custom Size Waist</th>
          <th class="text-nowrap">Product Custom Size Hips</th>
          <th class="text-nowrap">Product Custom Size Cuff</th>
          <th class="text-nowrap">Product Custom Size Sleeve Length</th>
          <th class="text-nowrap">Product Custom Size Arm Hole</th>
          <th class="text-nowrap">Product Custom Size Back Length</th>
          <th class="text-nowrap">Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- Table rows go here -->
      </tbody>
    </table>
  </div>
</div>
</div>
</div>


