<?php
ob_start(); 
header('Content-Type: text/html; charset=UTF-8');

// 数据库连接设置
$host = 'localhost';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'foodee';

// 建立数据库连接
$link = mysqli_connect($host, $dbuser, $dbpassword, $dbname);

// 初始化变量
$all_restaurant_data = [];
$restaurant_ids = [];
$restaurant_names = [];
$r_ids = [];

if ($link) {
    mysqli_query($link, 'SET NAMES utf8');

    for ($i = 1; $i <= 3; $i++) {
        if (isset($_GET["r_id$i"])) {
            $r_id = intval($_GET["r_id$i"]);
            $restaurant_ids[] = $r_id;
            $r_ids[] = $r_id;

            $query_name = "SELECT r_name FROM detail WHERE r_id = $r_id";
            $result_name = mysqli_query($link, $query_name);

            if ($result_name) {
                $row_name = mysqli_fetch_assoc($result_name);
                $restaurant_names[$r_id] = $row_name['r_name'];
            } else {
                echo "Error in query: " . mysqli_error($link);
                $restaurant_names[$r_id] = 'Unknown';
            }

            $query = "
                SELECT r_name, r_vibe, r_food_dishes, r_price_low, r_price_high, 
                    r_photo_env1, r_photo_env2, r_photo_env3, r_photo_food1, 
                    r_photo_food2, r_photo_food3, r_photo_food4, r_photo_food5, 
                    r_photo_door, r_photo_menu1, r_photo_menu2, r_photo_menu3, 
                    r_has_parking, r_rating, r_time_low
                FROM additional
                WHERE r_id = $r_id";
            $result = mysqli_query($link, $query);

            if ($result) {
                $restaurant_data = mysqli_fetch_assoc($result);
                $all_restaurant_data[$r_id] = $restaurant_data;
            } else {
                echo "查询出错: " . mysqli_error($link);
                $all_restaurant_data[$r_id] = null;
            }
        }
    }
} else {
    echo "数据库连接失败: " . mysqli_connect_error();
}
mysqli_close($link);

// 初始化接收到的變數
$vibe = isset($_GET['vibe']) ? json_decode(urldecode($_GET['vibe']), true) : [];
$food = isset($_GET['food']) ? json_decode(urldecode($_GET['food']), true) : [];
$price = isset($_GET['price']) ? json_decode(urldecode($_GET['price']), true) : [];
$diningTime = isset($_GET['diningTime']) ? json_decode(urldecode($_GET['diningTime']), true) : [];
$parking = isset($_GET['parking']) ? json_decode(urldecode($_GET['parking']), true) : [];
$spider = isset($_GET['spider']) ? json_decode(urldecode($_GET['spider']), true) : [];
$comment = isset($_GET['comment']) ? json_decode(urldecode($_GET['comment']), true) : [];
$openTime = isset($_GET['openTime']) ? json_decode(urldecode($_GET['openTime']), true) : [];

// 傳遞 PHP 變數到 JavaScript
echo "<script>
    const receivedVibe = " . json_encode($vibe) . ";
    const receivedFood = " . json_encode($food) . ";
    const receivedPrice = " . json_encode($price) . ";
    const receivedDiningTime = " . json_encode($diningTime) . ";
    const receivedParking = " . json_encode($parking) . ";
    const receivedSpider = " . json_encode($spider) . ";
    const receivedComment = " . json_encode($comment) . ";
    const receivedOpenTime = " . json_encode($openTime) . ";
</script>";


