<?php

// 設定 CORS 頭
header('Access-Control-Allow-Origin: *'); // 允許所有域名請求，可以根據需求指定具體域名
header('Content-Type: application/json');


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "foodee";

// 建立連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 使用 JOIN 查詢多個表格
$sql = "
SELECT 
    additional.*, 
    detail.*
FROM 
    additional
JOIN 
    detail ON additional.r_id = detail.r_id;
";

$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($data);
?>
