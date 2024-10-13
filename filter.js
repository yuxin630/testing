let restaurantIds = []; 
let usedRestaurantIds = [];

// price filter functions
const minVal = document.querySelector(".min-val");
const maxVal = document.querySelector(".max-val");
const priceInputMin = document.querySelector(".min-input");
const priceInputMax = document.querySelector(".max-input");
const minTooltip = document.querySelector(".min-tooltip");
const maxTooltip = document.querySelector(".max-tooltip");
const minGap = 100;
const range = document.querySelector(".slider-track");
const sliderMinValue = parseInt(minVal.min, 10);
const sliderMaxValue = parseInt(maxVal.max, 10);

document.addEventListener("DOMContentLoaded", function() {
    minVal.value = 50; // 初始值
    maxVal.value = 2000; // 初始值
    priceInputMin.value = minVal.value; // 設置 min_input 初始值
    priceInputMax.value = maxVal.value; // 設置 max_input 初始值
    slideMin();
    slideMax();
});

// 更新滑块和输入框的值
function slideMin() {
    let gap = parseInt(maxVal.value, 10) - parseInt(minVal.value, 10);
    if (gap <= minGap) {
        minVal.value = parseInt(maxVal.value, 10) - minGap;
    }
    minTooltip.innerHTML = "$" + minVal.value;
    priceInputMin.value = minVal.value;
    setArea();
}


function slideMax() {
    let gap = parseInt(maxVal.value, 10) - parseInt(minVal.value, 10);
    if (gap <= minGap) {
        maxVal.value = parseInt(minVal.value, 10) + minGap;
    }
    maxTooltip.innerHTML = "$" + maxVal.value;
    priceInputMax.value = maxVal.value;
    setArea();
}

// 更新价格范围和工具提示
function setArea() {
    // 取得滑桿的最小值和最大值的百分比位置
    const minPricePercent = (minVal.value - minVal.min) / (minVal.max - minVal.min) * 100;
    const maxPricePercent = (maxVal.value - minVal.min) / (maxVal.max - minVal.min) * 100;
    
    // 更新範圍條的寬度和位置
    range.style.left = minPricePercent + "%";
    range.style.width = (maxPricePercent - minPricePercent) + "%";
    
    // 更新工具提示的位置
    minTooltip.style.left = minPricePercent + "%";
    maxTooltip.style.left = maxPricePercent + "%";
}


function setMinInput() {
    let minPrice = parseInt(priceInputMin.value, 10);
    if (minPrice < sliderMinValue) {
        priceInputMin.value = sliderMinValue;
    }
    minVal.value = minPrice;
    slideMin();
}

function setMaxInput() {
    let maxPrice = parseInt(priceInputMax.value, 10);
    if (maxPrice > sliderMaxValue) {
        priceInputMax.value = sliderMaxValue;
        // maxPrice = sliderMaxValue;
    }
    maxVal.value = maxPrice;
    slideMax();
}


// Time filter functions
const minTimeVal = document.querySelector(".min-time");
const maxTimeVal = document.querySelector(".max-time");
const timeInputMin = document.querySelector(".min-time-input");
const timeInputMax = document.querySelector(".max-time-input");
const minTimeTooltip = document.querySelector(".min-time-tooltip");
const maxTimeTooltip = document.querySelector(".max-time-tooltip");
const timeMinGap = 10;
const timeRange = document.querySelectorAll(".slider-track")[1]; // second slider track
const timeSliderMinValue = parseInt(minTimeVal.min);
const timeSliderMaxValue = parseInt(maxTimeVal.max);

document.addEventListener("DOMContentLoaded", function() {
    minTimeVal.value = 60; // 確保初始化時設置值
    maxTimeVal.value = 180; // 確保初始化時設置值
    slideTimeMin();
    slideTimeMax();
});

function slideTimeMin() {
    let gap = parseInt(maxTimeVal.value) - parseInt(minTimeVal.value);
    if (gap <= timeMinGap) {
        minTimeVal.value = parseInt(maxTimeVal.value) - timeMinGap;
    }
    minTimeTooltip.innerHTML = minTimeVal.value + "時";
    timeInputMin.value = minTimeVal.value;
    setTimeArea();
}

