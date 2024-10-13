<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// 數據庫設置
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "foodee";

// 建立數據庫連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// 初始 SQL 查詢
$sql = "
SELECT 
    additional.*, 
    detail.*, 
    photos.*, 
    review.*
FROM 
    additional
JOIN 
    detail ON additional.r_id = detail.r_id
JOIN 
    photos ON additional.r_id = photos.r_id
JOIN 
    review ON additional.r_id = review.r_id
WHERE 1=1
";


if (isset($_GET['r_ids']) && !empty($_GET['r_ids'])) {  // 仅在 r_ids 参数存在且不为空时执行
    $rIds = explode(',', $_GET['r_ids']);
    $rIds = array_map('intval', $rIds); // 确保 r_id 是整数类型
    $rIdConditions = implode(',', $rIds);
    $sql .= " AND additional.r_id IN ($rIdConditions)";
}

// 篩選條件 - 停車場
if (isset($_GET['hasParking'])) {
    $hasParking = intval($_GET['hasParking']);
    if ($hasParking === 1) {
        $sql .= " AND additional.r_has_parking = 1";
    }
}
// 篩選條件 - 用餐時間

if (isset($_GET['times'])) {
    $timesList = explode(',', $_GET['times']);
    
    // 构建 SQL 条件
    $timeConditions = array_map(function($time) use ($conn) {
        if ($time === '無限制') {
            // 对于"無限制"，排除时间限制
            return "(additional.r_time_low IS NULL OR additional.r_time_low = '')";
        } else {
            // 否则根据具体的时间筛选
            return "additional.r_time_low = " . intval($time);
        }
    }, $timesList);

    // 将所有条件合并到 SQL 查询中
    $sql .= " AND (" . implode(' OR ', $timeConditions) . ")";
}

// 篩選條件 - 用餐時間限制
if (isset($_GET['selected_time'])) {
    $selectedTime = $_GET['selected_time'];
    
    // 如果選擇的是 "無限制"，篩選 r_time_low 為空或者為 NULL 的項目
    if ($selectedTime === '無限制') {
        $sql .= " AND (additional.r_time_low IS NULL OR additional.r_time_low = '')";
    } else {
        // 如果選擇了具體的時間值，篩選該時間
        $selectedTime = intval($selectedTime);
        $sql .= " AND additional.r_time_low = $selectedTime";
    }
}

// 篩選條件 - 評分
if (isset($_GET['ratings'])) {
    $ratings = explode(',', $_GET['ratings']);
    $ratings = array_map('floatval', $ratings);
    $ratingConditions = implode(' OR ', array_map(function($rating) {
        return "detail.r_rating = $rating";
    }, $ratings));
    $sql .= " AND ($ratingConditions)";
}
// 篩選條件 - 氣氛
if (isset($_GET['vibes'])) {
    $vibeList = explode(',', $_GET['vibes']);
    $vibeConditions = array_map(function($vibe) use ($conn) {
        return "additional.r_vibe LIKE '%" . $conn->real_escape_string($vibe) . "%'";
    }, $vibeList);
    $sql .= " AND (" . implode(' OR ', $vibeConditions) . ")";
}
// 篩選條件 - 價格
if (isset($_GET['min_price']) && isset($_GET['max_price'])) {
    $minPrice = intval($_GET['min_price']);
    $maxPrice = intval($_GET['max_price']);
    $sql .= " AND additional.r_price_low BETWEEN $minPrice AND $maxPrice";
}

// 篩選營業時間 - 根據 day 進行篩選
if (isset($_GET['selectedDays'])) {
    $selectedDays = explode(',', $_GET['selectedDays']);
    $selectedDays = array_map('ucfirst', $selectedDays); // 確保天數首字母大寫

    $dayConditions = implode(' OR ', array_map(function($day) {
        return "r_hours_weekday LIKE '%$day%'";
    }, $selectedDays));

    $sql .= " AND ($dayConditions)";
}


$result = $conn->query($sql);

if ($result === false) {
    echo json_encode(["error" => $conn->error]);
    $conn->close();
    exit;
}

$data = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();
echo json_encode($data);
?>