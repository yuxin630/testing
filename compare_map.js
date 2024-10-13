console.log("detail", restaurant_data_detail);

// 使用第一家餐廳的經緯度設置地圖的中心點
const firstRestaurant = restaurant_data_detail[0];
const r_lat = firstRestaurant.r_latitude;
const r_long = firstRestaurant.r_longitude;
// console.log("中心位置在哪裡呢?",firstRestaurant);
// 設置地圖的初始視圖，以第一家餐廳為中心
const map = L.map("map").setView([r_lat, r_long], 13);
// const map = L.map("map").setView(id1Coordinates, 13);

const tiles = L.tileLayer(
  "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png",
  {
    maxZoom: 19,
    attribution:
      '&copy; <a href="https://carto.com/">CartoDB</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  }
).addTo(map);

// // 只顯示頁面上的三家餐廳資訊
// restaurant_data_detail.forEach(function (restaurant) {
//   var marker = L.marker([restaurant.r_latitude, restaurant.r_longitude], {
//     isRestaurant: true, // 設置 isRestaurant 屬性 讓edgeMarker辨別是餐廳
//   }).addTo(map);

//   // 顯示餐廳名稱
//   marker.bindPopup("name: " + restaurant.r_name).openPopup();
// });

const colors = ["#FF70AE", "#85B4FF", "#FFCE47"]; // 定義顏色陣列

restaurant_data_detail.forEach(function (restaurant, index) {
  const colorIndex = index % colors.length; // 確保顏色索引在顏色數組範圍內循環
  const fillColor = colors[colorIndex]; // 為每個標記設置顏色

  // 使用內嵌 SVG 創建自定義圖標
  const markerIcon = L.divIcon({
    className: "map_marker",
    html: `
            <svg width="30" height="40" viewBox="-4 0 36 36" xmlns="http://www.w3.org/2000/svg">
                <g id="Vivid.JS" stroke="none" stroke-width="0" fill="none" fill-rule="evenodd">
                    <g id="Vivid-Icons" transform="translate(-125.000000, -643.000000)">
                        <g id="Icons" transform="translate(37.000000, 169.000000)">
                            <g id="map-marker" transform="translate(78.000000, 468.000000)">
                                <g transform="translate(10.000000, 6.000000)">
                                    <path d="M14,0 C21.732,0 28,5.641 28,12.6 C28,23.963 14,36 14,36 C14,36 0,24.064 0,12.6 C0,5.641 6.268,0 14,0 Z" id="Shape" fill="${fillColor}"></path>
                                    <circle id="Oval" fill="#ffffff" fill-rule="nonzero" cx="14" cy="14" r="7"></circle>
                                </g>
                            </g>
                        </g>
                    </g>
                </g>
            </svg>
        `,
    iconSize: [30, 40], // 將SVG的寬度和高度設定為圖標的大小
    iconAnchor: [15, 40], // 錨點為圖標的底部中心
  });

  var marker = L.marker([restaurant.r_latitude, restaurant.r_longitude], {
    icon: markerIcon, // 使用自定義圖標
    isRestaurant: true, // 設置 isRestaurant 屬性 讓edgeMarker辨別是餐廳
  }).addTo(map);

  // 使用 HTML 結構組合彈出窗口內容
  const popupContent = `
        <div class="popup-content">
            <h3 class="restaurant-name" style='padding: 10px 10px 0px 0px;'>${restaurant.r_name}</h3>
            <p class="restaurant-address">地址：${restaurant.r_address}</p>
            <p class="restaurant-phone">電話：+${restaurant.r_phone}</p>
        </div>
    `;

  // 顯示彈出窗口內容
  marker.bindPopup(popupContent);
});

//加上捷運輕軌
fetch("../connect_sql/get_data_map_json.php")
  .then((response) => response.json())
  .then((data) => {
    var LRT_points = [];
    var MRT_O_points = [];
    var MRT_R_points = [];
    var MRT = L.icon({
      iconUrl: "mrt.png",
      iconSize: [25, 25], // size of the icon
      // iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
      // shadowAnchor: [4, 62],  // the same for the shadow
      // popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
    });
    // console.log(data);
    data.forEach(function (transportation) {
      if (transportation.id.includes("C")) {
        LRT_points.push([transportation.latitude, transportation.longitude]);
        L.polyline(LRT_points, {
          color: "green",
          weight: 2,
        }).addTo(map);
        var circle = L.circle(
          [transportation.latitude, transportation.longitude],
          {
            color: "green",
            fillColor: "green",
            fillOpacity: 0.9,
            radius: 35,
          }
        ).addTo(map);
      } else if (transportation.id.includes("O")) {
        MRT_O_points.push([transportation.latitude, transportation.longitude]);
        L.polyline(MRT_O_points, {
          color: "orange",
          weight: 2,
        }).addTo(map);
        // L.marker([transportation.latitude, transportation.longitude], {icon: MRT}).addTo(map);
        var circle = L.circle(
          [transportation.latitude, transportation.longitude],
          {
            color: "orange",
            fillColor: "orange",
            fillOpacity: 0.9,
            radius: 35,
          }
        ).addTo(map);
      } else {
        MRT_R_points.push([transportation.latitude, transportation.longitude]);
        L.polyline(MRT_R_points, {
          color: "red",
          weight: 2,
        }).addTo(map);

        // L.marker([transportation.latitude, transportation.longitude], {icon: MRT}).addTo(map)
        var circle = L.circle(
          [transportation.latitude, transportation.longitude],
          {
            color: "red",
            fillColor: "#f03",
            fillOpacity: 0.9,
            radius: 35,
          }
        ).addTo(map);
      }

      circle.bindPopup(transportation.name).openPopup();
    });
  })
  .catch((error) => console.error("Error loading restaurant data:", error));

// add the EdgeMarker to the map. 箭頭
var edgeMarkerLayer = L.edgeMarker({
  icon: L.icon({
    // style markers
    iconUrl: "../map/edge_arrow_marker.png",
    clickable: true,
    iconSize: [25, 25],
    iconAnchor: [10, 10],
  }),
  rotateIcons: true, // rotate EdgeMarkers depending on their relative position
  layerGroup: null, // you can specify a certain L.layerGroup to create the edge markers from.
}).addTo(map);

// if you want to remove the edge markers
// edgeMarkerLayer.destroy();

// 餐廳和捷運輕軌連線
fetch("../connect_sql/get_data_json.php")
  .then((response) => response.json())
  .then((restaurants) => {
    fetch("../connect_sql/get_data_map_json.php")
      .then((response) => response.json())
      .then((transportations) => {
        // 建立捷運輕軌站點名稱與位置的對應字典
        const stationMap = {};
        transportations.forEach((station) => {
          // 保留輕軌站名稱中的"站"
          stationMap[station.name] = {
            lat: station.latitude,
            lng: station.longitude,
          };
        });

        // 用於保存所有線條的圖層群組
        const lineGroup = L.layerGroup().addTo(map);

        // 繪製餐廳到捷運輕軌站的最短連線
        restaurants.forEach((restaurant) => {
          // 餐廳位置
          const restaurantLatLng = [
            restaurant.r_latitude,
            restaurant.r_longitude,
          ];

          let shortestDistance = Infinity;
          let shortestLine = null;

          // console.log(`Processing restaurant: ${restaurant.r_name}`);

          // 處理捷運站
          if (restaurant.r_MRT) {
            const mrtStationName = restaurant.r_MRT.replace("站", "");
            const mrtStation = stationMap[mrtStationName];
            if (mrtStation) {
              const mrtDistance = parseFloat(restaurant.r_MRT_dist_km);
              // console.log(`MRT Station: ${mrtStationName}, Distance: ${mrtDistance} km`);
              if (mrtDistance < shortestDistance) {
                shortestDistance = mrtDistance;
                shortestLine = L.polyline(
                  [restaurantLatLng, [mrtStation.lat, mrtStation.lng]],
                  {
                    color: "blue",
                  }
                );
              }
            }
          }

          // 處理輕軌站
          if (restaurant.r_LRT) {
            const lrtStationName = restaurant.r_LRT;
            const lrtStation = stationMap[lrtStationName];
            if (lrtStation) {
              const lrtDistance = parseFloat(restaurant.r_LRT_dist_km);
              // console.log(`LRT Station: ${lrtStationName}, Distance: ${lrtDistance} km`);
              if (lrtDistance < shortestDistance) {
                shortestDistance = lrtDistance;
                shortestLine = L.polyline(
                  [restaurantLatLng, [lrtStation.lat, lrtStation.lng]],
                  {
                    color: "green",
                  }
                );
              }
            }
          }

          // 將最短的線添加到圖層群組中
          if (shortestLine) {
            // console.log(`Shortest Line for ${restaurant.r_name}: ${shortestDistance} km`);
            lineGroup.addLayer(shortestLine);
          }
        });

        // 設置當前的放大級別閾值
        const zoomThreshold = 15;

        // 當地圖縮放級別改變時觸發事件
        map.on("zoomend", () => {
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
      .catch((error) => console.error("Error loading MRT/LRT data:", error));
  })
  .catch((error) => console.error("Error loading restaurant data:", error));
