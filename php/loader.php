<?php 
 
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    require_once '../config/database.php';

    $conn = dbConnection();
    if (!$conn) {
        die("Conn failed: " . mysqli_connect_error());
    }