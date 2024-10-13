function drawMinimap(intervalPositions, totalHeight, intervalWidths, intervals, sortedTimes, data, currentIntervals) {
    const minimapWidth = 200;
    const minimapHeight = 85;

    const scaleX = d3.scaleBand()
        .domain(intervals)
        .range([0, minimapWidth])
        .padding(0.1);

    // 保持 sortedTimes 的正確順序
    const reversedTimes = sortedTimes.slice().reverse();

    const intervalData = intervals.map(interval => {
        const timeData = {};
        reversedTimes.forEach(time => {
            timeData[time] = data.filter(d => d.r_time_low === time && categorizePrice(d.r_price_low) === interval).length;
        });
        return {
            interval: interval,
            ...timeData
        };
    });

    const maxYValue = d3.max(intervalData, d => d3.sum(reversedTimes.map(time => d[time])));

    const scaleY = d3.scaleLinear()
        .domain([maxYValue, 0])
        .range([0, minimapHeight]);

    d3.select("#minimap").selectAll("*").remove();

    d3.select("#minimap")
        .append("rect")
        .attr("width", minimapWidth)
        .attr("height", minimapHeight)
        .attr("fill", "#ddd");

    const stack = d3.stack()
        .keys(reversedTimes);

    const stackedData = stack(intervalData);

    const colors = d3.scaleOrdinal()
        .domain(sortedTimes)  // 確保 domain 包含所有 time 值
        .range(['#d6af99', '#a2cdab', '#7ea1dd', '#fdc85e', '#e67575', '#b295ab']);
        // .range(['#F3BEBE', '#FFD8B6', '#A8D6A8', '#ADD8E6', '#80B1D3', '#B48CC8']); // 使用自定義顏色範圍

    stackedData.reverse().forEach((layer, layerIndex) => {
        d3.select("#minimap").selectAll(`.bar-${layer.key}`)
            .data(layer)
            .enter()
            .append("rect")
            .attr("class", `bar-${layer.key}`)
            .attr("x", d => scaleX(d.data.interval))
            .attr("y", d => scaleY(d[1]))
            .attr("width", scaleX.bandwidth())
            .attr("height", d => scaleY(d[0]) - scaleY(d[1]))
            .attr("fill", colors(layer.key))  // 根據 layer.key 使用自定義顏色
            .attr("stroke", "black");
    });

    // 初始紅框位置
    const currentStart = currentIntervals[0];
    const currentEnd = currentIntervals[currentIntervals.length - 1];
    const xPositionStart = scaleX(currentStart);
    const xPositionEnd = scaleX(currentEnd) + scaleX.bandwidth();

    const redBox = d3.select("#minimap").append("rect")
        .attr("x", xPositionStart)
        .attr("y", 0)
        .attr("width", xPositionEnd - xPositionStart)
        .attr("height", minimapHeight)
        .attr("stroke", "red")
        .attr("stroke-width", 2)
        .attr("fill", "none");

    intervals.forEach(interval => {
        if (!currentIntervals.includes(interval)) {
            d3.select("#minimap").append("rect")
                .attr("x", scaleX(interval))
                .attr("y", 0)
                .attr("width", scaleX.bandwidth())
                .attr("height", minimapHeight)
                .attr("fill", "white")
                .attr("fill-opacity", 0.5);
        }
    });

    return redBox;  // 返回紅框對象
}