<?php
ob_start(); // 啟用輸出緩衝，避免多餘的輸出
header('Content-Type: application/json; charset=utf-8');

// 顯示所有錯誤
ini_set('display_errors', 1);
error_reporting(E_ALL);


// 使用Nominatim API獲取地理座標
function getCoordinates($location) {
    $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($location . ", 高雄市") . "&format=json&addressdetails=1&limit=1";
    $opts = [
        "http" => [
            "header" => "User-Agent: MyAppName/1.0 (your.email@example.com)"
        ]
    ];
    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);

    if ($response === false) {
        error_log('Error fetching coordinates.');
        echo json_encode(['error' => 'Error fetching coordinates.']);
        return null;
    }
    
    if (empty($data)) {
        error_log('No data returned from coordinates API.');
        echo json_encode(['error' => 'No data returned from coordinates API.']);
        return null;
    }
    
    $address = $data[0]['address'];
    if (isset($address['city']) && $address['city'] === '高雄市') {
        return [
            'lat' => (float)$data[0]['lat'],
            'lon' => (float)$data[0]['lon']
        ];
    }
    
    error_log('Coordinates not found for the specified location.');
    echo json_encode(['error' => 'Coordinates not found for the specified location.']);
    return null;
}

// Haversine公式計算距離
function haversine($lon1, $lat1, $lon2, $lat2) {
    $R = 6371; // 地球半徑，單位：公里
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $d = $R * $c;
    return $d;
}

// 捕獲錯誤並返回JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode(['error' => "$errstr in $errfile on line $errline"]);
    exit;
});

// 使用者輸入的查詢參數
$query = isset($_GET['query']) ? $_GET['query'] : '';
$query = trim($query); // 去除前後空白

$query_parts = preg_split('/\s+/', $query); // 根據空白字符分割

if (count($query_parts) < 1) {
    echo json_encode(['error' => '請輸入地名和/或料理類型']);
    exit;
}

$location = $query_parts[0];
$cuisine = count($query_parts) > 1 ? $query_parts[1] : null;

// 如果有指定地點，則獲取地理座標
$coordinates = null;
if ($location) {
    $coordinates = getCoordinates($location);

    if (!$coordinates) {
        echo json_encode(['error' => '無法獲取地理位置']);
        exit;
    }
}

$center_lat = $coordinates ? $coordinates['lat'] : null;
$center_lon = $coordinates ? $coordinates['lon'] : null;

// 連接MySQL數據庫
$dsn = 'mysql:host=localhost;dbname=foodee;charset=utf8';
$username = 'root'; // 將'root'替換為您的用戶名
$password = ''; // 將''替換為您的密碼

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => '無法連接到數據庫: ' . $e->getMessage()]);
    exit;
}

// 查詢數據庫中的餐廳數據
$sql = 'SELECT * FROM detail';
$stmt = $pdo->query($sql);
$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 篩選和排序餐廳
$filtered_restaurants = [];
foreach ($restaurants as $restaurant) {
    $restaurant_cuisines = explode(',', $restaurant['cuisine']);
    $isCuisineMatch = $cuisine ? in_array($cuisine, $restaurant_cuisines) : false;
    $isLocationMatch = $coordinates ? haversine($restaurant['r_longitude'], $restaurant['r_latitude'], $center_lon, $center_lat) <= 2 : false;

    if ($isCuisineMatch || $isLocationMatch) {
        if ($isLocationMatch) {
            $restaurant['distance'] = haversine($restaurant['r_longitude'], $restaurant['r_latitude'], $center_lon, $center_lat);
        }
        $filtered_restaurants[] = $restaurant;
    }
}

// 按距離排序，將匹配的料理類型排在最前面
usort($filtered_restaurants, function($a, $b) use ($cuisine) {
    if ($cuisine && in_array($cuisine, explode(',', $a['cuisine'])) && !in_array($cuisine, explode(',', $b['cuisine']))) {
        return -1;
    }
    if ($cuisine && !in_array($cuisine, explode(',', $a['cuisine'])) && in_array($cuisine, explode(',', $b['cuisine']))) {
        return 1;
    }
    if (isset($a['distance']) && isset($b['distance'])) {
        return $a['distance'] - $b['distance'];
    }
    return 0;
});

// 返回結果
echo json_encode(array_map(function($restaurant) {
    $result = [
        'r_id' => $restaurant['r_id'],
        'r_name' => $restaurant['r_name']
    ];
    if (isset($restaurant['distance'])) {
        $result['distance'] = $restaurant['distance'];
    }
    return $result;
}, $filtered_restaurants));
ob_end_flush(); // 刷新並發送所有緩衝輸出
?>