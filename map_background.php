<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="compare_map.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="leaflet_edgeMarker.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <style>
        /* 定義CSS樣式 */
        .custom-icon {
            width: 40px;
            height: 40px;
        }

        #map {
            width: 1200px;
            height: 800px;
        }

        /* 返回按鈕樣式 */
        #backButton {
            position: fixed;
            top: 17px;
            /* 與minimap頂部對齊 */
            left: 60px;
            /* 距離左側10px */
            z-index: 1001;
            /* 確保在minimap和地圖之上 */
            background-color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 10px;
            cursor: pointer;
            font-size:1.2rem;
        }

        #backButton:hover{
            background-color: #ccc;
        }
        #minimap-container {
            width: 45%;
            /* 調整寬度以容納返回按鈕 */
            height: 10%;
            position: fixed;
            z-index: 1000;
            /* 確保它在 map 之上 */
            background-color: white;
            /* 添加背景颜色以提高可见性 */
            top: 10px;
            /* 與返回按鈕對齊 */
            right: 150px;
            /* 再往右移，為返回按鈕留出更多空間 */
            border: 1px solid #ccc;
            /* 添加邊框讓它更明顯 */
            padding: 10px;
            /* 內邊距以提供更多空間 */
        }

        /* Minimap 標籤樣式 */
        #minimap-container h4 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
    </style>
</head>

