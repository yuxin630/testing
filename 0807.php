<?php
$host = 'localhost';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'foodee';
$link = mysqli_connect($host, $dbuser, $dbpassword, $dbname);

// 初始化變數
// 初始化變數
$all_restaurant_data = [];

if ($link) {
    mysqli_query($link, 'SET NAMES utf8');

    // Get restaurant IDs from GET parameters
    $r_ids = [];
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_GET["r_id$i"])) {
            $r_ids[] = intval($_GET["r_id$i"]);
        }
    }

    // Fetch images, names, vibes, dishes, prices, and votes for each restaurant
    foreach ($r_ids as $r_id) {
        $query = "
            SELECT a.r_name, a.r_vibe, a.r_food_dishes, a.r_price_low, a.r_price_high, 
                   a.r_photo_env1, a.r_photo_env2, a.r_photo_env3, 
                   a.r_photo_food1, a.r_photo_food2, a.r_photo_food3, a.r_photo_food4, a.r_photo_food5, 
                   a.r_photo_door, a.r_photo_menu1, a.r_photo_menu2, a.r_photo_menu3,
                   a.special_comment_sum, a.notice_comment_sum, 
                   v.vote
            FROM additional a
            LEFT JOIN vote v ON a.r_id = v.r_id
            WHERE a.r_id = $r_id";

        $result = mysqli_query($link, $query);

        if ($result) {
            $restaurant_data = mysqli_fetch_assoc($result);
            $all_restaurant_data[$r_id] = $restaurant_data;
        } else {
            echo "Error in query: " . mysqli_error($link);
            $all_restaurant_data[$r_id] = null;
        }
    }
} else {
    echo "Failed to connect to the database: " . mysqli_connect_error();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="0807.css">
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <!-- <link rel="stylesheet" href="../word_tree/word_tree.css"> -->

    <!-- map -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" /> <!--leaflet css file-->
    <link rel="stylesheet" src="../map/compare_map.css">
    <!-- Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- edgeMarker -->
    <script src="../map/leaflet_edgeMarker.js"></script>

    <!-- openTime -->
    <link rel="stylesheet" href="../openTime/openTime.css">

    <!-- comment -->
    <link rel="stylesheet" href="../comment/comment.css" />
</head>

<body>
    <div class="container">
        <div class="gallery-container">
            <?php
            // Helper function to render image gallery for each restaurant

            // 顏色陣列
            $colors = ["#FF70AE", "#85B4FF", "#FFCE47"];
            $counter = 0;

            // 名稱背景opacity0.5 
            function hexToRgba($color, $opacity)
            {
                $hex = str_replace("#", "", $color);

                if (strlen($hex) == 6) {
                    $rgb = [
                        hexdec(substr($hex, 0, 2)),
                        hexdec(substr($hex, 2, 2)),
                        hexdec(substr($hex, 4, 2))
                    ];
                } elseif (strlen($hex) == 3) {
                    $rgb = [
                        hexdec(str_repeat(substr($hex, 0, 1), 2)),
                        hexdec(str_repeat(substr($hex, 1, 1), 2)),
                        hexdec(str_repeat(substr($hex, 2, 1), 2))
                    ];
                } else {
                    $rgb = [0, 0, 0]; // fallback to black if color is invalid
                }

                return "rgba(" . implode(",", $rgb) . ",$opacity)";
            }
            $restaurantColorIndices = [];

            function renderGallerySection($r_id, $restaurant_data, &$counter, $colors, $isFirst)
            {
                global $restaurantColorIndices;
                // 計算顏色索引，根據計數器依序分配顏色
                $colorIndex = $counter % count($colors);
                $backgroundColor = hexToRgba($colors[$colorIndex], 0.5); // Convert HEX to RGBA with 0.5 opacity
                // 记录颜色索引
                $restaurantColorIndices[$r_id] = $colorIndex;

                echo "<div class='gallery-section'>";

                // 餐廳名稱和投票次數部分
                // 獲取投票次數
                $vote_count = isset($restaurant_data['vote']) ? $restaurant_data['vote'] : 0;

                // 餐廳名稱和投票次數部分
                echo "<div class='restaurant-container' style='background-color: {$backgroundColor}; padding: 10px; border-radius: 10px; display: flex; width: auto;justify-content: space-between;align-items: center;'>"; // 整個區塊背景和設計
                echo "<div style='display: flex; align-items: center;'>"; // 使用flex排列名稱和勾選框
                echo "<input type='checkbox' class='restaurant-checkbox' data-id='{$r_id}' style='margin-right: 10px; cursor: pointer; width: 18px; height: 18px;' onchange='handleCheckboxChange(this)'>";
                echo "<div style='cursor: default; font-weight:bold;'>" . htmlspecialchars($restaurant_data['r_name']) . "</div>"; // 餐廳名稱在勾選框右邊
                echo "</div>";
                // 投票次數換行顯示
                echo "<div style='cursor: default; right:0'>票數: " . htmlspecialchars($vote_count) . "</div>";
                echo "</div>";

                // 更新計數器
                $counter++;

                // 環境圖片部分
                if ($isFirst) {
                    echo "<h3 style='cursor: default;'>環境</h3>";
                } else {
                    echo "<h3 style='color: white; cursor: default;'>我</h3>";
                }
                echo "<div class='vibe-tags'>";
                if (!empty($restaurant_data['r_vibe'])) {
                    $vibes = explode('，', $restaurant_data['r_vibe']);
                    $count = 0; // 初始化計數器
                    foreach ($vibes as $vibe) {
                        if ($count >= 5) break; // 當顯示了四個標籤時停止
                        echo "<div class='restaurant-tag' style='cursor: default;'>" . htmlspecialchars(trim($vibe)) . "</div>";
                        $count++; // 增加計數器
                    }
                }                
                echo "</div>";

                // 環境圖片
                echo "<div class='image-container'>";
                foreach (['r_photo_env1', 'r_photo_env2', 'r_photo_env3', 'r_photo_door'] as $index => $field) {
                    if (!empty($restaurant_data[$field])) {
                        $activeClass = $index === 0 ? 'active' : '';
                        echo "<img src='{$restaurant_data[$field]}' class='gallery-img $activeClass' data-category='environment-{$r_id}' data-index='$index' onerror='this.onerror=null;this.src=\"fallback.jpg\";' onclick='openModal(this)' />";
                    }
                }
                echo "<span class='nav-arrow prev' onclick='changeImage(this, -1)'>‹</span>";
                echo "<span class='nav-arrow next' onclick='changeImage(this, 1)'>›</span>";
                echo "</div>";

                // 食物圖片部分
                if ($isFirst) {
                    echo "<h3 style='cursor: default;'>食物</h3>";
                } else {
                    echo "<h3 style='color: white; cursor: default;'>我</h3>";
                }
                echo "<div class='food-tags'>";
                if (!empty($restaurant_data['r_food_dishes'])) {
                    $dishes = explode('、', $restaurant_data['r_food_dishes']);
                    $count = 0; // 初始化計數器
                    foreach ($dishes as $dish) {
                        if ($count >= 4) break; // 當顯示了四個標籤時停止
                        echo "<div class='restaurant-tag' style='cursor: default;'>" . htmlspecialchars(trim($dish)) . "</div>";
                        $count++; // 增加計數器
                    }
                }                
                echo "</div>";

                // 食物圖片
                echo "<div class='image-container'>";
                foreach (['r_photo_food1', 'r_photo_food2', 'r_photo_food3', 'r_photo_food4', 'r_photo_food5'] as $index => $field) {
                    if (!empty($restaurant_data[$field])) {
                        $activeClass = $index === 0 ? 'active' : '';
                        echo "<img src='{$restaurant_data[$field]}' class='gallery-img $activeClass' data-category='food-{$r_id}' data-index='$index' onerror='this.onerror=null;this.src=\"fallback.jpg\";' onclick='openModal(this)' />";
                    }
                }
                echo "<span class='nav-arrow prev' onclick='changeImage(this, -1)'>‹</span>";
                echo "<span class='nav-arrow next' onclick='changeImage(this, 1)'>›</span>";
                echo "</div>";

                // 菜單圖片部分
                if ($isFirst) {
                    echo "<h3 style='cursor: default;'>菜單</h3>";
                } else {
                    echo "<h3 style='color: white; cursor: default;'>我</h3>";
                }
                echo "<div class='vibe-tags'>";
                if (!empty($restaurant_data['r_price_low']) && !empty($restaurant_data['r_price_high'])) {
                    echo "<div class='price-tag' style='cursor: default;'>價錢: $" . htmlspecialchars($restaurant_data['r_price_low']) . " ~ $" . htmlspecialchars($restaurant_data['r_price_high']) . "</div>";
                }
                echo "</div>";

                // 菜單圖片
                echo "<div class='image-container'>";
                foreach (['r_photo_menu1', 'r_photo_menu2', 'r_photo_menu3'] as $index => $field) {
                    if (!empty($restaurant_data[$field])) {
                        $activeClass = $index === 0 ? 'active' : '';
                        echo "<img src='{$restaurant_data[$field]}' class='gallery-img $activeClass' data-category='menu-{$r_id}' data-index='$index' onerror='this.onerror=null;this.src=\"fallback.jpg\";' onclick='openModal(this)' />";
                    }
                }
                echo "<span class='nav-arrow prev' onclick='changeImage(this, -1)'>‹</span>";
                echo "<span class='nav-arrow next' onclick='changeImage(this, 1)'>›</span>";
                echo "</div>";

                // 可折叠的評論部分
                echo "<div class='collapsible-comments'>";
                echo "<button type='button' class='comments-button' onclick='toggleComments(this)'>特色 & 注意事項 <img src='up.png' style='width:12px; height:12px; vertical-align:middle;' /></button> ";
                echo "<div class='content'>";
                echo "<h4 style = 'margin-top: 10px; margin-bottom: 5px'>特色：</h4>";
                if (!empty($restaurant_data['special_comment_sum'])) {
                    $special_comments = explode('。', $restaurant_data['special_comment_sum']);
                    foreach ($special_comments as $comment) {
                        echo "<p> - " . htmlspecialchars(trim($comment)) . "</p>";
                    }
                }
                echo "<h4>注意事項：</h4>";
                if (!empty($restaurant_data['notice_comment_sum'])) {
                    $notice_comments = explode('。', $restaurant_data['notice_comment_sum']);
                    foreach ($notice_comments as $comment) {
                        echo "<p> - " . htmlspecialchars(trim($comment)) . "</p>";
                    }
                }
                echo "</div>";
                echo "</div>";

                echo "</div>";
            }


            if ($all_restaurant_data) {
                $isFirst = true; // 初始化标志位
                foreach ($all_restaurant_data as $r_id => $restaurant_data) {
                    if ($restaurant_data) {
                        renderGallerySection($r_id, $restaurant_data, $counter, $colors, $isFirst);
                        $isFirst = false; // 之后的循环将标志位设为 false
                    } else {
                        echo "<p>No data available for restaurant ID: $r_id.</p>";
                    }
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

            // 查询朋友的评论
            $sql_friends = "SELECT * FROM comment WHERE r_id IN ('$ids')";
            $result_friends = $conn->query($sql_friends);

            $friend_comments = array();
            if ($result_friends->num_rows > 0) {
                while ($row = $result_friends->fetch_assoc()) {
                    $friend_comments[] = $row;
                }
            }

            // 將朋友評論合併到原本的數據中
            foreach ($r_ids as $restaurant_id) {
                if (isset($data[$restaurant_id])) {
                    $data[$restaurant_id]['friend_reviews'] = array_filter($friend_comments, function ($comment) use ($restaurant_id) {
                        return $comment['r_id'] == $restaurant_id;
                    });
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
                    <svg class="spider" width="300" height="200"></svg>
                </div>
                <div class="middle-section2">
                    <script type="text/javascript">
                        const restaurant_time = <?php echo $detail_data; ?>;
                    </script>
                    <svg class="openTime" width="300" height="250"></svg>
                </div>
            </div>
            <div class="resizer-horizontal-2"></div> <!-- 新增的水平分隔條 -->

            <div class="lower-section">
                <script type="text/javascript">
                    const restaurant_data_detail = <?php echo $detail_data; ?>;
                </script>

                <div id="map" width="250" height="200">
                    <svg class="map" width="250" height="200"></svg>
                </div>
            </div>
            <div class="button_container"><!-- 分享和BACK按钮 -->
                <button id="backButton" class="map-button">BACK</button>
                <button id="shareButton" class="map-button">分享</button>
            </div>

            <!-- <div class="button_container">
                <button id="shareButton" role="button">分享</button>
            </div> -->

            <script type="text/javascript">
                var globalData = {}; // 用來共享狀態的全局變量
            </script>

            <!-- <script src="https://d3js.org/d3.v7.min.js"></script> -->
            <script type="module">
                // import '../word_tree/word_tree_modify.js';
                import '../comment/comment.js'
                import '../spider/spider.js';
                import '../openTime/openTime.js'
                import '../map/compare_map.js'
            </script>
        </div>
    </div>

    <!-- 新增的分享面板 -->
    <div id="sharePanel" style="display: none;">
        <h2 style='margin-bottom: 10px; margin-top: 0px'>分享給朋友：</h2>
        <div id="share-content">
            <!-- 动态生成餐厅信息 -->
        </div>
        <div class="panel-buttons">
            <button class="share-button" id="closePanelButton">BACK</button>
            <button class="share-button" id="finalShareButton">分享</button>
        </div>
    </div>


    <!-- The Modal -->
    <div id="imageModal" class="modal">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImg">
        <span class="modal-prev" onclick="prevModalImage()">‹</span>
        <span class="modal-next" onclick="nextModalImage()">›</span>
    </div>

    <script>
        let currentCategory = '';
        let currentIndex = 0;
        let currentImgSrc = '';

        function openModal(element) {
            const modal = document.getElementById("imageModal");
            const modalImg = document.getElementById("modalImg");
            modal.style.display = "block";
            modalImg.src = element.src;
            currentCategory = element.dataset.category;
            currentIndex = parseInt(element.dataset.index, 10);
            currentImgSrc = element.src;
        }

        function closeModal() {
            const modal = document.getElementById("imageModal");
            modal.style.display = "none";
        }

        function prevImage(arrow) {
            const section = arrow.closest('.image-container');
            const images = section.querySelectorAll('img');
            let currentIndex = Array.from(images).findIndex(img => img.classList.contains('active'));
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            images[currentIndex].classList.add('active');
        }

        function nextImage(arrow) {
            const section = arrow.closest('.image-container');
            const images = section.querySelectorAll('img');
            let currentIndex = Array.from(images).findIndex(img => img.classList.contains('active'));
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % images.length;
            images[currentIndex].classList.add('active');
        }

        function changeImage(arrow, direction) {
            const section = arrow.closest('.image-container');
            const images = section.querySelectorAll('img');
            let currentIndex = Array.from(images).findIndex(img => img.classList.contains('active'));
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + direction + images.length) % images.length;
            images[currentIndex].classList.add('active');
        }

        function prevModalImage() {
            const modalImg = document.getElementById("modalImg");
            const images = Array.from(document.querySelectorAll(`.image-container img[data-category='${currentCategory}']`));
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            currentImgSrc = images[currentIndex].src;
            modalImg.src = currentImgSrc;
        }

        function nextModalImage() {
            const modalImg = document.getElementById("modalImg");
            const images = Array.from(document.querySelectorAll(`.image-container img[data-category='${currentCategory}']`));
            currentIndex = (currentIndex + 1) % images.length;
            currentImgSrc = images[currentIndex].src;
            modalImg.src = currentImgSrc;
        }

        // function toggleComments(button) {
        //     const comments = button.nextElementSibling;
        //     const isExpanded = comments.classList.contains("show");

        //     if (isExpanded) {
        //         comments.classList.remove("show");
        //         comments.classList.remove("slide-down");
        //         comments.classList.add("slide-up");
        //         button.innerText = "特色 & 注意事項";
        //     } else {
        //         comments.classList.add("show");
        //         comments.classList.remove("slide-up");
        //         comments.classList.add("slide-down");
        //         button.innerText = "隱藏";
        //     }
        // }
        function toggleComments(button) {
            const comments = button.nextElementSibling;
            const isExpanded = comments.classList.contains("show");

            if (isExpanded) {
                comments.classList.remove("show");
                comments.classList.remove("slide-down");
                comments.classList.add("slide-up");
                button.innerHTML = "特色 & 注意事項 <img src='up.png' style='width:12px; height:12px; ' />";
            } else {
                comments.classList.add("show");
                comments.classList.remove("slide-up");
                comments.classList.add("slide-down");
                button.innerHTML = "隱藏 <img src='up.png' style='width:12px; height:12px; vertical-align:middle; transform: rotate(180deg);' />";
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const coll = document.getElementsByClassName("collapsible");
            for (let i = 0; i < coll.length; i++) {
                coll[i].addEventListener("click", function() {
                    this.classList.toggle("active");
                    const content = this.nextElementSibling;
                    const arrow = this.querySelector('.arrow');
                    if (content.classList.contains("show")) {
                        content.classList.remove("show");
                        arrow.classList.remove("arrow-up");
                    } else {
                        content.classList.add("show");
                        arrow.classList.add("arrow-up");
                    }
                });
            }
        });
        // 勾勾
        const selectedRestaurants = [];

        function handleCheckboxChange(checkbox) {
            const restaurantId = checkbox.getAttribute('data-id');
            const restaurantNameDiv = checkbox.parentElement; // 父元素是 .restaurant-name 的 div

            if (checkbox.checked) {
                // 如果勾選，將餐廳ID添加到數組中，並調整背景透明度
                selectedRestaurants.push(restaurantId);
                restaurantNameDiv.style.backgroundColor = restaurantNameDiv.style.backgroundColor.replace(/rgba\((\d+),\s*(\d+),\s*(\d+),\s*(\d+\.?\d*)\)/, 'rgba($1, $2, $3, 0.8)');
            } else {
                // 如果取消勾選，從數組中移除該ID，並恢復背景透明度
                const index = selectedRestaurants.indexOf(restaurantId);
                if (index > -1) {
                    selectedRestaurants.splice(index, 1);
                }
                restaurantNameDiv.style.backgroundColor = restaurantNameDiv.style.backgroundColor.replace(/rgba\((\d+),\s*(\d+),\s*(\d+),\s*(\d+\.?\d*)\)/, 'rgba($1, $2, $3, 0.5)');
            }

            console.log('Selected Restaurants:', selectedRestaurants);
            // 您可以在這裡將選擇的餐廳ID保存到後端或做其他處理
        }

        // 名稱背景opacity0.5 
        function hexToRgba(hex, opacity) {
            // 去掉 '#' 符号
            hex = hex.replace('#', '');

            // 处理 3 位和 6 位的 hex 颜色值
            let r, g, b;
            if (hex.length === 3) {
                r = parseInt(hex[0] + hex[0], 16);
                g = parseInt(hex[1] + hex[1], 16);
                b = parseInt(hex[2] + hex[2], 16);
            } else if (hex.length === 6) {
                r = parseInt(hex.substring(0, 2), 16);
                g = parseInt(hex.substring(2, 4), 16);
                b = parseInt(hex.substring(4, 6), 16);
            }

            // 返回 rgba 颜色值
            return `rgba(${r}, ${g}, ${b}, ${opacity})`;
        }

        const selectedItems = {
            vibe: {},
            food: {},
            price: {},
            diningTime: {},
            parking: {},
            spider: {},
            comment: {},
            openTime: {}
        };

        const all_restaurant_data = <?php echo json_encode($all_restaurant_data); ?>;
        const restaurantColorIndices = <?php echo json_encode($restaurantColorIndices); ?>;
        console.log('all_restaurant_data', all_restaurant_data);
        //分享版面樣示
        document.getElementById("shareButton").addEventListener("click", function() {
            const shareContent = document.getElementById('share-content');
            shareContent.innerHTML = ''; // 清空之前的內容

            const selectedCount = selectedRestaurants.length;
            let panelHeight;
            if (selectedCount === 1) {
                panelHeight = '350px';
            } else {
                panelHeight = '600px';
            }

            sharePanel.style.height = panelHeight;

            if (selectedCount > 1) {
                sharePanel.style.overflowY = 'scroll';
            } else {
                sharePanel.style.overflowY = 'hidden';
            }

            const colors = ["#FF70AE", "#85B4FF", "#FFCE47"];

            selectedRestaurants.forEach((id, index) => {
                const restaurantData = all_restaurant_data[id];
                console.log("restaurant_data= ", restaurantData);
                const colorIndex = restaurantColorIndices[id];
                const backgroundColor = colors[colorIndex];
                const rgbaBackgroundColor = hexToRgba(backgroundColor, 0.5);

                if (restaurantData) {
                    const restaurantTitle = document.createElement('div');
                    restaurantTitle.className = 'restaurant-title-div';

                    const restaurantItem = document.createElement('div');
                    restaurantItem.className = 'restaurant-item';

                    const titleDiv = document.createElement('div');
                    titleDiv.className = 'restaurant-title';
                    titleDiv.style.backgroundColor = rgbaBackgroundColor;

                    const nameSpan = document.createElement('span');
                    nameSpan.textContent = restaurantData.r_name;

                    //const input = document.createElement('input');
                    //input.type = 'text';
                    //input.placeholder = '想說ㄉ話...';

                    titleDiv.appendChild(nameSpan);
                    //titleDiv.appendChild(input);

                    const leftColumn = document.createElement('div');
                    leftColumn.className = 'left-column';

                    const buttonGroup = document.createElement('div');
                    buttonGroup.className = 'button-group';

                    const parkingButton = document.createElement('button');
                    parkingButton.className = 'parking-button';

                    const parkingSvg = `
                    <svg fill="${restaurantData.r_has_parking == 1 ? '#0000FF' : '#A9A9A9'}" width="20px" height="20px" viewBox="0 0 454 454" xmlns="http://www.w3.org/2000/svg">
                        <g>
                            <g>
                                <path d="M228.062,154.507h-34.938v65.631h34.938c18.094,0,32.814-14.72,32.814-32.814
                                    C260.877,169.23,246.156,154.507,228.062,154.507z"/>
                                <path d="M0,0v454h454V0H0z M228.062,279.648h-34.938v79.398h-59.512V94.952l94.451,0.043c50.908,0,92.325,41.418,92.325,92.328
                                    C320.388,238.232,278.971,279.648,228.062,279.648z"/>
                            </g>
                        </g>
                    </svg>`;

                    parkingButton.innerHTML = parkingSvg;

                    parkingButton.addEventListener('click', function() {
                        selectedItems.parking[id] = !selectedItems.parking[id];
                        parkingButton.style.backgroundColor = selectedItems.parking[id] ? '#F4DEB3' : '';
                    });

                    const priceButton = document.createElement('button');
                    priceButton.className = 'price-button';
                    priceButton.innerHTML = `
                    <svg height="20px" width="20px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 235.517 235.517" fill="#f9f053">
                        <path d="M118.1,235.517c7.898,0,14.31-6.032,14.31-13.483c0-7.441,0-13.473,0-13.473 c39.069-3.579,64.932-24.215,64.932-57.785v-0.549c0-34.119-22.012-49.8-65.758-59.977V58.334c6.298,1.539,12.82,3.72,19.194,6.549 c10.258,4.547,22.724,1.697,28.952-8.485c6.233-10.176,2.866-24.47-8.681-29.654c-11.498-5.156-24.117-8.708-38.095-10.236V8.251 c0-4.552-6.402-8.251-14.305-8.251c-7.903,0-14.31,3.514-14.31,7.832c0,4.335,0,7.843,0,7.843 c-42.104,3.03-65.764,25.591-65.764,58.057v0.555c0,34.114,22.561,49.256,66.862,59.427v33.021 c-10.628-1.713-21.033-5.243-31.623-10.65c-11.281-5.755-25.101-3.72-31.938,6.385c-6.842,10.1-4.079,24.449,7.294,30.029 c16.709,8.208,35.593,13.57,54.614,15.518v13.755C103.79,229.36,110.197,235.517,118.1,235.517z M131.301,138.12 c14.316,4.123,18.438,8.257,18.438,15.681v0.555c0,7.979-5.776,12.651-18.438,14.033V138.12z M86.999,70.153v-0.549 c0-7.152,5.232-12.657,18.71-13.755v29.719C90.856,81.439,86.999,77.305,86.999,70.153z"/>
                    </svg> 
                    ${restaurantData.r_price_low} ~ ${restaurantData.r_price_high}`;

                    priceButton.addEventListener('click', function() {
                        selectedItems.price[id] = !selectedItems.price[id];
                        priceButton.style.backgroundColor = selectedItems.price[id] ? '#F4DEB3' : '';
                    });

                    const diningTimeButton = document.createElement('button');
                    diningTimeButton.className = 'dining-time-button';
                    const diningTime = restaurantData.r_time_low ? `${restaurantData.r_time_low} min` : "未限時";

                    diningTimeButton.innerHTML = `
                    <svg fill="#000000" width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20,3a1,1,0,0,0,0-2H4A1,1,0,0,0,4,3H5.049c.146,1.836.743,5.75,3.194,8-2.585,2.511-3.111,7.734-3.216,10H4a1,1,0,0,0,0,2H20a1,1,0,0,0,0-2H18.973c-.105-2.264-.631-7.487-3.216-10,2.451-2.252,3.048-6.166,3.194-8Zm-6.42,7.126a1,1,0,0,0,.035,1.767c2.437,1.228,3.2,6.311,3.355,9.107H7.03c.151-2.8.918-7.879,3.355-9.107a1,1,0,0,0,.035-1.767C7.881,8.717,7.227,4.844,7.058,3h9.884C16.773,4.844,16.119,8.717,13.58,10.126ZM12,13s3,2.4,3,3.6V20H9V16.6C9,15.4,12,13,12,13Z"/>
                    </svg> 
                    用餐時間: ${diningTime}`;

                    diningTimeButton.addEventListener('click', function() {
                        selectedItems.diningTime[id] = !selectedItems.diningTime[id];
                        diningTimeButton.style.backgroundColor = selectedItems.diningTime[id] ? '#F4DEB3' : '';
                    });

                    const middleColumn = document.createElement('div');
                    middleColumn.className = 'middle-column';

                    const rightColumn = document.createElement('div');
                    rightColumn.className = 'right-column';

                    const vibeTitle = document.createElement('div');
                    vibeTitle.className = 'tag-title';
                    vibeTitle.textContent = '氣氛';

                    const visPicDiv = document.createElement('div');
                    visPicDiv.className = 'vis-pic';

                    // 使用 SVG 或 PNG 图像创建按钮
                    const visualButtons = [{
                            id: 'spider',
                            src: 'spider.png', // 替换为SVG或PNG的实际路径
                            label: 'Spider',
                            width: '100px', // 设置Spider图像的宽度
                            height: '100px' // 设置Spider图像的高度
                        },
                        {
                            id: 'comment',
                            src: 'comment.png', // 替换为SVG或PNG的实际路径
                            label: 'Comment',
                            width: '140px', // 设置Comment图像的宽度
                            height: '100px' // 设置Comment图像的高度
                        },
                        {
                            id: 'opentime',
                            src: 'openTime.png', // 替换为SVG或PNG的实际路径
                            label: 'Open Time',
                            width: '100px', // 设置Open Time图像的宽度
                            height: '100px' // 设置Open Time图像的高度
                        }
                    ];

                    visualButtons.forEach(buttonInfo => {
                        const button = document.createElement('button');
                        button.className = 'vis-button';
                        button.id = buttonInfo.id;

                        // 创建图像元素
                        const img = document.createElement('img');
                        img.src = buttonInfo.src;
                        img.alt = buttonInfo.label;

                        // 根据配置设置图像大小
                        img.style.width = buttonInfo.width; // 设置图像宽度
                        img.style.height = buttonInfo.height; // 设置图像高度

                        button.appendChild(img);

                        // 定義一個變量來跟蹤是否被點擊
                        let isClicked = false;

                        // Hover 事件同步
                        button.addEventListener('mouseenter', function() {
                            if (!isClicked) { // 如果沒有被點擊，才允許 hover 效果
                                document.querySelectorAll(`.vis-button img[src='${buttonInfo.src}']`).forEach(otherImg => {
                                    otherImg.style.transform = 'scale(1.1)'; // 放大圖片
                                    otherImg.parentElement.style.backgroundColor = '#F4DEB3'; // 改變背景顏色
                                });
                            }
                        });

                        button.addEventListener('mouseleave', function() {
                            if (!isClicked) { // 如果沒有被點擊，才恢復原來的樣式
                                document.querySelectorAll(`.vis-button img[src='${buttonInfo.src}']`).forEach(otherImg => {
                                    otherImg.style.transform = 'scale(1)'; // 恢復圖片大小
                                    otherImg.parentElement.style.backgroundColor = '#eee'; // 恢復背景顏色
                                });
                            }
                        });

                        // Click 事件同步
                        button.addEventListener('click', function() {
                            isClicked = !isClicked; // 切換選中狀態
                            selectedItems[buttonInfo.id] = isClicked; // 更新選中狀態

                            // 找到所有相同圖片的按鈕並同步點擊效果
                            document.querySelectorAll(`.vis-button img[src='${buttonInfo.src}']`).forEach(otherImg => {
                                if (isClicked) {
                                    // 當被選中時，設定固定樣式，並阻止 hover 改變背景顏色
                                    otherImg.style.transform = 'scale(1.1)';
                                    otherImg.parentElement.style.backgroundColor = '#F4DEB3';
                                } else {
                                    // 當取消選中時，恢復為默認狀態，允許 hover 改變背景顏色
                                    otherImg.style.transform = 'scale(1)';
                                    otherImg.parentElement.style.backgroundColor = '#eee';
                                }
                            });
                        });

                        visPicDiv.appendChild(button);
                    });

                    const vibeTagsDiv = document.createElement('div');
                    vibeTagsDiv.className = 'vibe-tags-share';
                    if (restaurantData.r_vibe) {
                        const vibes = restaurantData.r_vibe.split('，');
                        vibes.forEach(vibe => {
                            const button = document.createElement('button');
                            button.className = 'restaurant-tag-share';
                            button.textContent = vibe.trim();

                            button.addEventListener('click', function() {
                                if (!selectedItems.vibe[id]) {
                                    selectedItems.vibe[id] = [];
                                }

                                const index = selectedItems.vibe[id].indexOf(vibe);
                                if (index > -1) {
                                    selectedItems.vibe[id].splice(index, 1);
                                } else {
                                    selectedItems.vibe[id].push(vibe);
                                }

                                button.style.backgroundColor = selectedItems.vibe[id].includes(vibe) ? '#F4DEB3' : '';
                            });

                            vibeTagsDiv.appendChild(button);
                        });
                    }
                    vibeTitle.appendChild(vibeTagsDiv);

                    const foodTitle = document.createElement('div');
                    foodTitle.className = 'tag-title';
                    foodTitle.textContent = '食物';

                    const foodTagsDiv = document.createElement('div');
                    foodTagsDiv.className = 'food-tags-share';
                    if (restaurantData.r_food_dishes) {
                        const dishes = restaurantData.r_food_dishes.split('、');
                        dishes.forEach(dish => {
                            const button = document.createElement('button');
                            button.className = 'restaurant-tag-share';
                            button.textContent = dish.trim();

                            button.addEventListener('click', function() {
                                if (!selectedItems.food[id]) {
                                    selectedItems.food[id] = [];
                                }

                                const index = selectedItems.food[id].indexOf(dish);
                                if (index > -1) {
                                    selectedItems.food[id].splice(index, 1);
                                } else {
                                    selectedItems.food[id].push(dish);
                                }

                                button.style.backgroundColor = selectedItems.food[id].includes(dish) ? '#F4DEB3' : '';
                            });

                            foodTagsDiv.appendChild(button);
                        });
                    }
                    foodTitle.appendChild(foodTagsDiv);

                    const imageButtonGroup = document.createElement('div');
                    imageButtonGroup.className = 'image-button-group';

                    let selectedCategory = '環境';

                    // 定义 SVG 图标
                    const addSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="256" height="256" viewBox="0 0 256 256" xml:space="preserve">
                        <defs>
                        </defs>
                        <g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)">
                            <path d="M 45 90 C 20.187 90 0 69.813 0 45 C 0 20.187 20.187 0 45 0 c 24.813 0 45 20.187 45 45 C 90 69.813 69.813 90 45 90 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: #C5705D; fill-rule: nonzero; opacity: 1;" transform="matrix(1 0 0 1 0 0)" stroke-linecap="round" />
                            <path d="M 45 70.454 c -2.761 0 -5 -2.238 -5 -5 V 24.545 c 0 -2.761 2.239 -5 5 -5 c 2.762 0 5 2.239 5 5 v 40.909 C 50 68.216 47.762 70.454 45 70.454 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: #fff; fill-rule: nonzero; opacity: 1;" transform="matrix(1 0 0 1 0 0)" stroke-linecap="round" />
                            <path d="M 65.454 50 H 24.545 c -2.761 0 -5 -2.238 -5 -5 c 0 -2.761 2.239 -5 5 -5 h 40.909 c 2.762 0 5 2.239 5 5 C 70.454 47.762 68.216 50 65.454 50 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: #fff; fill-rule: nonzero; opacity: 1;" transform="matrix(1 0 0 1 0 0)" stroke-linecap="round" />
                        </g>
                    </svg>`;


                    const deletSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="256" height="256" viewBox="0 0 256 256" xml:space="preserve">
                        <defs>
                        </defs>
                        <g style="stroke: none; stroke-width: 0; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: none; fill-rule: nonzero; opacity: 1;" transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)" >
                            <circle cx="45" cy="45" r="45" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: #3C3D37; fill-rule: nonzero; opacity: 1;" transform="  matrix(1 0 0 1 0 0) "/>
                            <path d="M 65.592 29.574 h -5.395 h -3.776 v -3.481 c 0 -3.635 -2.957 -6.593 -6.593 -6.593 h -9.656 c -3.635 0 -6.593 2.958 -6.593 6.593 v 3.481 h -3.777 h -5.394 c -1.104 0 -2 0.896 -2 2 s 0.896 2 2 2 h 3.394 V 60.41 c 0 5.563 4.526 10.09 10.09 10.09 h 14.215 c 5.563 0 10.09 -4.526 10.09 -10.09 V 33.574 h 3.395 c 1.104 0 2 -0.896 2 -2 S 66.696 29.574 65.592 29.574 z M 37.579 26.093 c 0 -1.43 1.163 -2.593 2.593 -2.593 h 9.656 c 1.43 0 2.593 1.163 2.593 2.593 v 3.481 H 37.579 V 26.093 z M 58.197 60.41 c 0 3.358 -2.731 6.09 -6.09 6.09 H 37.892 c -3.358 0 -6.09 -2.731 -6.09 -6.09 V 33.574 h 3.777 h 18.842 h 3.776 V 60.41 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
                            <path d="M 40.3 39.566 c -1.104 0 -2 0.896 -2 2 V 56.78 c 0 1.104 0.896 2 2 2 s 2 -0.896 2 -2 V 41.566 C 42.3 40.462 41.404 39.566 40.3 39.566 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
                            <path d="M 49.7 39.566 c -1.104 0 -2 0.896 -2 2 V 56.78 c 0 1.104 0.896 2 2 2 s 2 -0.896 2 -2 V 41.566 C 51.7 40.462 50.805 39.566 49.7 39.566 z" style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-linejoin: miter; stroke-miterlimit: 10; fill: rgb(255,255,255); fill-rule: nonzero; opacity: 1;" transform=" matrix(1 0 0 1 0 0) " stroke-linecap="round" />
                        </g>
                    </svg> `;

                    // 记录各个按钮和 SVG 状态的对象
                    const buttonSvgState = {
                        環境: {
                            isSvgToggled: false
                        },
                        食物: {
                            isSvgToggled: false
                        },
                        菜單: {
                            isSvgToggled: false
                        },
                        地圖: {
                            isSvgToggled: false
                        }
                    };

                    const imageDisplayContainer = document.createElement('div');
                    imageDisplayContainer.className = 'image-display-container';

                    const imageContainer = document.createElement('div');
                    imageContainer.className = 'image-container-share';
                    imageContainer.innerHTML = `<span class="nav-arrow prev" onclick="changeImage(this, -1, ${index})">‹</span>
                            <img src="default.jpg" class="displayed-img displayed-img-${index}" style="object-fit: cover;">
                            <span class="nav-arrow next" onclick="changeImage(this, 1, ${index})">›</span>`;

                    const cornerSvgContainer = document.createElement('div');
                    cornerSvgContainer.className = 'corner-svg';
                    cornerSvgContainer.innerHTML = addSvg;


                    setTimeout(() => {
                        updateImage('環境', restaurantData, index);
                    }, 100); // 延遲 100 毫秒更新圖片



                    const imageToggleButtons = ['環境', '食物', '菜單', '地圖'].map(category => {
                        const button = document.createElement('button');
                        button.className = 'image-toggle-button';
                        button.textContent = category;

                        // 一开始直接加载 '环境' 类别的图片
                        if (category === selectedCategory) {
                            button.classList.add('selected');
                            updateImage('環境', restaurantData, index); // 默认加载环境图片
                            cornerSvgContainer.innerHTML = buttonSvgState[category].isSvgToggled ? deletSvg : addSvg;
                        }

                        button.addEventListener('click', function() {
                            // 切换选中的按钮
                            document.querySelectorAll('.image-toggle-button').forEach(btn => btn.classList.remove('selected'));
                            button.classList.add('selected');

                            // 更新图片
                            updateImage(category, restaurantData, index);

                            // 更新角标 SVG 和按钮背景颜色
                            cornerSvgContainer.innerHTML = buttonSvgState[category].isSvgToggled ? deletSvg : addSvg;
                            button.style.backgroundColor = buttonSvgState[category].isSvgToggled ? '#F4DEB3' : '';
                        });

                        return button;
                    });

                    // 將圖像按鈕組與圖像容器添加到佈局
                    rightColumn.appendChild(imageButtonGroup);
                    imageToggleButtons.forEach(button => imageButtonGroup.appendChild(button));
                    imageContainer.appendChild(cornerSvgContainer);
                    imageDisplayContainer.appendChild(imageContainer);
                    rightColumn.appendChild(imageDisplayContainer);

                    // 处理角标 SVG 点击事件
                    cornerSvgContainer.addEventListener('click', function() {
                        const selectedButton = document.querySelector('.image-toggle-button.selected');
                        if (selectedButton) {
                            const category = selectedButton.textContent;
                            buttonSvgState[category].isSvgToggled = !buttonSvgState[category].isSvgToggled;
                            cornerSvgContainer.innerHTML = buttonSvgState[category].isSvgToggled ? deletSvg : addSvg;
                            selectedButton.style.backgroundColor = buttonSvgState[category].isSvgToggled ? '#F4DEB3' : '';
                        }
                    });


                    buttonGroup.appendChild(priceButton); // 添加价钱按钮
                    buttonGroup.appendChild(diningTimeButton); // 添加用餐时间按钮
                    buttonGroup.appendChild(parkingButton); // 添加停车场按钮
                    leftColumn.appendChild(buttonGroup);

                    middleColumn.appendChild(vibeTitle);
                    middleColumn.appendChild(foodTitle);
                    middleColumn.appendChild(visPicDiv);

                    // 将元素加入到餐厅条目中     
                    restaurantTitle.appendChild(titleDiv);
                    restaurantTitle.appendChild(restaurantItem);
                    restaurantItem.appendChild(leftColumn);
                    restaurantItem.appendChild(middleColumn);
                    restaurantItem.appendChild(rightColumn);

                    console.log('Environment Images:', restaurantData.r_photo_env1, restaurantData.r_photo_env2, restaurantData.r_photo_env3);
                    console.log('Food Images:', restaurantData.r_photo_food1, restaurantData.r_photo_food2, restaurantData.r_photo_food3);

                    // 添加到分享内容容器中
                    shareContent.appendChild(restaurantTitle);
                }
            });

            document.getElementById("sharePanel").style.display = "block";
            document.querySelector(".container").classList.add("blur-background");
            document.getElementById("sharePanel").style.zIndex = "1001";
        });

        document.getElementById("closePanelButton").addEventListener("click", function() {
            // 隐藏分享面板
            document.getElementById("sharePanel").style.display = "none";

            // 移除 container 內的模糊效果
            document.querySelector(".container").classList.remove("blur-background");
        });

        document.getElementById("finalShareButton").addEventListener("click", function() {
            const shareLink = generateShareLink(selectedItems, selectedRestaurants);
            console.log('Selected Restaurants:', selectedItems);

            // 重定向用户到生成的 URL
            window.open(shareLink, '_blank');
        });

        function generateShareLink(selectedItems, selectedRestaurants) {
            let queryString = '';

            // 遍历所有被选中的餐厅
            selectedRestaurants.forEach((id, index) => {
                // 将r_id也添加到查询字符串中
                queryString += `r_id${index + 1}=${id}&`;
            });

            // 将 selectedItems 的对象转换为 JSON 字符串并进行 URL 编码
            const vibeString = `vibe=${encodeURIComponent(JSON.stringify(selectedItems.vibe))}`;
            const foodString = `food=${encodeURIComponent(JSON.stringify(selectedItems.food))}`;
            const priceString = `price=${encodeURIComponent(JSON.stringify(selectedItems.price))}`;
            const diningTimeString = `diningTime=${encodeURIComponent(JSON.stringify(selectedItems.diningTime))}`;
            const parkingString = `parking=${encodeURIComponent(JSON.stringify(selectedItems.parking))}`;
            const spiderString = `spider=${encodeURIComponent(JSON.stringify(selectedItems.spider))}`;
            const commentString = `comment=${encodeURIComponent(JSON.stringify(selectedItems.comment))}`;
            const openTimeString = `openTime=${encodeURIComponent(JSON.stringify(selectedItems.openTime))}`;

            // 将这些字符串合并到一个查询字符串中
            queryString += `${vibeString}&${foodString}&${priceString}&${diningTimeString}&${parkingString}&${spiderString}&${commentString}&${openTimeString}`;

            // 返回完整的 URL
            return `../cellphone/cellphone.php?${queryString}`;
        }

        function updateImage(category, restaurantData, index) {
            let images = [];
            switch (category) {
                case '環境':
                    images = [restaurantData.r_photo_env1, restaurantData.r_photo_env2, restaurantData.r_photo_env3, restaurantData.r_photo_door];
                    break;
                case '食物':
                    images = [restaurantData.r_photo_food1, restaurantData.r_photo_food2, restaurantData.r_photo_food3, restaurantData.r_photo_food4, restaurantData.r_photo_food5];
                    break;
                case '菜單':
                    images = [restaurantData.r_photo_menu1, restaurantData.r_photo_menu2, restaurantData.r_photo_menu3];
                    break;
                case '地圖':
                    images = ['map.jpg']; // 假设您有一个地图图片的占位符
                    break;
            }
            const displayedImg = document.querySelector(`.displayed-img-${index}`);
            console.log('displayedImg:', displayedImg); // 確認是否找到正確的元素

            if (displayedImg) { // 检查元素是否存在
                displayedImg.src = images[0] || 'spider.png';
                displayedImg.dataset.images = JSON.stringify(images);
                displayedImg.dataset.index = 0;
            }
        }


        function changeImage(arrow, direction, index) {
            const displayedImg = document.querySelector(`.displayed-img-${index}`);
            if (displayedImg) { // 检查元素是否存在
                let images = JSON.parse(displayedImg.dataset.images || '[]');
                let currentIndex = parseInt(displayedImg.dataset.index, 10);
                currentIndex = (currentIndex + direction + images.length) % images.length;
                displayedImg.src = images[currentIndex] || 'default.jpg';
                displayedImg.dataset.index = currentIndex;
            }
        }
    </script>
</body>

</html>