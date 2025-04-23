<?php
include_once '../php/dependencies.php';
include_once '../php/web-title.php';

if (session_status() === PHP_SESSION_NONE) session_start();
include_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$conn = dbConnection();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// AJAX: dynamic subcategory dropdown
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category'])) {
    $cat = mysqli_real_escape_string($conn, $_POST['category']);
    $sub_q = "SELECT DISTINCT product_sub_category FROM products_tbl WHERE product_category = '$cat' ORDER BY product_sub_category";
    $sub_r = mysqli_query($conn, $sub_q);

    $options = '<option value="">All Subcategories</option>';
    while ($r = mysqli_fetch_assoc($sub_r)) {
        $sub  = htmlspecialchars($r['product_sub_category']);
        $options .= "<option value='{$sub}'>{$sub}</option>";
    }
    echo $options;
    exit();
}

// Get current user
$user_id     = $_SESSION['user_id'];
$user_q      = "SELECT * FROM users WHERE id = '$user_id'";
$user_res    = mysqli_query($conn, $user_q);
if (mysqli_num_rows($user_res) === 0) {
    exit('User not found.');
}

// Filters
$selected_category    = $_GET['category']    ?? '';
$selected_subcategory = $_GET['subcategory'] ?? '';

// Build WHERE clause
$conds = [];
if ($selected_category    !== '') $conds[] = "product_category = '" . mysqli_real_escape_string($conn, $selected_category)    . "'";
if ($selected_subcategory !== '') $conds[] = "product_sub_category = '" . mysqli_real_escape_string($conn, $selected_subcategory) . "'";
$where = $conds ? ' WHERE ' . implode(' AND ', $conds) : '';

$per_page = 6;
$pageno   = isset($_GET['pageno']) && is_numeric($_GET['pageno']) ? (int)$_GET['pageno'] : 1;
if ($pageno < 1) $pageno = 1;

$count_sql   = "SELECT COUNT(*) AS total FROM products_tbl{$where}";
$count_res   = mysqli_query($conn, $count_sql);
$total_rows  = mysqli_fetch_assoc($count_res)['total'];
$total_pages = max(1, ceil($total_rows / $per_page));
if ($pageno > $total_pages) $pageno = $total_pages;

$offset        = ($pageno - 1) * $per_page;
$product_sql   = "SELECT * FROM products_tbl{$where} LIMIT {$per_page} OFFSET {$offset}";
$product_res   = mysqli_query($conn, $product_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
         .product-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-image {
            height: 180px;
            object-fit: cover;
            width: 100%;
        }
        .pagination {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h3 class="mb-3">Browse Products</h3>

    <!-- FILTER FORM -->
    <form method="get" class="row mb-4" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <div class="col-md-4">
            <label>Category</label>
            <select name="category" id="category" class="form-control">
                <option value="">All Categories</option>
                <?php
                $cat_q  = "SELECT DISTINCT product_category FROM products_tbl ORDER BY product_category";
                $cat_res = mysqli_query($conn, $cat_q);
                while ($c = mysqli_fetch_assoc($cat_res)) {
                    $cat = htmlspecialchars($c['product_category']);
                    $sel = ($cat === $selected_category) ? ' selected' : '';
                    echo "<option value='{$cat}'{$sel}>{$cat}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-4">
            <label>Subcategory</label>
            <select name="subcategory" id="subcategory" class="form-control">
                <option value="">All Subcategories</option>
                <?php
                if ($selected_category !== '') {
                    $sub_q = "SELECT DISTINCT product_sub_category FROM products_tbl WHERE product_category = '" . mysqli_real_escape_string($conn, $selected_category) . "' ORDER BY product_sub_category";
                    $sub_res = mysqli_query($conn, $sub_q);
                    while ($s = mysqli_fetch_assoc($sub_res)) {
                        $sub = htmlspecialchars($s['product_sub_category']);
                        $sel = ($sub === $selected_subcategory) ? ' selected' : '';
                        echo "<option value='{$sub}'{$sel}>{$sub}</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="col-md-4 align-self-end mt-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <!-- PRODUCTS GRID -->
    <div class="row">
        <?php if (mysqli_num_rows($product_res) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($product_res)): ?>
                <div class="col-md-4 d-flex mb-4">
                    <div class="product-card w-100">
                        <img src="../admin/<?= htmlspecialchars($row['product_image']) ?>" class="product-image mb-2">
                        <h5><?= htmlspecialchars($row['product_name']) ?></h5>
                        <p>Category: <?= htmlspecialchars($row['product_category']) ?></p>
                        <p>Subcategory: <?= htmlspecialchars($row['product_sub_category']) ?: 'N/A' ?></p>
                        <p>Price: ₱<?= number_format($row['product_price'], 2) ?></p>
                        <a href="add_to_cart.php?product_id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm mt-auto">
                            <i class="bi bi-cart"></i> Add to Cart
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-warning">No products found.</div></div>
        <?php endif; ?>
    </div>

    <!-- PAGINATION -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-end">
            <li class="page-item<?= $pageno <= 1 ? ' disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pageno'=>$pageno-1])) ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item<?= $i == $pageno ? ' active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pageno'=>$i])) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item<?= $pageno >= $total_pages ? ' disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pageno'=>$pageno+1])) ?>">Next</a>
            </li>
        </ul>
    </nav>
</div>

<script>
$(function(){
    $('#category').change(function(){
        var cat = $(this).val();
        if (!cat) {
            $('#subcategory').html('<option value="">All Subcategories</option>');
            return;
        }
        $.post('<?= basename(__FILE__) ?>', { category: cat }, function(data){
            $('#subcategory').html(data);
        }).fail(function(xhr, st, err){ console.error(st, err); });
    });
    $('#subcategory').change(function(){ $(this).closest('form').submit(); });
});
</script>
</body>
</html>
