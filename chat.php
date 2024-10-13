<?php
ob_start(); // 開啟輸出緩衝
header('Content-Type: text/html; charset=UTF-8');

// 資料庫連接設置
$host = 'localhost';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'foodee';

// 建立資料庫連接
$link = mysqli_connect($host, $dbuser, $dbpassword, $dbname);

// 初始化變數
$all_comments = [];
$restaurant_names = [];

if ($link) {
    mysqli_query($link, 'SET NAMES utf8');

    // 從網址參數中獲取餐廳ID
    $restaurant_ids = [];
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_GET["r_id$i"])) {
            $restaurant_ids[] = intval($_GET["r_id$i"]);
        }
    }

    // 查詢餐廳名稱
    if ($restaurant_ids) {
        $ids = implode(",", $restaurant_ids);
        $query = "SELECT r_id, r_name FROM detail WHERE r_id IN ($ids)";
        $result = mysqli_query($link, $query);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $restaurant_names[$row['r_id']] = $row['r_name'];
            }
        } else {
            echo "Error retrieving restaurant names: " . mysqli_error($link);
        }
    }

    // 處理表單提交
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $r_id = intval($_POST['r_id']);
        $r_name = mysqli_real_escape_string($link, $_POST['r_name']);
        $user = mysqli_real_escape_string($link, $_POST['user']);
        $comment = mysqli_real_escape_string($link, $_POST['comment']);

        $query = "INSERT INTO comment (r_id, r_name, user, comment) VALUES ('$r_id', '$r_name', '$user', '$comment')";
        if (mysqli_query($link, $query)) {
            // 留言添加成功，重定向回原頁面以刷新評論區域
            header("Location: ".$_SERVER['REQUEST_URI']);
            exit();
        } else {
            echo "Error: " . mysqli_error($link);
        }
    }

    // 檢索與當前餐廳相關的評論
    if ($restaurant_ids) {
        $ids = implode(",", $restaurant_ids);
        $query = "SELECT * FROM comment WHERE r_id IN ($ids)";
        $result = mysqli_query($link, $query);

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $all_comments[] = $row;
            }
        } else {
            echo "Error retrieving comments: " . mysqli_error($link);
        }
    }

    mysqli_close($link);
} else {
    echo "資料庫連接失敗: " . mysqli_connect_error();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./cellphone.css">
    <link rel="stylesheet" href="../chat/chat.css">
    <title>Chatroom</title>
    <style>
        /* 新增的 CSS */
        .comment-section {
            max-height: 100px; /* 根據需要調整高度 */
            overflow-y: auto; /* 啟用垂直滾動條 */
            border: 1px solid #ddd; /* 可選: 添加邊框 */
            padding: 10px; /* 可選: 添加內邊距 */
            margin-bottom: 20px; /* 可選: 添加底部間距 */
        }

        .comment {
            margin-bottom: 15px; /* 使評論之間有間隔 */
        }

        /* 評論標題的樣式 */
        .comment h4 {
            margin: 0;
            font-size: 1em; /* 根據需要調整字體大小 */
            color: #333; /* 可選: 調整顏色 */
        }

        /* 評論文本的樣式 */
        .comment p {
            margin: 0;
            font-size: 0.9em; /* 根據需要調整字體大小 */
            color: #555; /* 可選: 調整顏色 */
        }
    </style>
</head>

<body>

    <!--<div class="comment-section">
        <//?php if ($all_comments): ?>
            <//?php foreach ($all_comments as $comment): ?>
                <div class="comment">
                    <h4><//?php echo htmlspecialchars($comment['user']); ?> (<?php echo htmlspecialchars($comment['r_name']); ?>)</h4>
                    <p><//?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                </div>
            <//?php endforeach; ?>
        <//?php else: ?>
            <p>No comments yet. Be the first to comment!</p>
        <//?php endif; ?>
    </div>-->

    <div class="comment-form">
        <h2>說說你的想法吧！</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?' . $_SERVER['QUERY_STRING']); ?>" method="post">
            <select name="r_id" onchange="updateRestaurantName(this)">
                <?php foreach ($restaurant_names as $r_id => $r_name): ?>
                    <option value="<?php echo htmlspecialchars($r_id); ?>"><?php echo htmlspecialchars($r_name); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="r_name" id="r_name_input" value="<?php echo htmlspecialchars(reset($restaurant_names)); ?>"> <!-- 默認的餐廳名稱 -->
            <input type="text" name="user" placeholder="你的名字" required>
            <textarea name="comment" placeholder="你的想法..." required></textarea>
            <button type="submit">送出</button>
        </form>
    </div>

    <script>
        function updateRestaurantName(selectElement) {
            const r_name = selectElement.options[selectElement.selectedIndex].text;
            document.getElementById('r_name_input').value = r_name;
        }
    </script>
</body>

</html>
<?php
ob_end_flush();
?>