function slideTimeMax() {
    let gap = parseInt(maxTimeVal.value) - parseInt(minTimeVal.value);
    if (gap <= timeMinGap) {
        maxTimeVal.value = parseInt(minTimeVal.value) + timeMinGap;
    }
    maxTimeTooltip.innerHTML = maxTimeVal.value + "時";
    timeInputMax.value = maxTimeVal.value;
    setTimeArea();
}

function setTimeArea() {
    // 取得滑桿的最小值和最大值的百分比位置
    const minPercent = (minTimeVal.value - minTimeVal.min) / (minTimeVal.max - minTimeVal.min) * 100;
    const maxPercent = (maxTimeVal.value - minTimeVal.min) / (maxTimeVal.max - minTimeVal.min) * 100;
    
    // 更新範圍條的寬度和位置
    timeRange.style.left = minPercent + "%";
    timeRange.style.width = (maxPercent - minPercent) + "%";
    
    // 更新工具提示的位置
    minTimeTooltip.style.left = minPercent + "%";
    maxTimeTooltip.style.left = maxPercent + "%";
}


function setMinTimeInput() {
    let minTime = parseInt(timeInputMin.value);
    if (minTime < timeSliderMinValue) {
        timeInputMin.value = timeSliderMinValue;
    }
    minTimeVal.value = timeInputMin.value;
    slideTimeMin();
}

function setMaxTimeInput() {
    let maxTime = parseInt(timeInputMax.value);
    if (maxTime > timeSliderMaxValue) {
        timeInputMax.value = timeSliderMaxValue;
    }
    maxTimeVal.value = timeInputMax.value;
    slideTimeMax();
}