<body>
    <?php
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=foodee;charset=utf8', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("数据库连接失败: " . $e->getMessage());
    }

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

    // 如果有提供 restaurant_ids 參數
    if (isset($_GET['restaurant_ids'])) {
        $restaurant_ids = explode(',', $_GET['restaurant_ids']);  // 将逗号分隔的餐厅 ID 转换为数组

        // 根据ID数量生成占位符
        $placeholders = implode(',', array_fill(0, count($restaurant_ids), '?'));

        // 将条件附加到现有查询中
        $sql .= " AND additional.r_id IN ($placeholders)";

        // 準備 SQL 查詢
        $stmt = $pdo->prepare($sql);

        // 執行查詢
        $stmt->execute($restaurant_ids);

        // 获取查询结果
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 將餐廳數據傳遞給 JavaScript
        echo "<script>";
        echo "const restaurantData = " . json_encode($restaurants) . ";";
        echo "</script>";
    }

    ?>
    <!-- 返回按鈕 -->
    <button id="backButton" onclick="href">返回</button>
    <!-- 地圖區域 -->
    <div id="map"></div>
    <!-- Minimap區域 -->
    <div id="minimap-container">
        <!-- Minimap 將在這裡繪製 -->
    </div>
    </div>
    <script>
        // 顏色映射
        let filterRestaurants = []
        const sortedTimes = ['60', '90', '100', '120', '150', ''];
        const colors = d3.scaleOrdinal()
            .domain(sortedTimes)  // sortedTimes 是你的時間數據
            .range(['#d6af99', '#a2cdab', '#7ea1dd', '#fdc85e', '#e67575', '#b295ab']); 
        // 初始化地圖
        const map = L.map('map').setView([22.631386, 120.301951], 13);
        L.tileLayer('https://api.maptiler.com/maps/dataviz/{z}/{x}/{y}.png?key=nVkGoaPMkOqdVRLChAnz', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.maptiler.com/">MapTiler</a> contributors'
        }).addTo(map);

        document.getElementById('backButton').addEventListener('click', function() {
            window.location.href = 'final.html'; // 跳转到scale2.html
        });

        // 更新餐厅图标的大小
        function updateIconSize(marker, zoomLevel) {
            const size = zoomLevel * 5; // 调整倍率，按需修改
            const newIcon = marker.options.icon;
            newIcon.options.iconSize = [size, size];
            newIcon.options.iconAnchor = [size / 2, size / 2]; // 确保图标中心点正确对齐
            marker.setIcon(newIcon);
        }

        // 创建自定义的 Leaflet 图标
        function createLeafletD3Icon(restaurant) {
            const iconHtml = createD3Icon(restaurant);
            return L.divIcon({
                className: 'custom-icon',
                html: iconHtml,
                iconAnchor: [20, 20] // 默认图标中心点
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const restaurantIdsParam = urlParams.get('restaurant_ids');
            let restaurantIds = [];

            if (restaurantIdsParam) {
                restaurantIds = restaurantIdsParam.split(',');
            }

            // 加载餐厅数据并使用自定义图标
            fetch('../connect_sql/get_data_json.php')
                .then(response => response.json())
                .then(data => {
                    let filteredRestaurants = data;

                    // 如果有指定餐厅 ID 参数，则过滤餐厅数据
                    if (restaurantIds.length > 0) {
                        filteredRestaurants = data.filter(restaurant => restaurantIds.includes(restaurant.r_id.toString()));
                    }

                    // 如果过滤后的餐厅数量为 0，显示所有餐厅
                    if (filteredRestaurants.length === 0) {
                        console.log('没有符合条件的餐厅，显示所有餐厅。');
                        filteredRestaurants = data;
                    }

                    // 计算并归类用餐时间
                    const timeCategories = ['60', '90', '100', '120', '150', ''];
                    const sortedTimes = timeCategories.map(time => ({
                        time: time,
                        count: filteredRestaurants.filter(restaurant => restaurant.r_time_low === time).length
                    }));

                    // 绘制Minimap
                    drawMinimap(sortedTimes);

                    // 显示餐厅数据
                    filteredRestaurants.forEach((restaurant, index) => {
                        if (restaurant && Object.keys(restaurant).length > 0) {
                            var marker = L.marker([restaurant.r_latitude, restaurant.r_longitude], {
                                icon: createLeafletD3Icon(restaurant)
                            }).addTo(map);

                            updateIconSize(marker, map.getZoom());

                            map.on('zoomend', function() {
                                updateIconSize(marker, map.getZoom());
                            });

                            marker.bindPopup(`
                        <div style="width: 300px; max-width: 300px; padding: 10px;">
                            <div style="display: flex; align-items: center;">
                                <div id="carousel-${restaurant.r_id}" class="carousel" style="flex: 0 0 120px; margin-right: 10px;">
                                    <img src="${restaurant.r_photo_env1}" alt="環境照片1" style="width: 120px; height: 150px; border-radius: 8px; object-fit: cover;">
                                    <img src="${restaurant.r_photo_env2}" alt="環境照片2" style="width: 120px; height: 150px; border-radius: 8px; display: none; object-fit: cover;">
                                    <img src="${restaurant.r_photo_env3}" alt="環境照片3" style="width: 120px; height: 150px; border-radius: 8px; display: none; object-fit: cover;">
                                </div>
                                <div style="flex: 1;">
                                    <b style="font-size: 16px;">${restaurant.r_name}</b><br>
                                    <b style="font-size: 14px; color: #FFD700;">評分: ${restaurant.r_rating} 星</b><br>
                                    <div style="margin-top: 5px; word-wrap: break-word;">
                                        ${restaurant.r_food_dishes.split('、').slice(0, 3).map(dish => `
                                            <span style="background-color: #FFD700; padding: 2px 5px; margin-right: 3px; margin-bottom: 3px; font-size: 12px; border-radius: 5px; display: inline-block;">
                                                ${dish}
                                            </span>
                                        `).join('')}
                                    </div>
                                    <div id="bar-chart-${restaurant.r_id}" style="margin-top: 10px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    `);

                            marker.on('popupopen', function() {
                                const carousel = document.querySelector(`#carousel-${restaurant.r_id}`);
                                const items = carousel.querySelectorAll('img');
                                items.forEach((item, index) => {
                                    item.style.display = index === 0 ? 'block' : 'none';
                                });

                                let currentIndex = 0;
                                if (items.length > 1) {
                                    setInterval(() => {
                                        items[currentIndex].style.display = 'none';
                                        currentIndex = (currentIndex + 1) % items.length;
                                        items[currentIndex].style.display = 'block';
                                    }, 2000);
                                }

                                const ratingHTML = `
                              <div style="display: flex; align-items: center; margin-bottom: 0px; font-size: 12px; max-width: 200px;">
                                <span style="width: 40px;">服務:</span>
                                <div style="background-color: #ccc; width: 100px; height: 8px; border-radius: 5px; overflow: hidden; position: relative;">
                                    <div style="background-color: gold; width: ${restaurant.r_rate_service * 20}px; height: 8px;"></div>
                                </div>
                                <span style="margin-left: 5px; white-space: nowrap;">${restaurant.r_rate_service} 星</span>
                            </div>
                            <div style="display: flex; align-items: center; margin-bottom: 0px; font-size: 12px; max-width: 200px;">
                                <span style="width: 40px;">食物:</span>
                                <div style="background-color: #ccc; width: 100px; height: 8px; border-radius: 5px; overflow: hidden; position: relative;">
                                    <div style="background-color: gold; width: ${restaurant.r_rating_food * 20}px; height: 8px;"></div>
                                </div>
                                <span style="margin-left: 5px; white-space: nowrap;">${restaurant.r_rating_food} 星</span>
                            </div>
                            <div style="display: flex; align-items: center; margin-bottom: 0px; font-size: 12px; max-width: 200px;">
                                <span style="width: 40px;">環境:</span>
                                <div style="background-color: #ccc; width: 100px; height: 8px; border-radius: 5px; overflow: hidden; position: relative;">
                                    <div style="background-color: gold; width: ${restaurant.r_rate_atmosphere * 20}px; height: 8px;"></div>
                                </div>
                                <span style="margin-left: 5px; white-space: nowrap;">${restaurant.r_rate_atmosphere} 星</span>
                            </div>
                            <div style="display: flex; align-items: center; font-size: 12px; max-width: 200px;">
                                <span style="width: 40px;">衛生:</span>
                                <div style="background-color: #ccc; width: 100px; height: 8px; border-radius: 5px; overflow: hidden; position: relative;">
                                    <div style="background-color: gold; width: ${restaurant.r_rate_clean * 20}px; height: 8px;"></div>
                                </div>
                                <span style="margin-left: 5px; white-space: nowrap;">${restaurant.r_rate_clean} 星</span>
                            </div>`;

                                const popupContent = document.querySelector(`#bar-chart-${restaurant.r_id}`);
                                popupContent.innerHTML = ratingHTML;
                            });
                        }
                    });
                })
                .catch(error => console.error('Error loading restaurant data:', error));
        });
        let selectedTime = null; // 存储当前选中的时间

        // 绘制Minimap函数
        function drawMinimap(data) {
            const container = document.getElementById('minimap-container');
            const width = container.clientWidth; // 使得minimap的宽度与container一致
            const height = container.clientHeight;
            const paddingTop = 30; // 给顶部留出更多空间
            const paddingBottom = 50; // 给底部留出足够的空间用于显示text
            const barPadding = 0.2; // bar之间的间隔

            const svg = d3.select("#minimap-container").append("svg")
                .attr("width", width)
                .attr("height", height);

            const xScale = d3.scaleBand()
                .domain(data.map(d => d.time))
                .range([0, width])
                .padding(barPadding); // 调整bar之间的间隔

            const yScale = d3.scaleLinear()
                .domain([0, d3.max(data, d => d.count)])
                .range([height - paddingBottom, paddingTop]); // 调整顶部和底部的空间

            // 绘制条形图
            const bars = svg.selectAll(".bar")
                .data(data)
                .enter()
                .append("rect")
                .attr("class", "bar")
                .attr("x", d => xScale(d.time))
                .attr("y", d => yScale(d.count))
                .attr("width", xScale.bandwidth())
                .attr("height", d => height - paddingBottom - yScale(d.count))
                .attr("fill", d => colors(d.time))
                .on("click", function(event, d) {
                    const time = d.time === '' ? '無限制' : d.time;  // 把空值時間映射為 '無限制'

                    // 如果当前时间已经被选中，再次点击则取消选中
                    if (selectedTime === time) {
                        selectedTime = null; // 取消選擇
                        filterRestaurantsByTime(null); // 顯示所有餐廳
                        d3.selectAll(".bar").attr("fill", d => colors(d.time === '' ? '無限制' : d.time)); // 還原顏色
                    } else {
                        selectedTime = time;
                        filterRestaurantsByTime(time); // 根據選中的時間篩選餐廳
                        d3.selectAll(".bar").attr("fill", d => colors(d.time === '' ? '無限制' : d.time)); // 還原所有條形圖顏色
                        d3.select(this).attr("fill", "orange"); // 選中的條形圖高亮顯示
                    }
                })

                .on("mouseover", function(event, d) {
                    // 只在未選中的情況下顯示 hover 效果
                    if (selectedTime !== d.time) {
                        d3.select(this).attr("fill", "lightblue");
                    }
                })
                .on("mouseout", function(event, d) {
                    // 還原 hover 後的顏色，只還原非選中的條形圖
                    if (selectedTime !== d.time) {
                        d3.select(this).attr("fill", colors(d.time));
                    }
                });

            // 添加显示时间的标签，在每个bar的下方
            const labels = svg.selectAll(".label")
                .data(data)
                .enter()
                .append("g")
                .attr("transform", d => `translate(${xScale(d.time) + xScale.bandwidth() / 2}, ${height - paddingBottom + 30})`);

            labels.append("text")
                .attr("text-anchor", "middle")
                .attr("fill", "#000")
                .text(d => d.time === '' ? '無限制' : `${d.time} 分鐘`);

            // 添加显示数量的标签，在每个bar的上方
            svg.selectAll(".count")
                .data(data)
                .enter()
                .append("text")
                .attr("x", d => xScale(d.time) + xScale.bandwidth() / 2)
                .attr("y", d => yScale(d.count) - 5)
                .attr("text-anchor", "middle")
                .attr("fill", "#000")
                .text(d => d.count);
        }

        // 重置所有標記
        function resetMarkers() {
            map.eachLayer(function(layer) {
                if (layer instanceof L.Marker) {
                    map.removeLayer(layer);
                }
            });
        }

        function filterRestaurantsByTime(time) {
            resetMarkers(); // 先清除所有標記

            // 確保 filteredRestaurants 是基於所有餐廳數據的副本，而不是直接修改它
            let filteredList = restaurantData;

            // 如果 time 為 null，表示取消篩選，顯示所有餐廳
            if (!time) {
                filteredList = restaurantData; // 顯示所有餐廳
            } else {
                if (time === '') {
                    time = '無限制'; // 將空時間轉換為 '無限制'
                }

                // 根據選定的時間篩選餐廳
                filteredList = filteredList.filter(restaurant => {
                    if (restaurant.r_time_low === '' || restaurant.r_time_low === null) {
                        // 如果餐廳時間為空值，並且 time 為 '無限制'，則保留這些餐廳
                        return time === '無限制';
                    } else {
                        // 如果 time 不為 '無限制'，則篩選符合條件的餐廳
                        return parseInt(restaurant.r_time_low) === parseInt(time);
                    }
                });
            }

            console.log("Filtering restaurants by time:", time);  // 確認 time 的值
            console.log("Filtered restaurants:", filteredList);    // 查看過濾後的餐廳列表

            // 迭代篩選後的餐廳並在地圖上顯示它們
            filteredList.forEach(restaurant => {
                const marker = L.marker([restaurant.r_latitude, restaurant.r_longitude], {
                    icon: createLeafletD3Icon(restaurant)
                }).addTo(map);

                // 綁定 popupopen 事件
                marker.bindPopup(`
                    <div style="width: 300px; max-width: 300px; padding: 10px;">
                        <div style="display: flex; align-items: center;">
                            <div id="carousel-${restaurant.r_id}" class="carousel" style="flex: 0 0 120px; margin-right: 10px;">
                                <img src="${restaurant.r_photo_env1}" alt="環境照片1" style="width: 120px; height: 150px; border-radius: 8px; object-fit: cover;">
                                <img src="${restaurant.r_photo_env2}" alt="環境照片2" style="width: 120px; height: 150px; border-radius: 8px; display: none; object-fit: cover;">
                                <img src="${restaurant.r_photo_env3}" alt="環境照片3" style="width: 120px; height: 150px; border-radius: 8px; display: none; object-fit: cover;">
                            </div>
                            <div style="flex: 1;">
                                <b style="font-size: 16px;">${restaurant.r_name}</b><br>
                                <b style="font-size: 14px; color: #FFD700;">評分: ${restaurant.r_rating} 星</b><br>
                                <div style="margin-top: 5px; word-wrap: break-word;">
                                    ${restaurant.r_food_dishes.split('、').slice(0, 3).map(dish => `
                                        <span style="background-color: #FFD700; padding: 2px 5px; margin-right: 3px; margin-bottom: 3px; font-size: 12px; border-radius: 5px; display: inline-block;">
                                            ${dish}
                                        </span>
                                    `).join('')}
                                </div>
                                <div id="bar-chart-${restaurant.r_id}" style="margin-top: 10px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                `);

                marker.on('popupopen', function() {
                    const carousel = document.querySelector(`#carousel-${restaurant.r_id}`);
                    const items = carousel.querySelectorAll('img');
                    items.forEach((item, index) => {
                        item.style.display = index === 0 ? 'block' : 'none';
                    });

                    let currentIndex = 0;
                    if (items.length > 1) {
                        setInterval(() => {
                            items[currentIndex].style.display = 'none';
                            currentIndex = (currentIndex + 1) % items.length;
                            items[currentIndex].style.display = 'block';
                        }, 2000);
                    }

                    const ratingHTML = `
                        <div style="display: flex; align-items: center; margin-bottom: 0px; font-size: 12px; max-width: 200px;">
                            <span style="width: 40px;">服務:</span>
                            <div style="background-color: #ccc; width: 100px; height: 8px; border-radius: 5px; overflow: hidden; position: relative;">
                                <div style="background-color: gold; width: ${restaurant.r_rate_service * 20}px; height: 8px;"></div>
                            </div>
                            <span style="margin-left: 5px; white-space: nowrap;">${restaurant.r_rate_service} 星</span>
                        </div>
                        <div style="display: flex; align-items: center; margin-bottom: 0px; font-size: 12px; max-width: 200px;">
                            <span style="width: 40px;">食物:</span>
                            <div style="background-color: #ccc; width: 100px; height: 8px; border-radius: 5px; overflow: hidden; position: relative;">
                                <div style="background-color: gold; width: ${restaurant.r_rating_food * 20}px; height: 8px;"></div>
                            </div>
                            <span style="margin-left: 5px; white-space: nowrap;">${restaurant.r_rating_food} 星</span>
                        </div>
                        <div style="display: flex; align-items: center; margin-bottom: 0px; font-size: 12px; max-width: 200px;">
                            <span style="width: 40px;">環境:</span>
                            <div style="background-color: #ccc; width: 100px; height: 8px; border-radius: 5px; overflow: hidden; position: relative;">
                                <div style="background-color: gold; width: ${restaurant.r_rate_atmosphere * 20}px; height: 8px;"></div>
                            </div>
                            <span style="margin-left: 5px; white-space: nowrap;">${restaurant.r_rate_atmosphere} 星</span>
                        </div>
                        <div style="display: flex; align-items: center; font-size: 12px; max-width: 200px;">
                            <span style="width: 40px;">衛生:</span>
                            <div style="background-color: #ccc; width: 100px; height: 8px; border-radius: 5px; overflow: hidden; position: relative;">
                                <div style="background-color: gold; width: ${restaurant.r_rate_clean * 20}px; height: 8px;"></div>
                            </div>
                            <span style="margin-left: 5px; white-space: nowrap;">${restaurant.r_rate_clean} 星</span>
                        </div>`;
                    const popupContent = document.querySelector(`#bar-chart-${restaurant.r_id}`);
                    popupContent.innerHTML = ratingHTML;
                });
            });
        }


        function createD3Icon(restaurant) {
            const svg = d3.create("svg")
                .attr("width", 60) // 增大 SVG 宽度和高度，给背景圆圈留出空间
                .attr("height", 60)
                .attr("viewBox", "0 0 60 60");

            // 根據餐廳的用餐時間設置顏色，默認使用空字串
            const diningTime = restaurant.r_time_low || ''; // 根據你餐廳數據中的用餐時間字段設置
            const backgroundColor = colors(diningTime); // 使用顏色映射

            // 添加半透明的背景圓圈來表示用餐時間
            svg.append("circle")
                .attr("cx", 30) // 设为 SVG 的中心
                .attr("cy", 30)
                .attr("r", 25) // 半径比实际标记圆圈大一些
                .attr("fill", backgroundColor)
                .attr("opacity", 0.7); // 半透明效果

            // 定义圆形遮罩
            svg.append("defs").append("clipPath")
                .attr("id", "circle-clip")
                .append("circle")
                .attr("cx", 30)
                .attr("cy", 30)
                .attr("r", 20);

            // 定义图片，使用clipPath裁剪
            svg.append("image")
                .attr("xlink:href", restaurant.r_photo_env1)
                .attr("width", 40)
                .attr("height", 40)
                .attr("x", 10)
                .attr("y", 10)
                .attr("clip-path", "url(#circle-clip)")
                .attr("preserveAspectRatio", "xMidYMid slice");

            // 添加圆形边框
            svg.append("circle")
                .attr("cx", 30)
                .attr("cy", 30)
                .attr("r", 20)
                .attr("stroke", "black")
                .attr("stroke-width", 2)
                .attr("fill", "none");

            // 添加星星图标
            const smallCircleRadius = 5;
            const iconOffset = 12;

            svg.append('circle')
                .attr('cx', 30 - iconOffset)
                .attr('cy', 30 + iconOffset)
                .attr('r', smallCircleRadius)
                .attr('fill', 'white')
                .attr('stroke', 'black')
                .attr('stroke-width', '1px');

            svg.append("text")
                .attr("x", 30 - iconOffset)
                .attr("y", 30 + iconOffset)
                .attr("text-anchor", "middle")
                .attr("alignment-baseline", "middle")
                .attr("font-size", "10px")
                .attr("class", "fas fa-star")
                .attr("fill", "#FFD400")
                .text('\uf005'); // Font Awesome - star (Unicode)

            svg.append("text")
                .attr("x", 30 - iconOffset)
                .attr("y", 30 + iconOffset)
                .attr("text-anchor", "middle")
                .attr("alignment-baseline", "middle")
                .attr("font-size", "8px")
                .attr("font-weight", "bold")
                .text(restaurant.r_rating !== undefined ? restaurant.r_rating : 'N/A');

            // 添加停车图标
            svg.append('circle')
                .attr('cx', 30 + iconOffset)
                .attr('cy', 30 + iconOffset)
                .attr('r', smallCircleRadius)
                .attr('fill', 'white')
                .attr('stroke', 'black')
                .attr('stroke-width', '1px');

            svg.append("text")
                .attr("x", 30 + iconOffset)
                .attr("y", 30 + iconOffset)
                .attr("text-anchor", "middle")
                .attr("alignment-baseline", "middle")
                .attr("font-size", "10px")
                .attr("class", "fas fa-parking")
                .attr('fill', restaurant.r_has_parking == 1 ? 'blue' : 'lightgrey')
                .text('\uf540'); // Font Awesome - parking (Unicode)

            return svg.node().outerHTML;
        }

        // 添加捷运和轻轨线路和标记
        fetch('../connect_sql/get_data_map_json.php')
            .then(response => response.json())
            .then(data => {
                var LRT_points = [];
                var MRT_O_points = [];
                var MRT_R_points = [];
                data.forEach(function(transportation) {
                    var circle = null;
                    if (transportation.id.includes('C')) {
                        LRT_points.push([transportation.latitude, transportation.longitude]);
                        L.polyline(LRT_points, {
                            color: 'green',
                            weight: 2
                        }).addTo(map);
                        circle = L.circleMarker([transportation.latitude, transportation.longitude], {
                            color: 'green',
                            fillColor: 'green',
                            fillOpacity: 0.9,
                            radius: 8 // 使用circleMarker，确保大小不受缩放影响
                        }).addTo(map);
                    } else if (transportation.id.includes('O')) {
                        MRT_O_points.push([transportation.latitude, transportation.longitude]);
                        L.polyline(MRT_O_points, {
                            color: 'orange',
                            weight: 2
                        }).addTo(map);
                        circle = L.circleMarker([transportation.latitude, transportation.longitude], {
                            color: 'orange',
                            fillColor: 'orange',
                            fillOpacity: 0.9,
                            radius: 8
                        }).addTo(map);
                    } else {
                        MRT_R_points.push([transportation.latitude, transportation.longitude]);
                        L.polyline(MRT_R_points, {
                            color: 'red',
                            weight: 2
                        }).addTo(map);
                        circle = L.circleMarker([transportation.latitude, transportation.longitude], {
                            color: 'red',
                            fillColor: '#f03',
                            fillOpacity: 0.9,
                            radius: 8
                        }).addTo(map);
                    }
                    circle.bindPopup("name: " + transportation.name).openPopup();
                });
            })
            .catch(error => console.error('Error loading MRT/LRT data:', error));

        // 添加 EdgeMarker 箭头
        var edgeMarkerLayer = L.edgeMarker({
            icon: L.icon({
                iconUrl: 'edge_arrow_marker.png',
                clickable: true,
                iconSize: [25, 25],
                iconAnchor: [10, 10]
            }),
            rotateIcons: true,
            layerGroup: null
        }).addTo(map);

        // 餐廳和捷運輕軌連線
        fetch('../connect_sql/get_data_map_json.php')
            .then(response => response.json())
            .then(restaurants => {
                fetch('../connect_sql/get_data_map_json.php')
                    .then(response => response.json())
                    .then(transportations => {
                        const stationMap = {};
                        transportations.forEach(station => {
                            stationMap[station.name] = {
                                lat: station.latitude,
                                lng: station.longitude
                            };
                        });

                        const lineGroup = L.layerGroup().addTo(map);

                        restaurants.forEach(restaurant => {
                            const restaurantLatLng = [restaurant.r_latitude, restaurant.r_longitude];

                            let shortestDistance = Infinity;
                            let shortestLine = null;

                            if (restaurant.r_MRT) {
                                const mrtStationName = restaurant.r_MRT.replace('站', '');
                                const mrtStation = stationMap[mrtStationName];
                                if (mrtStation) {
                                    const mrtDistance = parseFloat(restaurant.r_MRT_dist_km);
                                    if (mrtDistance < shortestDistance) {
                                        shortestDistance = mrtDistance;
                                        shortestLine = L.polyline([restaurantLatLng, [mrtStation.lat, mrtStation.lng]], {
                                            color: 'blue'
                                        });
                                    }
                                }
                            }

                            if (restaurant.r_LRT) {
                                const lrtStationName = restaurant.r_LRT;
                                const lrtStation = stationMap[lrtStationName];
                                if (lrtStation) {
                                    const lrtDistance = parseFloat(restaurant.r_LRT_dist_km);
                                    if (lrtDistance < shortestDistance) {
                                        shortestDistance = lrtDistance;
                                        shortestLine = L.polyline([restaurantLatLng, [lrtStation.lat, lrtStation.lng]], {
                                            color: 'green'
                                        });
                                    }
                                }
                            }

                            if (shortestLine) {
                                lineGroup.addLayer(shortestLine);
                            }
                        });

                        const zoomThreshold = 15;

                        map.on('zoomend', () => {
                            if (map.getZoom() >= zoomThreshold) {
                                map.addLayer(lineGroup);
                            } else {
                                map.removeLayer(lineGroup);
                            }
                        });

                        if (map.getZoom() >= zoomThreshold) {
                            map.addLayer(lineGroup);
                        } else {
                            map.removeLayer(lineGroup);
                        }
                    })
                    .catch(error => console.error('Error loading MRT/LRT data:', error));
            })
            .catch(error => console.error('Error loading restaurant data:', error));
    </script>
</body>

</html>