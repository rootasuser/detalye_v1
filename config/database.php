<?php 

    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'detalye');

    function dbConnection() {

        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Conn failed: " . $conn->connect_error);
        }

        if (!$conn->set_charset("utf8")) {
            die("Err loading character set utf8: " . $conn->error);
        }
        return $conn;
    }

    ?>