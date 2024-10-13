// 價格區間歸類
function categorizePrice(price) {
    if (price < 200) return "區間0";
    if (price < 400) return "區間200";
    if (price < 600) return "區間400";
    if (price < 800) return "區間600";
    if (price < 1000) return "區間800";
    return "區間1000";
}

// 依照餐廳數計算區間寬度
function getCurrentIntervalTotals(intervals, circleRadius, data, width, margin, intervalWidths) {
    const intervalTotals = {};
    const minWidth = circleRadius * 2; // 最小寬度為一個圓圈

    intervals.forEach(interval => {
        intervalTotals[interval] = d3.sum(data, d => categorizePrice(d.r_price_low) === interval);
        console.log(`Interval: ${interval}, Total: ${intervalTotals[interval]}`);  // 调试信息
    });

    const total = d3.sum(Object.values(intervalTotals));
    const availableWidth = width - margin.left - margin.right;

    // 計算寬度
    intervals.forEach(interval => {
        let calculatedWidth = (intervalTotals[interval] / total) * availableWidth;
        intervalWidths[interval] = Math.max(minWidth, calculatedWidth);
    });

    return intervalTotals;
}


function calculateTotalHeight(data, intervalWidths, rowHeight, groupSpacing, margin, sortedTimes, currentIntervals, circleRadius, rectSpacing) {
    let totalHeight = margin.top;

    sortedTimes.forEach(time => {
        const groupData = currentIntervals.map(interval => ({
            key: interval,
            value: data.filter(d => d.r_time_low == time && categorizePrice(d.r_price_low) === interval).length
        }));

        // 檢查該時間段是否有任何餐廳
        const hasData = groupData.some(d => d.value > 0);
        if (!hasData) {
            // 如果沒有任何餐廳，跳過這個時間段
            return;
        }

        const maxRows = Math.max(...groupData.map(d => {
            const value = d.value || 0;
            const width = intervalWidths[d.key] || 0;
            if (width === 0) return 0;
            const maxItemsPerRow = Math.max(1, Math.floor(width / (circleRadius * 2 + rectSpacing)));
            return Math.ceil(value / maxItemsPerRow);
        }));

        const groupHeight = maxRows > 0 ? maxRows * (circleRadius * 2 + rectSpacing) : 10;  // 如果没有数据，默认高度为10
        console.log(`Group height for time ${time}:`, groupHeight);  // 查看组高度

        if (isNaN(groupHeight) || groupHeight < 0) {
            console.error("Invalid groupHeight:", groupHeight);
            return;
        }

        totalHeight += groupHeight + groupSpacing + 40; // 增加间距
    });

    totalHeight += margin.bottom;

    if (isNaN(totalHeight) || totalHeight <= 0 || !isFinite(totalHeight)) {
        console.error("Invalid total height calculated:", totalHeight);
        return 0;
    }

    return totalHeight;
}


