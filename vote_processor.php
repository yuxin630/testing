<?php
// 数据库连接设置
$host = 'localhost';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'foodee';

// 建立数据库连接
$link = mysqli_connect($host, $dbuser, $dbpassword, $dbname);

if (!$link) {
    die(json_encode(["status" => "error", "message" => "数据库连接失败: " . mysqli_connect_error()]));
}

$response = ["status" => "success", "message" => "投票成功"];

if (isset($_POST['votedRestaurants'])) {
    $votedRestaurants = explode(',', $_POST['votedRestaurants']);
    
    foreach ($votedRestaurants as $r_id) {
        $r_id = intval($r_id); // 将r_id转换为整数，确保安全性

        // 获取餐厅名称
        $r_name_query = "SELECT r_name FROM detail WHERE r_id = $r_id";
        $r_name_result = mysqli_query($link, $r_name_query);
        
        if ($r_name_result && mysqli_num_rows($r_name_result) > 0) {
            $r_name_row = mysqli_fetch_assoc($r_name_result);
            $r_name = mysqli_real_escape_string($link, $r_name_row['r_name']); // 对餐厅名称进行转义

            // 检查该餐厅是否已经被投过票
            $check_query = "SELECT * FROM vote WHERE r_id = $r_id";
            $check_result = mysqli_query($link, $check_query);

            if ($check_result && mysqli_num_rows($check_result) > 0) {
                // 如果已经存在记录，则更新 vote 字段
                $update_query = "UPDATE vote SET vote = vote + 1 WHERE r_id = $r_id";
                if (!mysqli_query($link, $update_query)) {
                    $response["status"] = "error";
                    $response["message"] = "更新投票信息时出错: " . mysqli_error($link);
                    break;
                }
            } else {
                // 如果不存在记录，则插入新的记录
                $insert_query = "INSERT INTO vote (r_id, r_name, vote) VALUES ($r_id, '$r_name', 1)";
                if (!mysqli_query($link, $insert_query)) {
                    $response["status"] = "error";
                    $response["message"] = "插入投票信息时出错: " . mysqli_error($link);
                    break;
                }
            }
        } else {
            $response["status"] = "error";
            $response["message"] = "找不到餐厅ID: " . $r_id;
            break;
        }
    }
} else {
    $response["status"] = "error";
    $response["message"] = "未接收到投票的餐厅数据";
}

mysqli_close($link);

// 输出JSON格式的响应
echo json_encode($response);
?>
