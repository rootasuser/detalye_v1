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


if (isset($_GET['search']) && !empty($_GET['search'])) {
    // Sanitize input to prevent SQL injection
    $searchQuery = htmlspecialchars($_GET['search']);
    
    // Query the database for products that match the search term
    $sql = "SELECT * FROM products_tbl WHERE product_name LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $searchParam);
    $searchParam = "%$searchQuery%"; // Use LIKE for partial matching
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // If no search term, fetch all products
    $sql = "SELECT * FROM products_tbl";
    $result = $conn->query($sql);
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
        $_SESSION['toast_message'] = 'Missing required parameters';
        $_SESSION['toast_type'] = 'danger';
       
    }

    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $size = isset($_POST['size']) ? mysqli_real_escape_string($conn, $_POST['size']) : null;
    $custom_sizes = isset($_POST['custom_sizes']) ? json_decode($_POST['custom_sizes'], true) : [];
    $custom_sizes_json = !empty($custom_sizes) ? json_encode($custom_sizes) : null;

    // Check if the product has enough quantity
    $stmt = $conn->prepare("SELECT product_quantity FROM products_tbl WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product_data = $result->fetch_assoc();
    
    if (!$product_data) {
        $_SESSION['toast_message'] = 'Product not found';
        $_SESSION['toast_type'] = 'danger';
       
    }
    
    $product_quantity = $product_data['product_quantity'];
    
    if ($product_quantity < $quantity) {
        $_SESSION['toast_message'] = 'Insufficient product quantity in stock';
        $_SESSION['toast_type'] = 'danger';
      
    }

    $stmt = $conn->prepare("UPDATE products_tbl SET product_quantity = product_quantity - ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $product_id);
    $stmt->execute();
    
    $stmt = $conn->prepare("INSERT INTO customer_add_cart_tbl 
                          (user_id, product_id, quantity, size, custom_sizes, created_at) 
                          VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiiss", $_SESSION['user_id'], $product_id, $quantity, $size, $custom_sizes_json);
    
    if ($stmt->execute()) {
        $_SESSION['toast_message'] = 'Product added to cart successfully';
        $_SESSION['toast_type'] = 'success';
    } else {
        $_SESSION['toast_message'] = 'Error inserting into database: ' . $stmt->error;
        $_SESSION['toast_type'] = 'danger';
    }

  
}

$conn = dbConnection();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get user
$user_id = $_SESSION['user_id'];
$user_q = "SELECT * FROM users WHERE id = '$user_id'";
$user_res = mysqli_query($conn, $user_q);
if (mysqli_num_rows($user_res) === 0) {
    exit('User not found.');
}

// Filters
$selected_category = $_GET['category'] ?? '';
$selected_subcategory = $_GET['subcategory'] ?? '';

// Build WHERE clause
$conds = [];
if ($selected_category !== '') {
    $conds[] = "product_category = '" . mysqli_real_escape_string($conn, $selected_category) . "'";
}
if ($selected_subcategory !== '') {
    $conds[] = "product_sub_category = '" . mysqli_real_escape_string($conn, $selected_subcategory) . "'";
}
$where = $conds ? ' WHERE ' . implode(' AND ', $conds) : '';

$per_page = 6;
$pageno = isset($_GET['pageno']) && is_numeric($_GET['pageno']) ? (int)$_GET['pageno'] : 1;
if ($pageno < 1) $pageno = 1;

$count_sql = "SELECT COUNT(*) AS total FROM products_tbl {$where}";
$count_res = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_res)['total'] ?? 0;
$total_pages = max(1, ceil($total_rows / $per_page));
if ($pageno > $total_pages) $pageno = $total_pages;

$offset = ($pageno - 1) * $per_page;
$product_sql = "SELECT * FROM products_tbl {$where} LIMIT {$per_page} OFFSET {$offset}";
$product_res = mysqli_query($conn, $product_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
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
        .size-button.active {
            background-color: #dc3545 !important;
            color: white !important;
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

<div class="container mt-4">
    <h3 class="mb-3">Browse Products</h3>

    <form method="get" class="row mb-4">
        <input type="hidden" name="page" value="shop">

        <div class="col-md-4">
            <label>Category</label>
            <select name="category" id="category" class="form-control">
                <option value="">All Categories</option>
                <?php
                $cat_q = "SELECT DISTINCT product_category FROM products_tbl ORDER BY product_category";
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
            <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) . '?page=shop'; ?>" class="btn btn-danger">Reset</a>
        </div>
    </form>

    <div class="row">
        <?php if (mysqli_num_rows($product_res) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($product_res)): ?>
                <div class="col-md-4 d-flex mb-4">
                <form method="post" class="mt-2">
                    <div class="product-card w-100" id="product-<?= $row['id'] ?>">
                        <img src="../admin/<?= htmlspecialchars($row['product_image']) ?>" class="product-image mb-2" alt="Product">
                        <h5><?= htmlspecialchars($row['product_name']) ?></h5>
                        <p><span class="fw-bold">Category:</span> <?= htmlspecialchars($row['product_category']) ?></p>
                        <p><span class="fw-bold">Subcategory:</span> <?= htmlspecialchars($row['product_sub_category']) ?: 'N/A' ?></p>
                        <p><span class="fw-bold">Price:</span> ₱<?= number_format($row['product_price'], 2) ?></p>
                        
                        <div class="d-flex align-items-center justify-content-center text-center mb-2 quantity-control">
                            <button class="btn btn-dark btn-subtract" type="button" onclick="decrement(this, <?= $row['id'] ?>)">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number"
                                class="quantity-input form-control text-center mx-2"
                                value="1"
                                min="1"
                                max="<?= $row['product_quantity'] ?>"
                                style="width: 60px;"
                                data-product-id="<?= $row['id'] ?>"
                                onchange="updateQuantity(this)">
                            <button class="btn btn-dark btn-add" type="button" onclick="increment(this, <?= $row['id'] ?>)">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>

                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <span class="fw-bold me-2">Size:</span> 
                            <button type="button" class="btn btn-outline-danger size-button" name="size" value="S">S</button>
                            <button type="button" class="btn btn-outline-danger size-button" name="size" value="M">M</button>
                            <button type="button" class="btn btn-outline-danger size-button" name="size" value="L">L</button>
                            <button type="button" class="btn btn-outline-danger size-button" name="size" value="XL">XL</button>
                            <button type="button" class="btn btn-outline-danger size-button" name="size" value="2X">2X</button>
                            <button type="button" class="btn btn-outline-danger size-button" name="size" value="3X">3X</button>
                        </div>

                        <div class="d-flex align-items-start justify-content-start mb-1 mt-1 gap-2">
                            <button type="button" class="btn btn-outline-primary" onclick="showCustomSizes(<?= $row['id'] ?>)">Custom</button>
                            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#viewChartExample">
                                View Chart
                            </button>
                        </div>

                      <!--- CUSTOM SIZE INPUTS -->
                      <div id="customSize-<?= $row['id'] ?>" class="mt-2" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-1">
                                    <input type="text" class="form-control custom-size-input" placeholder="Size" name="custom_size">
                                </div>
                                <div class="col-md-6 mb-1">
                                    <input type="text" class="form-control custom-size-input" placeholder="Collar" name="custom_collar">
                                </div>
                                <div class="col-md-6 mb-1">
                                    <input type="text" class="form-control custom-size-input" placeholder="Shoulder" name="custom_shoulder">
                                </div>
                                <div class="col-md-6 mb-1">
                                    <input type="text" class="form-control custom-size-input" placeholder="Chest" name="custom_chest">
                                </div>
                                <div class="col-md-6 mb-1">
                                    <input type="text" class="form-control custom-size-input" placeholder="Waist" name="custom_waist">
                                </div>
                                <div class="col-md-6 mb-1">
                                    <input type="text" class="form-control custom-size-input" placeholder="Hip" name="custom_hip">
                                </div>
                                <div class="col-md-6 mb-1">
                                    <input type="text" class="form-control custom-size-input" placeholder="Cuff" name="custom_cuff">
                                </div>
                                <div class="col-md-6 mb-1">
                                    <input type="text" class="form-control custom-size-input" placeholder="Sleeve Length" name="sleeve_length">
                                </div>
                                <div class="col-md-6 mb-1">
                                    <input type="text" class="form-control custom-size-input" placeholder="Armhole" name="armhole">
                                </div>
                                <div class="col-md-6 mb-1">
                                    <input type="text" class="form-control custom-size-input" placeholder="Back Length" name="back_length">
                                </div>
                            </div>
                        </div>

                        
                            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="quantity" id="hidden-quantity-<?= $row['id'] ?>" value="1">
                            <input type="hidden" name="size" id="hidden-size-<?= $row['id'] ?>" value="">
                            <input type="hidden" name="custom_sizes" id="hidden-custom-sizes-<?= $row['id'] ?>" value="">
                            <input type="hidden" name="add_to_cart" value="1">
                            <button type="submit" class="btn btn-warning btn-sm mt-auto fw-bold">
                                <i class="bi bi-cart"></i> Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-warning">No products found.</div></div>
        <?php endif; ?>
    </div>

    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-end">
            <li class="page-item<?= $pageno <= 1 ? ' disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pageno' => $pageno - 1])) ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item<?= $i === $pageno ? ' active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pageno' => $i])) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item<?= $pageno >= $total_pages ? ' disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pageno' => $pageno + 1])) ?>">Next</a>
            </li>
        </ul>
    </nav>
</div>

<script>
    function showCustomSizes(productId) {
        var customSizeDiv = document.getElementById("customSize-" + productId);
        if (customSizeDiv.style.display === "none") {
            customSizeDiv.style.display = "block";
        } else {
            customSizeDiv.style.display = "none";
        }
    }
    
    function increment(btn, productId) {
        const input = btn.parentElement.querySelector('.quantity-input');
        let val = parseInt(input.value);
        let max = parseInt(input.max) || 9999; 
        if (val < max) {
            input.value = val + 1;
            updateQuantity(input);
        }
    }

    function decrement(btn, productId) {
        const input = btn.parentElement.querySelector('.quantity-input');
        let val = parseInt(input.value);
        let min = parseInt(input.min) || 1;
        if (val > min) {
            input.value = val - 1;
            updateQuantity(input);
        }
    }
    
    function updateQuantity(input) {
        const productId = input.dataset.productId;
        document.getElementById('hidden-quantity-' + productId).value = input.value;
    }

    // Handle size button selection
    document.querySelectorAll('.size-button').forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons in the same group
            this.parentElement.querySelectorAll('.size-button').forEach(btn => {
                btn.classList.remove('active');
            });
            // Add active class to the clicked button
            this.classList.add('active');
            
            // Update hidden size input
            const productId = this.closest('.product-card').id.replace('product-', '');
            document.getElementById('hidden-size-' + productId).value = this.value;
        });
    });

    // Handle custom size inputs
    document.querySelectorAll('.custom-size-input').forEach(input => {
        input.addEventListener('input', function() {
            const productId = this.closest('.product-card').id.replace('product-', '');
            const customSizes = {};
            
            // Collect all custom size values
            this.closest('.product-card').querySelectorAll('.custom-size-input').forEach(customInput => {
                customSizes[customInput.name] = customInput.value;
            });
            
            // Update hidden custom sizes input
            document.getElementById('hidden-custom-sizes-' + productId).value = JSON.stringify(customSizes);
        });
    });
</script>

</body>
</html>