function calculateSortedPositions(data, intervalWidths, rowHeight, rectSpacing, margin, intervalPositions, circleRadius, sortedTimes, currentIntervals, groupSpacing) {
    const positions = [];
    let groupYOffset = margin.top;

    // 确保每个间隔的最小宽度能放置一个圆圈
    Object.keys(intervalWidths).forEach(key => {
        if (intervalWidths[key] < circleRadius * 2) {
            intervalWidths[key] = circleRadius * 2 + rectSpacing;
        }
    });

    sortedTimes.forEach(time => {
        // 按 `r_rating` 对数据排序，并过滤属于当前区间的餐厅
        const groupData = currentIntervals.map(interval => ({
            key: interval,
            items: data
                .filter(d => d.r_time_low == time && (categorizePrice(d.r_price_low) === interval || !categorizePrice(d.r_price_low)))
                .sort((a, b) => b.r_rating - a.r_rating)

        }));

        // 检查该时间段是否有任何餐厅
        const hasData = groupData.some(group => group.items.length > 0);
        if (!hasData) {
            return;
        }

        const maxRows = Math.max(...groupData.map(group => {
            const value = group.items.length;
            const width = intervalWidths[group.key] || 0;
            const maxItemsPerRow = width > 0 ? Math.max(1, Math.floor(width / (circleRadius * 2 + rectSpacing))) : 1;
            return Math.ceil(value / maxItemsPerRow);
        }));

        const groupHeight = maxRows > 0 ? maxRows * (circleRadius * 2 + rectSpacing) : 10;

        // 如果需要在区间中显示标签（如"用餐时间"），可以将其添加到positions数组中
        positions.push({
            x: margin.left,
            y: groupYOffset,
            width: Math.max(...Object.values(intervalWidths)) - margin.left - margin.right,
            height: groupHeight + 40, // 调整高度以避免重叠
            key: '',
            time: time,
            border: true,
            label: true,
            r_time_low: time
        });

        const placedPositions = {};

        groupData.forEach(group => {
            let xOffset = 0;
            let yOffset = groupYOffset;
            const maxItemsPerRow = Math.floor(intervalWidths[group.key] / (circleRadius * 2 + rectSpacing));

            group.items.forEach((restaurant, i) => {
                if (i > 0 && i % maxItemsPerRow === 0) {
                    xOffset = 0;
                    yOffset += (circleRadius * 2 + rectSpacing);
                }

                let currentX = intervalPositions[group.key] + xOffset;
                let posKey = `${currentX}-${yOffset}`;

                while (placedPositions[posKey]) {
                    currentX += circleRadius * 2;
                    posKey = `${currentX}-${yOffset}`;
                }

                if (currentX + circleRadius * 2 > intervalPositions[group.key] + intervalWidths[group.key]) {
                    xOffset = 0;
                    yOffset += (circleRadius * 2 + rectSpacing);
                    currentX = intervalPositions[group.key] + xOffset;
                }

                positions.push({
                    x: currentX,
                    y: yOffset,
                    key: group.key,
                    time: time,
                    label: false,
                    r_name: restaurant.r_name,
                    r_photo_env1: restaurant.r_photo_env1,
                    r_photo_env2: restaurant.r_photo_env2,  // 添加第二张照片的URL
                    r_photo_env3: restaurant.r_photo_env3,  // 添加第三张照片的URL
                    r_rating: restaurant.r_rating,
                    r_has_parking: restaurant.r_has_parking,
                    r_food_dishes: restaurant.r_food_dishes,  // 添加菜餚信息
                    r_rate_clean: restaurant.r_rate_clean,    // 添加清潔度评分
                    r_rate_atmosphere: restaurant.r_rate_atmosphere,  // 添加氛圍评分
                    r_rate_service: restaurant.r_rate_service,  // 添加服务评分
                    r_rating_food: restaurant.r_rating_food,// 添加食物评分
                    r_days: restaurant.r_hours_weekday,
                    r_atmosphere: restaurant.r_vibe,
                    r_id: restaurant.r_id
                });


                placedPositions[posKey] = true;
                xOffset += circleRadius * 2 + rectSpacing;
            });
        });

        groupYOffset += groupHeight + groupSpacing + 40; // 调整垂直间距以避免重叠
    });

    return positions;
}


function findMaxIntervalWithNeighbors() {
    let maxInterval = intervals[0];
    let maxTotal = 0;

    // 找出数据最多的区间
    intervals.forEach(interval => {
        const intervalTotal = d3.sum(data, d => categorizePrice(d.r_price_low) === interval);
        if (intervalTotal > maxTotal) {
            maxTotal = intervalTotal;
            maxInterval = interval;
        }
    });

    // 如果最大区间的总数仍然为 0，表示所有区间没有数据
    if (maxTotal === 0) {
        return null;
    }

    // 找到 maxInterval 的索引并包含相邻的区间
    const maxIntervalIndex = intervals.indexOf(maxInterval);
    let startIndex = Math.max(0, maxIntervalIndex - 1);
    let endIndex = Math.min(intervals.length - 1, maxIntervalIndex + 1);

    // 如果 maxInterval 是最左边的区间，显示它和它右边的两个区间
    if (maxIntervalIndex === 0) {
        endIndex = Math.min(intervals.length - 1, maxIntervalIndex + 2);
    }
    // 如果 maxInterval 是最右边的区间，显示它和它左边的两个区间
    else if (maxIntervalIndex === intervals.length - 1) {
        startIndex = Math.max(0, maxIntervalIndex - 2);
    }

    return intervals.slice(startIndex, endIndex + 1);
}

