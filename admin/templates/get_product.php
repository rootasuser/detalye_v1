<?php
include_once '../config/database.php';

header('Content-Type: application/json');

$conn = dbConnection();
if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(["error" => "Invalid product ID"]);
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM products_tbl WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $product = mysqli_fetch_assoc($result);
    echo json_encode($product);
} else {
    echo json_encode(["error" => "Product not found"]);
}

mysqli_stmt_close($stmt);
?>