<?php
    header('Content-Type: application/json');

    include("connect.php");

    // 聯合查詢，使用傳遞的查詢條件
    $sql = "
        SELECT *
        FROM 
            mrt_lrt";
    
    $result = $conn->query($sql);

    $transportation = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $transportation[] = $row;
        }
    } else {
        echo json_encode([]);
        exit();
    }

    echo json_encode($transportation);

    $conn->close();
?>

