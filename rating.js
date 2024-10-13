function drawRatingBars(container, data) {
    const dimensions = [
      { label: "食物", value: data.r_rating_food },
      { label: "服務", value: data.r_rate_service },
      { label: "衛生", value: data.r_rate_clean },
      { label: "划算度", value: data.r_rate_value },
    ];
  
    const margin = { top: 10, right: 30, bottom: 40, left: 50 };
    const width = 330 - margin.left - margin.right;
    const height = 120 - margin.top - margin.bottom;
  
    const svg = d3
      .select(container)
      .append("svg")
      .attr("width", width + margin.left + margin.right)
      .attr("height", height + margin.top + margin.bottom)
      .append("g")
      .attr("transform", `translate(${margin.left},${margin.top})`);
  
    const x = d3.scaleLinear().domain([0, 5]).range([0, width]);
  
    const y = d3
      .scaleBand()
      .domain(dimensions.map((d) => d.label))
      .range([0, height])
      .padding(0.1);
  
    // 添加X轴
    svg.append("g").attr("transform", `translate(0,${height})`).call(d3.axisBottom(x).ticks(5).tickFormat(() => ""));
  
    // 调用 addStarsForD3 函数添加星星
    svg.selectAll(".tick").each(function (d, i) {
      if (i !== 0) { // 你可以根据需求修改这个判断，决定在哪些刻度上绘制星星
        addStarsForD3(d3.select(this), 1, -6, 10); // 调用绘制星星的函数
      }
    });
  
    // 绘制条形图
    svg
      .selectAll(".bar")
      .data(dimensions)
      .enter()
      .append("rect")
      .attr("class", "bar")
      .attr("x", 0)
      .attr("y", (d) => y(d.label))
      .attr("width", (d) => x(d.value))
      .attr("height", y.bandwidth())
      .attr("fill", "#F4DEB3");
  
    // 绘制Y轴
    svg
      .append("g")
      .call(d3.axisLeft(y))
      .selectAll("text")
      .style("font-size", "13px");

  }

//   .attr("fill", function(d, i) {
//     const colors = ["#FFD700", "#FF4500", "#4169E1", "#228B22"];
//     return colors[i % colors.length];
//   });

function addStarsForD3(d3Container, rating, xPosition, yPosition, starSize = 10) {
    const fullStarSVG = `
        <svg height="${starSize}px" width="${starSize}px" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 47.94 47.94" xml:space="preserve">
            <path style="fill:#ED8A19;" d="M26.285,2.486l5.407,10.956c0.376,0.762,1.103,1.29,1.944,1.412l12.091,1.757
            c2.118,0.308,2.963,2.91,1.431,4.403l-8.749,8.528c-0.608,0.593-0.886,1.448-0.742,2.285l2.065,12.042
            c0.362,2.109-1.852,3.717-3.746,2.722l-10.814-5.685c-0.752-0.395-1.651-0.395-2.403,0l-10.814,5.685
            c-1.894,0.996-4.108-0.613-3.746-2.722l2.065-12.042c0.144-0.837-0.134-1.692-0.742-2.285l-8.749-8.528
            c-1.532-1.494-0.687-4.096,1.431-4.403l12.091-1.757c0.841-0.122,1.568-0.65,1.944-1.412l5.407-10.956
            C22.602,0.567,25.338,0.567,26.285,2.486z"/>
        </svg>
    `;

    const halfStarSVG = `
        <svg height="${starSize}px" width="${starSize}px" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 47.94 47.94" xml:space="preserve">
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

    // Add full stars to the D3 container
    for (let i = 0; i < fullStars; i++) {
        d3Container
          .append("foreignObject")
          .attr("x", xPosition + i * (starSize + 2)) // 控制每颗星星的X坐标位置
          .attr("y", yPosition) // 控制星星的Y坐标位置
          .attr("width", starSize)
          .attr("height", starSize)
          .html(fullStarSVG);
    }

    // Add half star if needed
    if (hasHalfStar) {
        d3Container
          .append("foreignObject")
          .attr("x", xPosition + fullStars * (starSize + 2)) // 半颗星的X坐标
          .attr("y", yPosition) // 半颗星的Y坐标
          .attr("width", starSize)
          .attr("height", starSize)
          .html(halfStarSVG);
    }
}
