<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>test</title>
</head>
<body>
    <?php
    $where_condition = "r_rate_value > 4";

    // 包含data_variable.php檔案
    include("get_data.php");

    // 迭代$restaurants數組並顯示餐廳信息
    foreach ($restaurants as $restaurant) {
        echo "restaurant name: " . $restaurant['r_name'] . "<br>";
        echo "restaurant value rating: " . $restaurant['r_rate_value'] . "<br>"; 
        echo "<hr>"; // 用於分隔不同餐廳的信息
    }
    ?>
</body>
</html>