function renderTags($items, $selectedItems, $r_id, $delimiter) {
    if (!empty($items)) {
        $tags = explode($delimiter, $items);
        foreach ($tags as $tag) {
            $tag = trim($tag);
            $backgroundColor = isset($selectedItems[$r_id]) && in_array($tag, $selectedItems[$r_id]) ? 'background-color: rgba(252, 235, 167, 0.8);' : 'background-color: rgba(224,224,224,0.5);';
            echo "<span style='padding: 5px; margin: 5px; border-radius: 5px; $backgroundColor'>" . htmlspecialchars($tag) . "</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../cellphone/cellphone.css">
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="../map/leaflet_edgeMarker.js"></script>
</head>

<body>

    <div class="gallery-container">
        <?php
        $index = 0;
        function renderGallerySection($r_id, $restaurant_data, $index, $price, $diningTime, $parking) {
            $activeClass = $index === 0 ? 'active' : '';
            echo "<div class='restaurant-section $activeClass' id='restaurant-$r_id'>";
            echo "<div class='image-container'>";
            $image_fields = [
                'r_photo_env1', 'r_photo_env2', 'r_photo_env3', 
                'r_photo_food1', 'r_photo_food2', 'r_photo_food3', 
                'r_photo_food4', 'r_photo_food5', 'r_photo_door', 
                'r_photo_menu1', 'r_photo_menu2', 'r_photo_menu3'
            ];
            foreach ($image_fields as $index => $field) {
                if (!empty($restaurant_data[$field])) {
                    $activeClass = $index === 0 ? 'active' : '';
                    echo "<img src='" . htmlspecialchars($restaurant_data[$field]) . "' class='gallery-img $activeClass' data-category='environment-{$r_id}' data-index='$index' onclick='nextImage(this)' />";
                }
            }
            echo "</div>";

            echo "<div class='button-container'>";
            $colors = [
                "#ffc6df86",  // #FF70AE with 50% opacity
                "#acccff8c",  // #85B4FF with 50% opacity
                "#ffeab089"    // #FFCE47 with 50% opacity
            ];
            $index = 0;
            
            foreach ($GLOBALS['restaurant_names'] as $r_id => $r_name) {
                $color = $colors[$index % count($colors)]; // 根據索引選擇顏色
                echo "<button style='background-color: $color;' onclick='changeRestaurant($r_id);'>" . htmlspecialchars($r_name) . "</button>";
                $index++;
            }
            
            echo "</div>";            

        ?>
            <div id="restaurant-info" class="toggle-content active">
                <?php
                echo "<div class='restaurant-name'>";
                echo "<div>" . htmlspecialchars($restaurant_data['r_name']) . "</div>";
                echo "</div>";

                if (isset($restaurant_data['r_rating'])) {
                    $rating = floatval($restaurant_data['r_rating']);
                    $fullStars = floor($rating);
                    $halfStar = ($rating - $fullStars) >= 0.5;
                    echo "<div class='star-rating'>";
                    for ($i = 0; $i < 5; $i++) {
                        if ($i < $fullStars) {
                            echo "<img src='full_star.png' alt='Full Star'>";
                        } elseif ($i == $fullStars && $halfStar) {
                            echo "<img src='half_star.png' alt='Half Star'>";
                        }
                    }
                    echo "</div>";
                }

                echo "<div class='info-row'>";
                $parkingTagClass = isset($parking[$r_id]) && $parking[$r_id] ? 'background-color: rgba(252, 235, 167, 0.8);' : 'background-color: rgba(224,224,224,0.5);';
                if (isset($restaurant_data['r_has_parking'])) {
                    $parkingImage = $restaurant_data['r_has_parking'] == 1 ? 'parking.png' : 'no_parking.png';
                    echo "<div class='parking-tag' style='display: inline-block; $parkingTagClass'><img src='$parkingImage' alt='Parking Info' width='20px'></div>";
                }

                $diningTimeTagClass = isset($diningTime[$r_id]) && $diningTime[$r_id] ? 'background-color: rgba(252, 235, 167, 0.8);' : 'background-color: rgba(224,224,224,0.5);';
                if (!empty($restaurant_data['r_time_low'])) {
                    echo "<div class='dining-time-tag' style='display: inline-block; $diningTimeTagClass'>用餐時間: " . htmlspecialchars($restaurant_data['r_time_low']) . "</div>";
                } else {
                    echo "<div class='dining-time-tag' style='display: inline-block; $diningTimeTagClass'>無用餐時間限制</div>";
                }
                
                $priceTagClass = isset($price[$r_id]) && $price[$r_id] ? 'background-color: rgba(252, 235, 167, 0.8);' : 'background-color:rgba(224, 224, 224, 0.5);';
                if (!empty($restaurant_data['r_price_low']) && !empty($restaurant_data['r_price_high'])) {
                    echo "<div class='price-tag' style='$priceTagClass'>$" . htmlspecialchars($restaurant_data['r_price_low']) . " ~ $" . htmlspecialchars($restaurant_data['r_price_high']) . "</div>";
                }
                echo "</div>";

                echo "<div class='vibe-tags'>";
                if (!empty($restaurant_data['r_vibe'])) {
                    renderTags($restaurant_data['r_vibe'], $GLOBALS['vibe'], $r_id, '，');
                }
                echo "</div>";

                echo "<div class='food-tags'>";
                if (!empty($restaurant_data['r_food_dishes'])) {
                    renderTags($restaurant_data['r_food_dishes'], $GLOBALS['food'], $r_id, '、');
                }
                echo "</div>";

                ?>
            </div>
            <?php
            echo "</div>";
        }

        if ($all_restaurant_data) {
            foreach ($all_restaurant_data as $r_id => $restaurant_data) {
                renderGallerySection($r_id, $restaurant_data, $index, $price, $diningTime, $parking);
                $index++;
            }
        } else {
            echo "<p>No data available for the given restaurant IDs.</p>";
        }
        ?>
    </div>

    <!-- 評論使用的資料庫 -->
    <?php
        // 获取餐厅ID
        $r_ids = [];
        for ($i = 1; $i <= 3; $i++) {
            if (isset($_GET["r_id$i"])) {
                $r_ids[] = intval($_GET["r_id$i"]);
            }
        }

        // 连接数据库并查询数据
        $conn = new mysqli('localhost', 'root', '', 'foodee');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // 构建 SQL 查询
        if (!empty($r_ids)) {
            $ids = implode("','", $r_ids); // 将数组中的ID转换为SQL字符串格式

            // 查询餐厅的评论
            $sql = "SELECT * FROM additional WHERE r_id IN ('$ids')";
            $result = $conn->query($sql);

            $data = array();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $data[$row['r_id']] = $row; // 以r_id为键保存数据
                }
            }


            // 按照 $r_ids 的顺序重新排序 $data
            $ordered_data = array();
            foreach ($r_ids as $id) {
                if (isset($data[$id])) {
                    $ordered_data[] = $data[$id];
                }
            }

            // 将数据转换为 JSON 格式
            $json_data = json_encode($ordered_data);
        } else {
            // 如果没有 r_id 參數，返回空数据
            $json_data = json_encode([]);
        }

        // 关闭数据库连接
        $conn->close();
        ?>

        <!-- 評論使用資料庫 結束 -->


        <!-- map使用資料庫開始 -->
        <?php
        // 获取餐厅ID
        $r_ids = [];
        for ($i = 1; $i <= 3; $i++) {
            if (isset($_GET["r_id$i"])) {
                $r_ids[] = intval($_GET["r_id$i"]);
            }
        }

        // 连接数据库并查询数据
        $conn = new mysqli('localhost', 'root', '', 'foodee');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // 構建SQL查詢
        if (!empty($r_ids)) {
            $ids = implode("','", $r_ids); // 將數組中的ID轉換為SQL字符串格式
            $sql = "SELECT * FROM detail WHERE r_id IN ('$ids')";

            $result = $conn->query($sql);

            $data = array();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $data[$row['r_id']] = $row; // 以 r_id 為鍵保存數據
                }
            }

            // 根據 $r_ids 的順序重新排序 $data
            $ordered_data = array();
            foreach ($r_ids as $id) {
                if (isset($data[$id])) {
                    $ordered_data[] = $data[$id];
                }
            }

            // 将数据转换为 JSON 格式
            $detail_data = json_encode($ordered_data);
        } else {
            // 處理沒有 r_id 參數的情況
            $detail_data = json_encode([]);
        }

        // 关闭数据库连接
        $conn->close();
        ?>

        <!-- map使用資料庫 結束 -->

        <div class="info-container">
            <div class="upper-section">
                <script type="text/javascript">
                    // 在PHP中将JSON数据传递给JS
                    const reviewData = <?php echo $json_data; ?>;
                    console.log('reviewData', reviewData);
                    console.log('reviewtime', receivedOpenTime);
                </script>
                <!-- <svg class="comment" width="600" height="220"></svg> -->
                <!-- <div class="comment_comment">評論</div> -->
            </div>

            <div class="resizer-horizontal-1"></div> <!-- 新增的水平分隔條 -->

            <div class="middle-section">
                <div class="middle-section1">
                    <script type="text/javascript">
                        const restaurant_data = <?php echo $detail_data; ?>;
                    </script>
                    <svg id="spider-<?php echo $r_id; ?>" class="spider" width="300" height="200"></svg>
                </div>
                <div class="middle-section2">
                    <script type="text/javascript">
                        const restaurant_time = <?php echo $detail_data; ?>;
                    </script>
                    <svg id="openTime-<?php echo $r_id; ?>" class="openTime" ></svg>
                </div>
            </div>
            <div class="resizer-horizontal-2"></div> <!-- 新增的水平分隔條 -->

            <div class="lower-section">
                <script type="text/javascript">
                    const restaurant_data_detail = <?php echo $detail_data; ?>;
                </script>

                <div id="map" width="250" height="200">
                    <svg id="comment-<?php echo $r_id; ?>" class="map" width="250" height="200"></svg>
                </div>
            </div>

            <!--<div class="button_container">
                <button id="shareButton">分享</button>
            </div>
            
            <script type="text/javascript">
                var globalData = {}; // 用來共享狀態的全局變量
            </script>

            <! <script src="https://d3js.org/d3.v7.min.js"></script> -->
            <script type="module">
                // import '../word_tree/word_tree_modify.js';
                import '../comment/cell_comment.js'
                import '../spider/cell_spider.js';
                import '../openTime/cell_openTime.js'
                import '../map/compare_map.js'
            </script>

            <div id="chat-section">
                <div class="chat">
                    <div id="chat">
                    <?php include '../chat/chat.php'; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="vote">
            <button id="toggleVoteButton" onclick="toggleVoteList()">去投票</button>
            <div id="voteList" style="display: none;flex-direction: row;"> <!-- 默认隐藏 -->
                
                <?php
                if (!empty($restaurant_names)) {
                    echo "<div class='vote-button'>";
                    $colors = [
                        "#ffc6dfef",  // #FF70AE with 50% opacity
                        "#acccffea",  // #85B4FF with 50% opacity
                        "#ffeab0ee"    // #FFCE47 with 20% opacity
                    ];
                    $index = 0;

                    foreach ($restaurant_ids as $r_id) {
                        if (isset($restaurant_names[$r_id])) {
                            $color = $colors[$index % count($colors)]; // 根据索引选择颜色
                            echo "<button class='restaurant-button' style='background-color: $color;' onclick='changeButtonState(this, $r_id)' data-selected='false'>" . htmlspecialchars($restaurant_names[$r_id]) . "</button>";
                            $index++;
                        }
                    }

                    echo "</div>";
                } else {
                    echo "没有餐廳资料可顯示。";
                }
                ?>

                <form id="voteForm">
                    <input type="hidden" id="votedRestaurants" name="votedRestaurants" value="">
                    <button type="button" style="width: auto; border:none;" onclick="submitVote()">投票</button>
                </form>
                <div id="voteMessage"></div> <!-- 显示确认信息 -->
            </div>
        </div>

        <script>
        function toggleVoteList() {
            const voteList = document.getElementById('voteList');
            const toggleButton = document.getElementById('toggleVoteButton');

            if (voteList.style.display === 'none' || voteList.style.display === '') {
                voteList.style.display = 'flex'; // 顯示餐廳選項
                voteList.style.maxHeight = voteList.scrollHeight + 'px'; // 設定為實際高度
                toggleButton.innerText = '﹀'; // 更改按鈕文字
            } else {
                voteList.style.maxHeight = '0px'; // 收回至0高度
                setTimeout(() => {
                    voteList.style.display = 'none'; // 延遲收回後隱藏
                }, 500); // 動畫結束後隱藏
                toggleButton.innerText = '去投票'; // 恢復按鈕文字
            }
        }


        function changeButtonState(button, r_id) {
            let isSelected = button.getAttribute('data-selected') === 'true';

            if (isSelected) {
                button.setAttribute('data-selected', 'false');
                button.style.backgroundColor = button.getAttribute('data-initial-color');
                removeRestaurantFromVote(r_id);
            } else {
                button.setAttribute('data-selected', 'true');
                button.setAttribute('data-initial-color', button.style.backgroundColor);
                button.style.backgroundColor = 'rgb(247, 215, 70,.8)';
                addRestaurantToVote(r_id);
            }
        }

        function addRestaurantToVote(r_id) {
            let votedRestaurants = document.getElementById('votedRestaurants').value;
            votedRestaurants = votedRestaurants ? votedRestaurants.split(',') : [];
            if (!votedRestaurants.includes(r_id.toString())) {
                votedRestaurants.push(r_id);
            }
            document.getElementById('votedRestaurants').value = votedRestaurants.join(',');
        }

        function removeRestaurantFromVote(r_id) {
            let votedRestaurants = document.getElementById('votedRestaurants').value;
            votedRestaurants = votedRestaurants ? votedRestaurants.split(',') : [];
            const index = votedRestaurants.indexOf(r_id.toString());
            if (index > -1) {
                votedRestaurants.splice(index, 1);
            }
            document.getElementById('votedRestaurants').value = votedRestaurants.join(',');
        }

        function submitVote() {
            // 收集所有选中的餐厅 r_id
            const votedRestaurants = [];
            document.querySelectorAll('.restaurant-button').forEach(button => {
                if (button.getAttribute('data-selected') === 'true') {
                    const r_id = button.getAttribute('onclick').match(/\d+/)[0]; // 修改這行
                    votedRestaurants.push(r_id);
                }
            });

            // 如果有选中的餐厅，发送数据到服务器
            if (votedRestaurants.length > 0) {
                const xhr = new XMLHttpRequest();
                const formData = new FormData();
                formData.append('votedRestaurants', votedRestaurants.join(',')); // 传递r_id而不是名称

                xhr.open("POST", "../cellphone/vote_processor.php", true);

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        document.getElementById('voteMessage').innerText = '投票成功！';
                    } else {
                        document.getElementById('voteMessage').innerText = '投票失败，请重试。';
                    }
                };

                xhr.send(formData);
            } else {
                document.getElementById('voteMessage').innerText = '至少選一個:/';
            }
        }


    </script>

    <script>
    function changeButtonColor(button) {
        // 检查当前背景颜色是否为黄色
        if (button.style.backgroundColor === 'rgba(255, 255, 0, 0.7)') { // 若背景色为黄色透明度0.7
            // 如果是黄色，恢复到初始颜色
            button.style.backgroundColor = button.getAttribute('data-initial-color');
        } else {
            // 否则，保存当前颜色并设置为黄色透明度0.7
            button.setAttribute('data-initial-color', button.style.backgroundColor);
            button.style.backgroundColor = 'rgba(255, 255, 0, 0.7)'; // 黄色透明度0.7
        }
    }
    </script>
    <script>
        let currentRestaurantId = <?php echo reset($restaurant_ids); ?>;
        function changeRestaurant(r_id) {
            document.getElementById(`restaurant-${currentRestaurantId}`).classList.remove('active');
            document.getElementById(`restaurant-${r_id}`).classList.add('active');
            currentRestaurantId = r_id;
        }

        function nextImage(element) {
            const images = element.parentElement.querySelectorAll('.gallery-img');
            let currentIndex = Array.from(images).findIndex(img => img.classList.contains('active'));
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % images.length;
            images[currentIndex].classList.add('active');
        }

        window.onload = function() {
            // 在接收到的變數中尋找並亮起相關標籤
            function highlightTagsBasedOnReceivedData() {
                const tagElements = document.querySelectorAll('.vibe-tags span, .food-tags span');
                tagElements.forEach(tagElement => {
                    const tagText = tagElement.innerText.trim();
                    const restaurantId = tagElement.closest('.restaurant-section').id.split('-')[1]; // 获取餐厅ID

                    // 检查 receivedVibe 和 receivedFood 中是否有对应的标签
                    if ((receivedVibe[restaurantId] && receivedVibe[restaurantId].includes(tagText)) ||
                        (receivedFood[restaurantId] && receivedFood[restaurantId].includes(tagText))) {
                        tagElement.style.backgroundColor = 'rgba(252, 235, 167, 0.8)';
                    }
                });

                // 高亮价钱标签
                Object.keys(receivedPrice).forEach(restaurantId => {
                    if (receivedPrice[restaurantId]) {
                        const priceTag = document.querySelector(`#restaurant-${restaurantId} .price-tag`);
                        if (priceTag) {
                            priceTag.style.backgroundColor = 'rgba(252, 235, 167, 0.8)';
                        }
                    }
                });

                // 高亮用餐时间标签
                Object.keys(receivedDiningTime).forEach(restaurantId => {
                    if (receivedDiningTime[restaurantId]) {
                        const diningTimeTag = document.querySelector(`#restaurant-${restaurantId} .dining-time-tag`);
                        if (diningTimeTag) {
                            diningTimeTag.style.backgroundColor = 'rgba(252, 235, 167, 0.8)';
                        }
                    }
                });

                // 高亮停车场标签
                Object.keys(receivedParking).forEach(restaurantId => {
                    if (receivedParking[restaurantId]) {
                        const parkingTag = document.querySelector(`#restaurant-${restaurantId} .parking-tag`);
                        if (parkingTag) {
                            parkingTag.style.backgroundColor = 'rgba(252, 235, 167, 0.8)';
                        }
                    }
                });

                // 高亮图表背景
                if (receivedSpider[currentRestaurantId]) {
                    document.querySelector('.middle-section1').style.backgroundColor = '#f3f3f3';
                }
                if (receivedComment[currentRestaurantId]) {
                    document.querySelector('.upper-section').style.backgroundColor = '#f3f3f3';
                }
                if (receivedOpenTime[currentRestaurantId]) {
                    document.querySelector('.middle-section2').style.backgroundColor = '#f3f3f3';
                }
            }

            // 檢查傳過來的變數 spider 是否為 true，並亮起 middle-section1 背景
            if (receivedSpider === true) {
                document.querySelector('.middle-section1').style.backgroundColor = '#f3f3f3';
            }
            if (receivedComment === true) {
                document.querySelector('.upper-section').style.backgroundColor = '#f3f3f3';
            }
            if (receivedOpenTime === true) {
                document.querySelector('.middle-section2').style.backgroundColor = '#f3f3f3';
            }

            highlightTagsBasedOnReceivedData();
        }
    // 定义一个函数来更新 chat-section 的 margin-bottom
    function updateMarginBottom() {
        // 获取 .vote 元素的高度
        var voteHeight = document.querySelector('.vote').offsetHeight;

        // 设置 chat-section 的 margin-bottom 为 voteHeight + 80px
        document.querySelector('#chat-section').style.marginBottom = (voteHeight + 20) + 'px';
    }

    // 页面加载后立即更新一次
    updateMarginBottom();

    // 如果 .vote 的高度可能会改变，可以监听窗口大小变化事件
    window.addEventListener('resize', updateMarginBottom);
    </script>

</body>

</html>

<?php
ob_end_flush();
?>
