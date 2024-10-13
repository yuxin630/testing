<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" /> <!--leaflet css file-->
    <link rel="stylesheet" src="compare_map.css">
    <!-- Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- edgeMarker -->
    <script src="../map/leaflet_edgeMarker.js"></script>
    <title>compare_map</title>
</head>

<body>
    <div id="map" ></div>

    <script>

        // 地圖的點應該要是變數 看使用者選到哪一家店 以那家店為中心
        const map = L.map('map').setView([22.631386, 120.301951], 13);

        const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        // 從JSON文件加載數據並顯示在地圖上
        fetch('../connect_sql/get_data_json.php')
            .then(response => response.json())
            .then(data => {
                console.log(data);
                data.forEach(function (restaurants) {
                    var marker = L.marker([restaurants.r_latitude, restaurants.r_longitude], {
                        isRestaurant: true // 設置 isRestaurant 屬性 讓edgeMarker辨別是餐廳
                    }).addTo(map);

                    // 顯示餐廳名稱
                    marker.bindPopup("name: " + restaurants.r_name).openPopup();
                });
            })
            .catch(error => console.error('Error loading restaurant data:', error));

        //加上捷運輕軌
        fetch('../connect_sql/get_data_map_json.php')
            .then(response => response.json())
            .then(data => {
                var LRT_points = []
                var MRT_O_points = []
                var MRT_R_points = []
                var MRT = L.icon({
                    iconUrl: 'mrt.png',
                    iconSize: [25, 25], // size of the icon
                    // iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
                    // shadowAnchor: [4, 62],  // the same for the shadow
                    // popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
                });
                console.log(data);
                data.forEach(function (transportation) {
                    if (transportation.id.includes('C')) {
                        LRT_points.push([transportation.latitude, transportation.longitude])
                        L.polyline(LRT_points, {
                            color: 'green',
                            weight: 2
                        }).addTo(map)
                        var circle = L.circle([transportation.latitude, transportation.longitude], {
                            color: 'green',
                            fillColor: 'green',
                            fillOpacity: 0.9,
                            radius: 35
                        }).addTo(map);

                    }
                    else if (transportation.id.includes('O')) {
                        MRT_O_points.push([transportation.latitude, transportation.longitude])
                        L.polyline(MRT_O_points, {
                            color: 'orange',
                            weight: 2
                        }).addTo(map);
                        // L.marker([transportation.latitude, transportation.longitude], {icon: MRT}).addTo(map);
                        var circle = L.circle([transportation.latitude, transportation.longitude], {
                            color: 'orange',
                            fillColor: 'orange',
                            fillOpacity: 0.9,
                            radius: 35
                        }).addTo(map)
                    }
                    else {
                        MRT_R_points.push([transportation.latitude, transportation.longitude])
                        L.polyline(MRT_R_points, {
                            color: 'red',
                            weight: 2
                        }).addTo(map);

                        // L.marker([transportation.latitude, transportation.longitude], {icon: MRT}).addTo(map)
                        var circle = L.circle([transportation.latitude, transportation.longitude], {
                            color: 'red',
                            fillColor: '#f03',
                            fillOpacity: 0.9,
                            radius: 35
                        }).addTo(map)
                    }


                    circle.bindPopup("name: " + transportation.name).openPopup();
                });
            })
            .catch(error => console.error('Error loading restaurant data:', error));

        // add the EdgeMarker to the map. 箭頭
        var edgeMarkerLayer = L.edgeMarker({
            icon: L.icon({ // style markers
                iconUrl: 'edge_arrow_marker.png',
                clickable: true,
                iconSize: [25, 25],
                iconAnchor: [10, 10]
            }),
            rotateIcons: true, // rotate EdgeMarkers depending on their relative position
            layerGroup: null // you can specify a certain L.layerGroup to create the edge markers from.
        }).addTo(map);

        // if you want to remove the edge markers
        // edgeMarkerLayer.destroy();

        // 餐廳和捷運輕軌連線
        fetch('../connect_sql/get_data_json.php')
            .then(response => response.json())
            .then(restaurants => {
                fetch('../connect_sql/get_data_map_json.php')
                    .then(response => response.json())
                    .then(transportations => {
                        // 建立捷運輕軌站點名稱與位置的對應字典
                        const stationMap = {};
                        transportations.forEach(station => {
                            // 保留輕軌站名稱中的"站"
                            stationMap[station.name] = {
                                lat: station.latitude,
                                lng: station.longitude
                            };
                        });

                        // 用於保存所有線條的圖層群組
                        const lineGroup = L.layerGroup().addTo(map);

                        // 繪製餐廳到捷運輕軌站的最短連線
                        restaurants.forEach(restaurant => {
                            // 餐廳位置
                            const restaurantLatLng = [restaurant.r_latitude, restaurant.r_longitude];

                            let shortestDistance = Infinity;
                            let shortestLine = null;

                            console.log(`Processing restaurant: ${restaurant.r_name}`);

                            // 處理捷運站
                            if (restaurant.r_MRT) {
                                const mrtStationName = restaurant.r_MRT.replace('站', '');
                                const mrtStation = stationMap[mrtStationName];
                                if (mrtStation) {
                                    const mrtDistance = parseFloat(restaurant.r_MRT_dist_km);
                                    console.log(`MRT Station: ${mrtStationName}, Distance: ${mrtDistance} km`);
                                    if (mrtDistance < shortestDistance) {
                                        shortestDistance = mrtDistance;
                                        shortestLine = L.polyline([restaurantLatLng, [mrtStation.lat, mrtStation.lng]], {
                                            color: 'blue'
                                        });
                                    }
                                }
                            }

                            // 處理輕軌站
                            if (restaurant.r_LRT) {
                                const lrtStationName = restaurant.r_LRT;
                                const lrtStation = stationMap[lrtStationName];
                                if (lrtStation) {
                                    const lrtDistance = parseFloat(restaurant.r_LRT_dist_km);
                                    console.log(`LRT Station: ${lrtStationName}, Distance: ${lrtDistance} km`);
                                    if (lrtDistance < shortestDistance) {
                                        shortestDistance = lrtDistance;
                                        shortestLine = L.polyline([restaurantLatLng, [lrtStation.lat, lrtStation.lng]], {
                                            color: 'green'
                                        });
                                    }
                                }
                            }

                            // 將最短的線添加到圖層群組中
                            if (shortestLine) {
                                console.log(`Shortest Line for ${restaurant.r_name}: ${shortestDistance} km`);
                                lineGroup.addLayer(shortestLine);
                            }
                        });

                        // 設置當前的放大級別閾值
                        const zoomThreshold = 15;

                        // 當地圖縮放級別改變時觸發事件
                        map.on('zoomend', () => {
                            if (map.getZoom() >= zoomThreshold) {
                                // 顯示線條
                                map.addLayer(lineGroup);
                            } else {
                                // 隱藏線條
                                map.removeLayer(lineGroup);
                            }
                        });

                        // 初始化檢查縮放級別並設置初始線條顯示狀態
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