function calculatePositions(data, intervalWidths, rowHeight, rectSpacing, margin, intervalPositions, circleRadius, sortedTimes, currentIntervals, groupSpacing) {
    const positions = [];
    let groupYOffset = margin.top;

    // 確保每個間隔的最小寬度能放置一個圓圈
    Object.keys(intervalWidths).forEach(key => {
        if (intervalWidths[key] < circleRadius * 2) {
            intervalWidths[key] = circleRadius * 2 + rectSpacing;
        }
    });

    sortedTimes.forEach(time => {
        const groupData = currentIntervals.map(interval => ({
            key: interval,
            value: data.filter(d => d.r_time_low == time && categorizePrice(d.r_price_low) === interval).length
        }));

        // 檢查該時間段是否有任何餐廳
        const hasData = groupData.some(d => d.value > 0);
        if (!hasData) {
            // 如果沒有任何餐廳，跳過這個時間段
            return;
        }

        const maxRows = Math.max(...groupData.map(d => {
            const value = d.value || 0;
            const width = intervalWidths[d.key] || 0;
            const maxItemsPerRow = width > 0 ? Math.max(1, Math.floor(width / (circleRadius * 2 + rectSpacing))) : 1;
            return Math.ceil(value / maxItemsPerRow);
        }));

        const groupHeight = maxRows > 0 ? maxRows * (circleRadius * 2 + rectSpacing) : 10;  // 如果沒有數據，默認高度為10
        positions.push({
            x: margin.left,
            y: groupYOffset,
            width: Math.max(...Object.values(intervalWidths)) - margin.left - margin.right,
            height: groupHeight + 40, // 增加高度
            key: '',
            time: time,
            border: true,
            label: true, // 設置 label 屬性
            r_time_low: time // 包含需要顯示的用餐時間
        });

        const placedPositions = {};

        groupData.forEach(group => {
            let xOffset = 0;
            let yOffset = groupYOffset;
            const maxItemsPerRow = Math.floor(intervalWidths[group.key] / (circleRadius * 2 + rectSpacing));

            // 过滤出属于当前group.key和time的餐厅
            const restaurantsInGroup = data.filter(d => d.r_time_low == time && categorizePrice(d.r_price_low) === group.key);

            restaurantsInGroup.forEach((restaurant, i) => {
                if (i > 0 && i % maxItemsPerRow === 0) {
                    xOffset = 0;
                    yOffset += (circleRadius * 2 + rectSpacing);
                }

                let currentX = intervalPositions[group.key] + xOffset;
                let posKey = `${currentX}-${yOffset}`;

                // 检查是否有相同的 X 位置，若有则加上 radius 的宽度
                while (placedPositions[posKey]) {
                    currentX += circleRadius * 2;
                    posKey = `${currentX}-${yOffset}`;
                }

                if (currentX + circleRadius * 2 > intervalPositions[group.key] + intervalWidths[group.key]) {
                    xOffset = 0;
                    yOffset += (circleRadius * 2 + rectSpacing);
                    currentX = intervalPositions[group.key] + xOffset;
                }

                positions.push({
                    x: currentX,
                    y: yOffset,
                    key: group.key,
                    time: time,
                    label: false,
                    r_name: restaurant.r_name,
                    r_photo_env1: restaurant.r_photo_env1,
                    r_photo_env2: restaurant.r_photo_env2,  // 添加第二张照片的URL
                    r_photo_env3: restaurant.r_photo_env3,  // 添加第三张照片的URL
                    r_rating: restaurant.r_rating,
                    r_has_parking: restaurant.r_has_parking,
                    r_food_dishes: restaurant.r_food_dishes,  // 添加菜餚信息
                    r_rate_clean: restaurant.r_rate_clean,    // 添加清潔度评分
                    r_rate_atmosphere: restaurant.r_rate_atmosphere,  // 添加氛圍评分
                    r_rate_service: restaurant.r_rate_service,  // 添加服务评分
                    r_rating_food: restaurant.r_rating_food, // 添加食物评分
                    r_days: restaurant.r_hours_weekday,
                    r_atmosphere: restaurant.r_vibe,
                    r_id: restaurant.r_id
                });


                placedPositions[posKey] = true;
                xOffset += circleRadius * 2 + rectSpacing;
            });
        });

        groupYOffset += groupHeight + groupSpacing + 40; // 增加垂直间距

    });

    return positions;
}









