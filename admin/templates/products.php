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
    header('Content-Type: application/json');
    ob_end_clean();
    echo json_encode(['error' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

// Route requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'get_product') {
    handleGetProduct();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['productName'])) {
        handleAddProduct();
    } elseif (isset($_POST['editProductId'])) {
        handleEditProduct();
    } elseif (isset($_POST['deleteProductId'])) {
        handleDeleteProduct();
    }
}

// Handle edit button clicks
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $id = intval($_GET['id']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM products_tbl WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    if ($product) {
        showEditForm($product);
    } else {
        showAlert("Error", "Product not found", "error");
    }
    exit;
}

function handleGetProduct() {
    global $conn;
    // Clear any previous output buffering to ensure clean JSON response
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');

    try {
        $id = intval($_GET['id']);
        $stmt = mysqli_prepare($conn, "SELECT * FROM products_tbl WHERE id = ?");
        if (!$stmt) {
            echo json_encode(['error' => 'Database prepare failed: ' . mysqli_error($conn)]);
            exit;
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (!mysqli_stmt_execute($stmt)) {
            echo json_encode(['error' => 'Database execute failed: ' . mysqli_stmt_error($stmt)]);
            exit;
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        
        if ($product) {
            echo json_encode($product);
        } else {
            echo json_encode(['error' => 'Product not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

function handleAddProduct() {
    global $conn;
    // Collect fields
    $fields = [
        'productName', 'productCategory', 'productSubCategory', 'productQuantity',
        'productPrice', 'productDescription', 'productSize'
    ];
    foreach ($fields as $field) {
        $$field = $_POST[$field] ?? '';
    }

    // Handle image upload
    if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] === UPLOAD_ERR_OK) {
        $dir = 'uploads/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $ext = pathinfo($_FILES['productImage']['name'], PATHINFO_EXTENSION);
        $newName = uniqid('IMG_', true) . '.' . $ext;
        $imagePath = $dir . $newName;
        if (!move_uploaded_file($_FILES['productImage']['tmp_name'], $imagePath)) {
            showAlert('Error', 'Failed to move uploaded image.', 'error');
            exit;
        }
    } else {
        showAlert('Error', 'Image upload failed.', 'error');
        exit;
    }

    // Insert SQL
    $sql = "INSERT INTO products_tbl (
        product_image, product_name, product_category, product_sub_category,
        product_quantity, product_price, product_description, product_size
    ) VALUES (?,?,?,?,?,?,?,?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        'ssssidss',
        $imagePath,
        $productName,
        $productCategory,
        $productSubCategory,
        $productQuantity,
        $productPrice,
        $productDescription,
        $productSize
    );

    if (mysqli_stmt_execute($stmt)) {
        showAlert('Success', 'Product added successfully!', 'success', true);
    } else {
        showAlert('Error', 'Failed to add product: ' . mysqli_stmt_error($stmt), 'error');
    }
    mysqli_stmt_close($stmt);
}

function handleEditProduct() {
    global $conn;
    $id = intval($_POST['editProductId']);
    // Collect fields except ID
    $fields = [
        'editProductName', 'editProductCategory', 'editProductSubCategory', 'editProductQuantity',
        'editProductPrice', 'editProductDescription', 'editProductSize'
    ];
    foreach ($fields as $f) {
        $$f = $_POST[$f] ?? '';
    }

    // Optional image
    $imagePath = null;
    if (!empty($_FILES['editProductImage']['tmp_name']) && $_FILES['editProductImage']['error'] === UPLOAD_ERR_OK) {
        $dir = 'uploads/'; if (!is_dir($dir)) mkdir($dir, 0777, true);
        $ext = pathinfo($_FILES['editProductImage']['name'], PATHINFO_EXTENSION);
        $newName = uniqid('IMG_', true) . '.' . $ext;
        $imagePath = $dir . $newName;
        move_uploaded_file($_FILES['editProductImage']['tmp_name'], $imagePath);
    }

    // Build update SQL
    $sql = "UPDATE products_tbl SET
        product_name=?, product_category=?, product_sub_category=?,
        product_quantity=?, product_price=?, product_description=?, product_size=?";

    $types = 'sssidss';
    $params = [
        $editProductName, $editProductCategory, $editProductSubCategory,
        $editProductQuantity, $editProductPrice, $editProductDescription, $editProductSize
    ];
    if ($imagePath) {
        $sql .= ', product_image=?';
        $types .= 's';
        $params[] = $imagePath;
    }
    $sql .= ' WHERE id=?';
    $types .= 'i';
    $params[] = $id;

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (mysqli_stmt_execute($stmt)) {
        showAlert('Success', 'Product updated successfully!', 'success', true);
    } else {
        showAlert('Error', 'Failed to update product: ' . mysqli_stmt_error($stmt), 'error');
    }
    mysqli_stmt_close($stmt);
}

function handleDeleteProduct() {
    global $conn;
    $id = intval($_POST['deleteProductId']);
    $stmt = mysqli_prepare($conn, 'DELETE FROM products_tbl WHERE id=?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        showAlert('Success', 'Product deleted successfully!', 'success', true);
    } else {
        showAlert('Error', 'Failed to delete product: ' . mysqli_stmt_error($stmt), 'error');
    }
    mysqli_stmt_close($stmt);
}

function showAlert($title, $text, $icon, $reload = false) {
  echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
  echo "<script>
      Swal.fire({
          title: '" . addslashes($title) . "',
          text: '" . addslashes($text) . "',
          icon: '" . $icon . "',
          allowOutsideClick: false,
          showConfirmButton: " . ($reload ? "false" : "true") . "
      })";
  
  if ($reload) {
      echo ".then(function() {
          window.location.reload();
      });";
  } else {
      echo ";";
  }
  
  echo "</script>";
}

function showEditForm($product) {
   
    echo "
    <div class='modal fade' id='editProductsModal' tabindex='-1' aria-labelledby='editProductsModalLabel' aria-hidden='true'>
      <div class='modal-dialog modal-xl modal-dialog-scrollable'>
        <div class='modal-content'>
          <form id='editProductForm' method='POST' enctype='multipart/form-data'>
            <input type='hidden' name='editProductId' value='" . htmlspecialchars($product['id']) . "'>
            <div class='modal-header'>
              <h5 class='modal-title' id='editProductsModalLabel'>Edit Product</h5>
              <button type='button' class='close' data-dismiss='modal' aria-label='Close'>X</button>
            </div>
            <div class='modal-body'>
              <div class='row g-3'>
                <!-- Product Image -->
                <div class='col-md-6'>
                  <label for='editProductImage' class='form-label'>Product Image</label>
                  <input class='form-control' type='file' name='editProductImage' id='editProductImage'>
                  <input type='hidden' name='currentImage' value='" . htmlspecialchars($product['product_image']) . "'>
                  <img src='" . htmlspecialchars($product['product_image']) . "' width='80' style='margin-top: 10px;'>
                </div>
                <!-- Product Name -->
                <div class='col-md-6'>
                  <label for='editProductName' class='form-label'>Product Name</label>
                  <input type='text' class='form-control' name='editProductName' value='" . htmlspecialchars($product['product_name']) . "' required>
                </div>
                <!-- Category -->
                <div class='col-md-6'>
                  <label for='editProductCategory' class='form-label'>Product Category</label>
                  <input type='text' class='form-control' name='editProductCategory' value='" . htmlspecialchars($product['product_category']) . "' required>
                </div>
                <!-- Sub Category -->
                <div class='col-md-6'>
                  <label for='editProductSubCategory' class='form-label'>Product Sub Category</label>
                  <input type='text' class='form-control' name='editProductSubCategory' value='" . htmlspecialchars($product['product_sub_category']) . "'>
                </div>
                <!-- Quantity -->
                <div class='col-md-6'>
                  <label for='editProductQuantity' class='form-label'>Quantity</label>
                  <input type='number' class='form-control' name='editProductQuantity' value='" . htmlspecialchars($product['product_quantity']) . "' required>
                </div>
                <!-- Price -->
                <div class='col-md-6'>
                  <label for='editProductPrice' class='form-label'>Price</label>
                  <input type='number' class='form-control' name='editProductPrice' value='" . htmlspecialchars($product['product_price']) . "' step='0.01' required>
                </div>
                <!-- Description -->
                <div class='col-12'>
                  <label for='editProductDescription' class='form-label'>Description</label>
                  <textarea class='form-control' name='editProductDescription' rows='2'>" . htmlspecialchars($product['product_description']) . "</textarea>
                </div>
                <!-- Size -->
                <div class='col-md-6 d-none'>
                  <label for='editProductSize' class='form-label'>Size</label>
                  <input type='text' class='form-control' name='editProductSize' value='" . htmlspecialchars($product['product_size']) . "'>
                </div>
              </div>
            </div>
            <div class='modal-footer'>
              <button type='submit' class='btn btn-primary'>Update Product</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script>
      $(document).ready(function() {
          $('#editProductsModal').modal('show');
      });
    </script>";
}
?>

<style>
  th, td {
    text-align: start;
    color:#000;
    text-wrap: nowrap;
  }
</style>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

<div class="container-fluid mt-5 p-0 justify-content-center align-items-center">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Products</h4>
    <button class="btn" style="background-color: #000; color: #fff;" data-toggle="modal" data-target="#addProductsModal"><i class="bi bi-plus"></i> New</button>
  </div>

  <div class="card shadow-sm p-3 bg-white rounded border-0">

  <div class="d-flex align-items-start justify-content-start">
  <input type="text" class="form-control w-100 w-md-25" id="searchProducts" placeholder="Search product here...">
</div>

    <div class="card-body">
      <div class="table-responsive">
        <table id="myTable" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Image</th>
              <th>Name</th>
              <th>Category</th>
              <th>Subcategory</th>
              <th>Quantity</th>
              <th>Price</th>
              <th>Description</th>
              <th class="d-none">Size</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="searchProduct">
            <?php
            $sql = "SELECT * FROM products_tbl";
            $result = mysqli_query($conn, $sql);
            $count = 1;

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>{$count}</td>";
                echo "<td><img src='" . htmlspecialchars($row['product_image']) . "' width='80' onerror=\"this.src='uploads/placeholder.jpg';\"></td>";
                echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['product_category']) . "</td>";
                echo "<td>" . htmlspecialchars($row['product_sub_category']) . "</td>";
                echo "<td>{$row['product_quantity']}</td>";
                echo "<td>{$row['product_price']}</td>";
                echo "<td title='" . htmlspecialchars($row['product_description']) . "'>" . htmlspecialchars(substr($row['product_description'], 0, 30)) . "...</td>";
                echo "<td class='d-none'>" . htmlspecialchars($row['product_size']) . "</td>";
                echo "<td>
                        <div class='d-flex align-items-center justify-content-center'>
                        <button class='btn btn-sm btn-primary edit-btn mr-1' data-id='{$row['id']}'>Edit</button>
                        <button class='btn btn-sm btn-danger delete-btn' data-id='{$row['id']}'>Delete</button>
                        </div>
                      </td>";
                echo "</tr>";
                $count++;
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductsModal" tabindex="-1" aria-labelledby="addProductsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form id="addProductForm" method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="addProductsModalLabel">Add New Product</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">X</button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <!-- Product Image -->
            <div class="col-md-6">
              <label for="productImage" class="form-label">Product Image</label>
              <input class="form-control" type="file" name="productImage" id="productImage" required>
            </div>

            <!-- Product Name -->
            <div class="col-md-6">
              <label for="productName" class="form-label">Product Name</label>
              <input type="text" class="form-control" name="productName" id="productName" required>
            </div>

            <!-- Category -->
            <div class="col-md-6">
              <label for="productCategory" class="form-label">Product Main Category</label>
              <input type="text" class="form-control" name="productCategory" id="productCategory" required>
            </div>

            <!-- Sub Category -->
            <div class="col-md-6">
              <label for="productSubCategory" class="form-label">Product Sub Category</label>
              <input type="text" class="form-control" name="productSubCategory" id="productSubCategory">
            </div>

            <!-- Quantity -->
            <div class="col-md-6">
              <label for="productQuantity" class="form-label">Quantity</label>
              <input type="number" class="form-control" name="productQuantity" id="productQuantity" required>
            </div>

            <!-- Price -->
            <div class="col-md-6">
              <label for="productPrice" class="form-label">Price</label>
              <input type="number" class="form-control" name="productPrice" id="productPrice" step="0.01" required>
            </div>

            <!-- Description -->
            <div class="col-12">
              <label for="productDescription" class="form-label">Description</label>
              <textarea class="form-control" name="productDescription" id="productDescription" rows="2"></textarea>
            </div>

            <!-- Size -->
            <div class="col-md-6 d-none">
              <label for="productSize" class="form-label">Size</label>
              <input type="text" class="form-control" name="productSize" id="productSize">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Add Product</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchProducts');
    const tableBody = document.getElementById('searchProduct');
    const rows = Array.from(tableBody.getElementsByTagName('tr'));

    searchInput.addEventListener('input', function () {
      const searchTerm = searchInput.value.toLowerCase();

      rows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        row.style.display = rowText.includes(searchTerm) ? '' : 'none';
      });
    });
  });


