if (typeof reviewData !== "undefined") {
} else {
  console.error("reviewData is not defined.");
}

initializeReviews(reviewData);

// 點擊「評論」後調用這個函數
d3.select(".comment_comment").on("click", function () {
  d3.selectAll(".upper-section svg").remove(); // 移除現有的 SVG
  initializeReviews(reviewData); // 重新初始化
});

function initializeReviews(reviewData) {
  const data = [
    {
      id: 1,
      text: "食物",
      reviews: reviewData.map((d) => d.food_comment_sum),
      details: reviewData.map((d) => [
        d.food_review1,
        d.food_review2,
        d.food_review3,
      ]),
    },
    {
      id: 2,
      text: "服務",
      reviews: reviewData.map((d) => d.service_comment_sum),
      details: reviewData.map((d) => [
        d.service_review1,
        d.service_review2,
        d.service_review3,
      ]),
    },
    {
      id: 3,
      text: "划算",
      reviews: reviewData.map((d) => d.value_comment_sum),
      details: reviewData.map((d) => [
        d.value_review1,
        d.value_review2,
        d.value_review3,
      ]),
    },
    {
      id: 4,
      text: "環境",
      reviews: reviewData.map((d) => d.atmosphere_comment_sum),
      details: reviewData.map((d) => [
        d.atmosphere_review1,
        d.atmosphere_review2,
        d.atmosphere_review3,
      ]),
    },
  ];

  const pageWidth = window.innerWidth; // 獲取頁面寬度
  const blockWidth = (pageWidth - 100) / 4; // 計算總評區塊的寬度，確保不會超出頁面寬度
  const svg = d3
    .select(".upper-section")
    .append("svg")
    .attr("width", pageWidth)
    //.attr("height", 300); // 設置 SVG 的寬度和高度

  let Fixed = null; // 記錄當前選中的總評狀態

  const groups = svg
    .selectAll(".block-group")
    .data(data)
    .enter()
    .append("g")
    .attr("class", "block-group")
    .attr("transform", (d, i) => `translate(${i * blockWidth}, 0)`) // 水平排列總評區塊
    .on("mouseover", function () {
      d3.select(this).style("cursor", "pointer");
    })
    .on("click", function (event, d) {
      // 呼叫顯示評論的函數
      handleButtonClick(this, svg, d, blockWidth);
    });

  // 繪製區塊中的文字
  groups
    .append("text")
    .attr("class", "block-text")
    .attr("x", 40)
    .attr("y", 35)
    .attr("text-anchor", "middle")
    .attr("dominant-baseline", "middle")
    .attr("fill", "black")
    .text((d) => d.text)
    .each(function (d) {
      const textElement = d3.select(this);
      const bbox = textElement.node().getBBox();

      // 繪製根據文字大小調整的總評區塊
      d3.select(this.parentNode)
        .insert("rect", "text") // 在 text 前插入 rect
        .attr("class", "block")
        .attr("width", bbox.width + 20) // 根據文字的寬度來設置 rect 寬度
        .attr("height", bbox.height + 10) // 根據文字的高度來設置 rect 高度
        .attr("x", bbox.x - 10) // 調整 rect 的 x 位置
        .attr("y", bbox.y - 5) // 調整 rect 的 y 位置
        .attr("rx", 15) // 設置圓角
        .attr("ry", 15)
        .attr("fill", "#f3f3f3"); // 預設背景顏色
    });

  // 預設顯示第一個類別（食物）的總評和評論細節
  const firstCategory = data[0];
  const firstGroup = groups.nodes()[0]; // 獲取第一個按鈕
  handleButtonClick(firstGroup, svg, firstCategory, blockWidth); // 模擬點擊第一個按鈕
}

// 點擊按鈕後處理顯示評論的函數
function handleButtonClick(button, svg, category, blockWidth) {
  d3.selectAll(".block-group").select("rect").attr("fill", "#c3d4cc"); // 恢復預設背景顏色
  d3.selectAll(".block-group").select("text").attr("fill", "black").attr("font-weight", "normal");

  // 選中當前的總評並改變顏色
  d3.select(button).select("rect").attr("fill", "rgb(148, 168, 158)"); // 選中總評變為亮色
  d3.select(button).select("text").attr("fill", "black").attr("font-weight", "bold");

  showReviews(svg, category, button, blockWidth); // 顯示評論
}

