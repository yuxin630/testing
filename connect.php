<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "foodee";
    
    // 創建連接
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // 檢查連接
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>