// Function to add star SVGs based on rating
function addStars(container, rating) {
    const fullStarSVG = `
        <svg height=".9rem" width=".9rem" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 47.94 47.94" xml:space="preserve">
            <path style="fill:#ED8A19;" d="M26.285,2.486l5.407,10.956c0.376,0.762,1.103,1.29,1.944,1.412l12.091,1.757
            c2.118,0.308,2.963,2.91,1.431,4.403l-8.749,8.528c-0.608,0.593-0.886,1.448-0.742,2.285l2.065,12.042
            c0.362,2.109-1.852,3.717-3.746,2.722l-10.814-5.685c-0.752-0.395-1.651-0.395-2.403,0l-10.814,5.685
            c-1.894,0.996-4.108-0.613-3.746-2.722l2.065-12.042c0.144-0.837-0.134-1.692-0.742-2.285l-8.749-8.528
            c-1.532-1.494-0.687-4.096,1.431-4.403l12.091-1.757c0.841-0.122,1.568-0.65,1.944-1.412l5.407-10.956
            C22.602,0.567,25.338,0.567,26.285,2.486z"/>
        </svg>
    `;

    const halfStarSVG = `
        <svg height=".9rem" width=".9rem" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 47.94 47.94" xml:space="preserve">
            <defs>
                <clipPath id="half-star">
                    <rect x="0" y="0" width="50%" height="100%"></rect> <!-- 只顯示左半邊 -->
                </clipPath>
            </defs>
            <path style="fill:#ED8A19;" d="M26.285,2.486l5.407,10.956c0.376,0.762,1.103,1.29,1.944,1.412l12.091,1.757
            c2.118,0.308,2.963,2.91,1.431,4.403l-8.749,8.528c-0.608,0.593-0.886,1.448-0.742,2.285l2.065,12.042
            c0.362,2.109-1.852,3.717-3.746,2.722l-10.814-5.685c-0.752-0.395-1.651-0.395-2.403,0l-10.814,5.685
            c-1.894,0.996-4.108-0.613-3.746-2.722l2.065-12.042c0.144-0.837-0.134-1.692-0.742-2.285l-8.749-8.528
            c-1.532-1.494-0.687-4.096,1.431-4.403l12.091-1.757c0.841-0.122,1.568-0.65,1.944-1.412l5.407-10.956
            C22.602,0.567,25.338,0.567,26.285,2.486z" clip-path="url(#half-star)"/>
        </svg>
    `;

    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;

    let starsHTML = '';

    // Add full stars
    for (let i = 0; i < fullStars; i++) {
        starsHTML += fullStarSVG;
    }

    // Add half star if needed
    if (hasHalfStar) {
        starsHTML += halfStarSVG;
    }

    // Insert stars before the rating number
    container.innerHTML = starsHTML + container.innerHTML;
}

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search');
    const clearSearchIcon = document.querySelector('.clear-search');
    const noIdMessage = document.getElementById('message');
    const allButton = document.querySelector('.all-btn');
    const backButton = document.querySelector('.back-btn');
    const maxSelection = 5;
    const colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5'];
    const vibeButtons = document.querySelectorAll('.vibe-button');
    const parkingCheckbox = document.getElementById('parking-checkbox');
    const minTimeSlider = document.querySelector('.min-time');
    const maxTimeSlider = document.querySelector('.max-time');
    const minTimeInput = document.querySelector('.min-time-input');
    const maxTimeInput = document.querySelector('.max-time-input');
    const filterItemPrice = document.querySelector('.filter-price');
    const filterItemTime = document.querySelector('.filter-time');
    let selectedButtons = [];
    let availableColors = [...colors];
    let isNoLimitActive = false;
    const noLimitBtn = document.getElementById('no-limit-btn');
    noLimitBtn.classList.add('active');  // 頁面加載時添加 active 類
    isNoLimitActive = true;  // 同步狀態變量
    const checkboxes = document.querySelectorAll('.opening-hours input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            applyFilters();
        });
    });
    const dayLabels = document.querySelectorAll('.day-label');
    const timeButtons = document.querySelectorAll('.time-button');
    let selectedTimes = [];  // 存储被选中的时间

    const starItems = document.querySelectorAll('.l-item .item-text');
    starItems.forEach(item => {
        const rating = parseFloat(item.textContent);  // 取得數字評分
        addStars(item, rating);  // 添加對應的星星
    });

    dayLabels.forEach(label => {
        label.addEventListener('click', function() {
            label.classList.toggle('active');  // 切換選中狀態
            applyFilters();  // 調用篩選函數
        });
    });

    timeButtons.forEach(item => {
        item.addEventListener('click', function() {
            label.classList.toggle('active');  // 切換選中狀態
            applyFilters();  // 調用篩選函數
        });
    });

    // 頁面加載時預設勾選所有 checkbox
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;  // 設置 checkbox 為已選擇狀態
    });
    function resetFiltersToDefault() {
        // 重置价格过滤器到默认状态
        minVal.value = minVal.min;
        maxVal.value = maxVal.max;
        priceInputMin.value = minVal.min;
        priceInputMax.value = maxVal.max;
        slideMin();
        slideMax();
    

        selectedTimes = [];  // 清空数组，确保重新选择
        timeButtons.forEach(button => {
            const timeValue = button.innerText.trim();
            selectedTimes.push(timeValue);  // 将每个按钮的值添加到 selectedTimes
            button.classList.add('selected');  // 设置为选中状态
            button.classList.add('active'); 
        });
    
        // 取消氣氛
        selectedButtons.forEach(button => {
            const colorClass = colors.find(color => button.classList.contains(color));
            if (colorClass) {
                availableColors.push(colorClass);
            }
            button.classList.remove('selected');
    
            // 重置顏色
            colors.forEach(color => button.classList.remove(color));
        });
    
        // 清空已選按鈕並重置 availableColors
        selectedButtons = [];
        availableColors = [...colors];

        // 取消星星的勾选
        items.forEach(item => {
            item.classList.remove('checked');
        });
        const btnText = document.querySelector('.btn-text');
        btnText.innerText = "選擇評分";  // 重置按钮文本

        // 取消停车场的勾选
        parkingCheckbox.checked = false;
    
        // 其他过滤器的重置逻辑（如星级、星期等）
        checkboxes.forEach(checkbox => checkbox.checked = false);
        dayLabels.forEach(label => label.classList.remove('active'));
    
        // 应用过滤器（显示所有餐厅）
        restaurantIds = [];
        applyFilters(true);
    }
    
    
    function fetchAllRestaurantIds() {
        // 不传递任何筛选条件，获取所有餐厅
        // 檢查 fetch 回傳的資料
        fetch('./data.php?')
            .then(response => response.json())
            .then(data => {
                console.log("Fetched data:", data);  // 輸出資料，檢查是否正確
                if (data && data.length > 0) {
                    restaurantIds = data.map(item => item.r_id);
                    console.log("Restaurant IDs:", restaurantIds);  // 檢查 restaurantIds 是否正確
                    applyFilters();
                } else {
                    console.error("No restaurant data found.");
                }
            })
            .catch(error => console.error('Error fetching data:', error));
    }
    function fetchData() {
        let query = document.getElementById('search').value.trim();
        if (query === "") {
            alert("請輸入查詢字串");
            return;
        }
        
        // 清除之前的错误信息
        d3.select("#results").html(""); 

        document.getElementById('loading').style.display = 'block';
    
        fetch(`search.php?query=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('網絡回應不正確');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                
                // 2. 清空之前存储的 r_id
                restaurantIds = [];
    
                if (data && (data.error || data.length === 0)) {
                    // 只在有错误或无数据时显示错误信息
                    console.error("Error:", data.error || "No data found");
                    noIdMessage.classList.remove('fade-out');
                    noIdMessage.style.display = 'block';
                }else {
                    // 处理成功返回的数据
                    data.forEach(d => {
                        // 将 r_id 存储到数组中
                        restaurantIds.push(d.r_id);
                    });

                    // 确保 iframe 显示出来
                    const iframe = document.getElementById('scale-iframe');
                    if (iframe) {
                        iframe.style.display = 'block'; // 显示 iframe
                    }
                    // 在控制台中打印 r_id 数组
                    console.log("Found restaurant IDs:", restaurantIds);
                    // 调用 applyFilters 使用最新的 restaurantIds 进行过滤
                    applyFilters(true);
                }
            })
            .catch(error => {
                document.getElementById('loading').style.display = 'none';
                console.error('獲取數據錯誤:', error);
                // d3.select("#results").html("無法取得數據，請稍後再試。");
                noIdMessage.classList.remove('fade-out');
                noIdMessage.style.display = 'block';
            });
    }
    // 更新篩選條件
    function applyFilters() {
        console.log("applyFilters function is defined");
        const minTime = minTimeSlider.value;
        const maxTime = maxTimeSlider.value;
        const hasParking = parkingCheckbox.checked ? 1 : 0;
        const selectedRatings = Array.from(document.querySelectorAll('.filter-star .l-item.checked .item-text'))
        .map(el => el.textContent.match(/[\d\.]+/)[0]);  // 仅提取数字部分
        const selectedVibes = Array.from(vibeButtons)
            .filter(button => button.classList.contains('selected'))
            .map(button => button.innerText.trim());
        const minPrice = parseInt(minVal.value, 10);
        const maxPrice = parseInt(maxVal.value, 10);
        var button = document.getElementById("no-limit-btn");
        button.classList.add("selected");
        const selectedDays = Array.from(document.querySelectorAll('.day-label.active'))
            .map(label => label.getAttribute('data-day'));
                // 获取选中的时间限制，作为查询参数
        const selectedTimesQuery = selectedTimes.length > 0 
            ? selectedTimes.map(time => encodeURIComponent(time)).join(',')
            : '';
        usedRestaurantIds = [];
        // 基于 restaurantIds 进行初步筛选
        // 如果 restaurantIds 为空，则获取所有餐厅
        if (restaurantIds.length === 0) {
            console.log("restaurantIds is empty, fetching all restaurants...");
            fetchAllRestaurantIds(); // 获取所有餐厅的 ID
            return;  // 暂停当前的过滤逻辑，等待餐厅数据获取完成后再调用 applyFilters
        }

        let filteredIds = restaurantIds; // 使用 restaurantIds 进行过滤

        let url = './data.php?';

        // 如果有选中的时间限制，加入 URL 参数
        if (selectedTimesQuery) {
            url += `times=${selectedTimesQuery}&`;
        }
    
        if (selectedVibes.length > 0) {
            const vibeQuery = selectedVibes.map(vibe => encodeURIComponent(vibe)).join(',');
            url += `vibes=${vibeQuery}&`; 
        }

        if (selectedRatings.length > 0) {
            const ratingQuery = selectedRatings.map(rating => encodeURIComponent(rating)).join(',');
            url += `ratings=${ratingQuery}&`;
            console.log('Selected Ratings:', selectedRatings); // 打印選中的 ratings
            console.log('Ratings Query:', ratingQuery); // 打印 ratings 查詢字符串
        }

        if (hasParking) {
            url += `hasParking=${hasParking}&`;
        }

        if (minTime && maxTime) {
            url += `min_time=${minTime}&max_time=${maxTime}&`;
        }

        if (minPrice && maxPrice) {
            url += `min_price=${minPrice}&max_price=${maxPrice}&`;
        }    

        if (isNoLimitActive) {
            url += 'no_limit=1&';
        }

        // 篩選資料中符合選中日期的項目
        if (selectedDays.length > 0) {
            url += `selectedDays=${selectedDays.join(',')}&`;
        }

        // 添加 restaurantIds 作为筛选条件
        if (filteredIds.length > 0) {
            const idsQuery = filteredIds.join(',');
            url += `r_ids=${idsQuery}&`;
        }

        url = url.endsWith('&') ? url.slice(0, -1) : url;

        console.log('Request URL:', url); // 輸出請求 URL 以便檢查

        fetch(url)
            .then(response => response.json())
            .then(data => {
                // 清空顯示區域
                svg.selectAll("*").remove();

                // 更新顯示的數據
                svg.selectAll("text")
                    .data(data)
                    .enter()
                    .append("text")
                    .attr("x", () => 50)
                    .attr("y", (d, i) => 30 + i * 20)
                    .text(d => d.r_name);

                // 將每個返回的 r_id 添加到 usedRestaurantIds 數組中
                usedRestaurantIds = data.map(item => item.r_id);

                console.log('Used Restaurant IDs:', usedRestaurantIds); // 打印已使用的 r_id
                // 發送 usedRestaurantIds 到 scale.html 的 iframe
                const iframe = document.getElementById('scale-iframe');
                if (iframe && iframe.contentWindow) {
                    console.log("yy", usedRestaurantIds)
                    iframe.contentWindow.postMessage(usedRestaurantIds, '*');
                }
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
           
    }

    document.querySelector('.reset-btn').addEventListener('click', function() {
        const resetButton = this;
        // 重置过滤器到默认状态
        resetFiltersToDefault();

        // 显示所有餐厅
        fetchAllRestaurantIds();
    });

    // 通过 JavaScript 绑定事件，而不是在 HTML 中使用 onclick
    document.querySelector('.fa-magnifying-glass').addEventListener('click', fetchData);



    // 监听输入框的按键事件
    document.getElementById('search').addEventListener('keypress', function (event) {
        if (event.key === 'Enter') { // 检测是否按下 Enter 键
            fetchData(); // 触发搜索
        }
    });
    // 监听输入框内容变化
    searchInput.addEventListener('input', function() {
        if (searchInput.value.length > 0) {
            clearSearchIcon.style.display = 'block';
        } else {
            clearSearchIcon.style.display = 'none';
        }
    });

    // 点击叉叉图标时清空输入框内容并隐藏叉叉图标
    clearSearchIcon.addEventListener('click', function() {
        searchInput.value = '';
        clearSearchIcon.style.display = 'none';
        noIdMessage.style.display = 'none';
        restaurantIds = [];
        applyFilters(); // 恢复默认状态
    });

    allButton.addEventListener('click', function(){
        searchInput.value = '';
        clearSearchIcon.style.display = 'none';
        noIdMessage.classList.add('fade-out');
        setTimeout(() => {
            noIdMessage.style.display = 'none';
        }, 500);
        // 重置过滤器到默认状态
        resetFiltersToDefault();

        // 显示所有餐厅
        fetchAllRestaurantIds();
        
    });

    backButton.addEventListener('click', function(){
        searchInput.value = '';
        clearSearchIcon.style.display = 'none';
        noIdMessage.style.display = 'none';
        noIdMessage.classList.add('fade-out');
        applyFilters();
        setTimeout(() => {
            noIdMessage.style.display = 'none';
        }, 500);
    });
    
   // 監聽每個按鈕的點擊事件
   vibeButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.classList.contains('selected')) {
                button.classList.remove('selected');
                const index = selectedButtons.indexOf(button);
                selectedButtons.splice(index, 1);
                const colorClass = colors.find(color => button.classList.contains(color));
                button.classList.remove(colorClass);
                availableColors.push(colorClass);
                availableColors.sort((a, b) => colors.indexOf(a) - colors.indexOf(b));
            } else {
                if (selectedButtons.length < maxSelection && availableColors.length > 0) {
                    button.classList.add('selected');
                    selectedButtons.push(button);
                    const colorClass = availableColors.shift();
                    button.classList.add(colorClass);
                    console.log(`Button selected: ${button.innerText.trim()}, Color class added: ${colorClass}`);
                } else if (selectedButtons.length >= maxSelection) {
                    alert(`最多只能選擇 ${maxSelection} 個選項`);
                }
            }

            // 收集所有選中的氛圍名稱和顏色
            const selectedVibesWithColors = selectedButtons.map(button => ({
                vibe: button.innerText.trim(),
                colorClass: colors.find(color => button.classList.contains(color))
            }));

            console.log(`Selected Vibes with Colors:`, selectedVibesWithColors);

            // 將選中的氛圍名稱和顏色傳遞到主頁面
            const iframe = document.getElementById('scale-iframe');
            if (iframe && iframe.contentWindow) {
                // 创建消息对象，并将 selectedVibesWithColors 作为数据发送
                const message = {
                    type: 'vibesWithColors',  // 指定消息类型
                    data: selectedVibesWithColors  // 传递整个对象数组，而不是单一的颜色
                };
                iframe.contentWindow.postMessage(message, '*');
            }

            applyFilters();
        });
    });



    // 監聽每個 checkbox 的變化事件
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            applyFilters(); // 當 checkbox 狀態改變時，重新篩選
        });
    });

     // 監聽勾選框變化
     parkingCheckbox.addEventListener('change', function() {
        applyFilters(true);
    });

    // 監聽滑桿和輸入框變化
    minVal.addEventListener('input', function() {
        filterItemPrice.style.opacity = 1;
        applyFilters();
    });
    maxVal.addEventListener('input', function() {
        filterItemPrice.style.opacity = 1;
        applyFilters();
    });
    priceInputMin.addEventListener('change', function() {
        filterItemPrice.style.opacity = 1;
        applyFilters();
    });
    priceInputMax.addEventListener('change', function() {
        filterItemPrice.style.opacity = 1;
        applyFilters();
    });

    // 为每个 timeButton 添加事件监听器
    timeButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterItemTime.style.opacity = 1;
        });
    });


    function updateTimeSliders() {
        minTimeSlider.value = parseInt(minTimeInput.value, 10);
        maxTimeSlider.value = parseInt(maxTimeInput.value, 10);
    }


    // stars selection
    const selectBtn = document.querySelector(".select-btn");
    const listItems = document.querySelector(".list-items");
    const items = document.querySelectorAll(".l-item");

    selectBtn.addEventListener("click", (event) => {
        selectBtn.classList.toggle("open");
        listItems.classList.toggle("open");
        event.stopPropagation(); // 阻止事件冒泡
    });

    // 处理每个评分项点击事件
    items.forEach((item) => {
        item.addEventListener("click", () => {
            item.classList.toggle("checked");

            let checkedItems = document.querySelectorAll(".l-item.checked .item-text"),
                btnText = document.querySelector(".btn-text");

            if (checkedItems && checkedItems.length > 0) {
                // 获取所有选中的评分，仅显示数字部分
                let selectedText = Array.from(checkedItems).map(itemTextElement => itemTextElement.textContent.match(/[\d\.]+/)[0]).join('、');
                btnText.innerText = selectedText;  // 仅显示数字
            } else {
                btnText.innerText = "選擇評分";  // 没有选择时的默认文本
            }

        // 调用 applyFilters 函数
        applyFilters();
        });
    });

// 初始化并监听每个时间按钮的点击事件
timeButtons.forEach(button => {
    // 初始状态下，如果有按钮带有 active 类，加入 selectedTimes 数组
    if (button.classList.contains('selected')) {
        selectedTimes.push(button.innerText.trim());  // 将初始激活的按钮值加入 selectedTimes
    }

    // 监听每个时间按钮的点击事件
    button.addEventListener('click', () => {
        const timeValue = button.innerText.trim();  // 获取按钮中的时间值

        // 如果已经是 selected 状态，则取消选中
        if (button.classList.contains('selected')) {
            button.classList.remove('selected');  // 移除选中状态
            selectedTimes = selectedTimes.filter(time => time !== timeValue);  // 从数组中移除
        } else {
            button.classList.add('selected');  // 添加选中状态
            selectedTimes.push(timeValue);  // 加入数组
        }

        // 调用 applyFilters 更新筛选
        applyFilters();
    });
});
    
    // 初始化時顯示滑桿和輸入框的值
    updateTimeSliders();
    applyFilters();
});