function showReviews(svg, d, blockGroup, blockWidth) {
  // 清除之前的评论区块
  svg.selectAll(".review-group").remove();

  let previousEndY = 60; // 初始化Y位置，確保從適當位置開始，調整為 50px 向下移動 10px
  const colors = ["#ffc6df", "#acccff", "#ffeab0"]; // 顏色陣列
  const fixedXPosition = 20; // 固定左邊距離，確保所有評論位置一致

  d.reviews.forEach((review, i) => {
    const tempText = svg
      .append("text")
      .attr("class", "temp-text")
      .attr("x", fixedXPosition) // 固定X軸位置
      .attr("y", 20)
      .attr("visibility", "hidden")
      .text(review);

    // 根據矩形宽度進行換行處理
    wrapText(tempText, blockWidth - 40);

    const bbox = tempText.node().getBBox();
    const textHeight = bbox.height + 15; // 加上 padding
    const textWidth = bbox.width + 20; // 加上 padding
    tempText.remove(); // 移除臨時元素

    const blockHeight = Math.max(textHeight, 50); // 設定區塊的最小高度

    const reviewGroup = svg
      .append("g")
      .attr("class", `review-group review-${i + 1}`)
      .attr("transform", `translate(${fixedXPosition}, ${previousEndY})`); // 使用固定的X位置

    previousEndY += blockHeight + 20; // 每個區塊之間的間距增加為 20

    reviewGroup
      .append("rect")
      .attr("width", textWidth) // 根據計算出的文字寬度設定矩形寬度
      .attr("height", blockHeight) // 設定矩形高度為文本高度
      .attr("rx", 10)
      .attr("ry", 10)
      .attr("fill", colors[i])
      .attr("fill-opacity", 0.5);

    reviewGroup
      .append("text")
      .attr("x", 10) // 保持左對齊
      .attr("y", 0)
      .attr("text-anchor", "start")
      .attr("dominant-baseline", "middle")
      .attr("opacity", 0)
      .text(review)
      .call(wrapText, blockWidth - 40) // 自动换行处理
      .transition()
      .duration(500)
      .attr("opacity", 1); // 淡入动画
  });

  // 動態調整 SVG 的高度，以包裹所有評論
  svg.attr("height", previousEndY + 10); // 增加10px padding
}

// 文字換行函數
function wrapText(text, blockWidth) {
  text.each(function () {
    const textElement = d3.select(this);

    // 先按句號和逗號進行分割
    const sentences = textElement.text().split(/(?<=。|，)/).filter(Boolean); // 按句號和逗號分割文本
    let y = parseFloat(textElement.attr("y")),
      x = textElement.attr("x"),
      lineNumber = 0,
      lineHeight = 10; // 設置行間距為 10px

    textElement.text(null); // 清空文本内容，準備進行逐句插入

    sentences.forEach((sentence) => {
      let tspan = textElement.append("tspan")
        .attr("x", x)
        .attr("y", y + lineNumber * lineHeight)
        .attr("text-anchor", "start"); // 確保左對齊
      tspan.text(sentence);

      // 如果当前行的寬度超過了區塊寬度，進行換行處理
      if (tspan.node().getComputedTextLength() > blockWidth) {
        wrapSentenceInTspans(tspan, blockWidth, x, ++lineNumber * lineHeight + y);
      }
      lineNumber++; // 每次新句子的 y 位置增加 5px
    });
  });
}

function wrapSentenceInTspans(tspan, blockWidth, x, y) {
  const words = tspan.text().split(/\s+/).reverse(); // 將句子分割成單詞
  let word,
    line = [],
    lineNumber = 0,
    lineHeight = 10; // 設定行高為 5px

  tspan.text(null); // 清空當前 tspan 的內容，準備換行

  while ((word = words.pop())) {
    line.push(word);
    tspan.text(line.join(" ")); // 更新當前行的內容

    // 如果當前行的長度超過 blockWidth，將最後一個單詞移至下一行
    if (tspan.node().getComputedTextLength() > blockWidth) {
      line.pop(); // 移除超過寬度的單詞
      tspan.text(line.join(" ")); // 還原到上一次狀態
      line = [word]; // 將超出寬度的單詞移至新行
      tspan = tspan // 創建新的 tspan 來換行
        .append("tspan")
        .attr("x", x) // 確保新行左對齊
        .attr("y", y + ++lineNumber * lineHeight)
        .attr("text-anchor", "start") // 左對齊
        .text(word);
    }
  }
}
