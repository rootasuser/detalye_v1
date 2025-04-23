<?php
include_once '../config/database.php';
$conn = dbConnection();

$category = mysqli_real_escape_string($conn, $_POST['category'] ?? '');
$subcategory = mysqli_real_escape_string($conn, $_POST['subcategory'] ?? '');

$conditions = [];
if (!empty($category)) $conditions[] = "product_category = '$category'";
if (!empty($subcategory)) $conditions[] = "product_sub_category = '$subcategory'";

$sql = "SELECT * FROM products_tbl";
if ($conditions) $sql .= " WHERE " . implode(" AND ", $conditions);

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0):
    while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="col-md-4 d-flex mb-4">
            <div class="product-card w-100">
                <img src="../admin/<?php echo $row['product_image']; ?>" alt="" class="product-image mb-2">
                <h5><?php echo htmlspecialchars($row['product_name']); ?></h5>
                <p>Category: <?php echo htmlspecialchars($row['product_category']); ?></p>
                <p>Subcategory: <?php echo htmlspecialchars($row['product_sub_category']) ?: 'N/A'; ?></p>
                <p>Price: $<?php echo number_format($row['product_price'], 2); ?></p>
                <a href="#" class="btn btn-outline-primary btn-sm mt-auto">View Details</a>
            </div>
        </div>
    <?php endwhile;
else: ?>
    <div class="col-12">
        <div class="alert alert-warning">No products found.</div>
    </div>
<?php endif; ?>