document.addEventListener('DOMContentLoaded', function() {
    // Edit button click handler
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
          
            const currentUrl = window.location.pathname.split('/').pop();
            let fetchUrl;
            
            if (currentUrl === 'index.php') {
          
                fetchUrl = 'index.php?page=products&action=get_product&id=' + productId;
            } else {
            
                fetchUrl = 'products.php?action=get_product&id=' + productId;
            }
            

            fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(product => {
                    if (product.error) {
                        throw new Error(product.error);
                    }
                    
                    if (!product.id) {
                        throw new Error('Product not found or invalid data returned');
                    }
                    
                    // Create and show edit modal
                    const editModal = document.createElement('div');
                    editModal.innerHTML = `
                        <div class="modal fade" id="editProductsModal" tabindex="-1" aria-labelledby="editProductsModalLabel" aria-hidden="true">
                          <div class="modal-dialog modal-xl modal-dialog-scrollable">
                            <div class="modal-content">
                              <form id="editProductForm" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="editProductId" value="${product.id}">
                                <div class="modal-header">
                                  <h5 class="modal-title" id="editProductsModalLabel">Edit Product</h5>
                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">X</button>
                                </div>
                                <div class="modal-body">
                                  <div class="row g-3">
                                    <!-- Product Image -->
                                    <div class="col-md-6">
                                      <label for="editProductImage" class="form-label">Product Image</label>
                                      <input class="form-control" type="file" name="editProductImage" id="editProductImage">
                                      <input type="hidden" name="currentImage" value="${product.product_image || ''}">
                                      <img src="${product.product_image || ''}" width="80" style="margin-top: 10px;" onerror="this.src='uploads/placeholder.jpg';">
                                    </div>
                                    <!-- Product Name -->
                                    <div class="col-md-6">
                                      <label for="editProductName" class="form-label">Product Name</label>
                                      <input type="text" class="form-control" name="editProductName" value="${product.product_name || ''}" required>
                                    </div>
                                    <!-- Category -->
                                    <div class="col-md-6">
                                      <label for="editProductCategory" class="form-label">Product Category</label>
                                      <input type="text" class="form-control" name="editProductCategory" value="${product.product_category || ''}" required>
                                    </div>
                                    <!-- Sub Category -->
                                    <div class="col-md-6">
                                      <label for="editProductSubCategory" class="form-label">Product Sub Category</label>
                                      <input type="text" class="form-control" name="editProductSubCategory" value="${product.product_sub_category || ''}">
                                    </div>
                                    <!-- Quantity -->
                                    <div class="col-md-6">
                                      <label for="editProductQuantity" class="form-label">Quantity</label>
                                      <input type="number" class="form-control" name="editProductQuantity" value="${product.product_quantity || 0}" required>
                                    </div>
                                    <!-- Price -->
                                    <div class="col-md-6">
                                      <label for="editProductPrice" class="form-label">Price</label>
                                      <input type="number" class="form-control" name="editProductPrice" value="${product.product_price || 0.00}" step="0.01" required>
                                    </div>
                                    <!-- Description -->
                                    <div class="col-12">
                                      <label for="editProductDescription" class="form-label">Description</label>
                                      <textarea class="form-control" name="editProductDescription" rows="2">${product.product_description || ''}</textarea>
                                    </div>
                                    <!-- Size -->
                                    <div class="col-md-6">
                                      <label for="editProductSize" class="form-label">Size</label>
                                      <input type="text" class="form-control" name="editProductSize" value="${product.product_size || ''}">
                                    </div>
                                  </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="submit" class="btn btn-primary">Update Product</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                    `;
                    document.body.appendChild(editModal);
                    
                    // Show the modal
                    $('#editProductsModal').modal('show');
                    
                    // Clean up when modal is closed
                    $('#editProductsModal').on('hidden.bs.modal', function () {
                        $(editModal).remove();
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to fetch product data: ' + error.message,
                        icon: 'error'
                    });
                });
        });
    });

    // Delete button click handler
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit delete form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'deleteProductId';
                    input.value = productId;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
});
</script>