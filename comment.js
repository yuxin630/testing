// 確保這個變數已經在前面PHP中定義了
if (typeof reviewData !== "undefined") {
} else {
  console.error("reviewData is not defined.");
}

initializeReviews(reviewData);

// 在点击“评论”时再调取这个函数
d3.select(".comment_comment").on("click", function () {
  // 对现有内容执行淡出动画
  d3.selectAll(".upper-section svg")
    .style("opacity", 0) // 设置动画目标样式
    .remove(); // 在动画结束后移除元素

  initializeReviews(reviewData);
});

function initializeReviews(reviewData) {
  const data = [
    {
      id: 1,
      text: "食物總評",
      reviews: reviewData.map((d) => d.food_comment_sum),
      details: reviewData.map((d) => [
        d.food_review1,
        d.food_review2,
        d.food_review3,
      ]),
    },
    {
      id: 2,
      text: "服務總評",
      reviews: reviewData.map((d) => d.service_comment_sum),
      details: reviewData.map((d) => [
        d.service_review1,
        d.service_review2,
        d.service_review3,
      ]),
    },
    {
      id: 3,
      text: "划算總評",
      reviews: reviewData.map((d) => d.value_comment_sum),
      details: reviewData.map((d) => [
        d.value_review1,
        d.value_review2,
        d.value_review3,
      ]),
    },
    {
      id: 4,
      text: "環境總評",
      reviews: reviewData.map((d) => d.atmosphere_comment_sum),
      details: reviewData.map((d) => [
        d.atmosphere_review1,
        d.atmosphere_review2,
        d.atmosphere_review3,
      ]),
    },
    {
      id: 5,
      text: "朋友評論",
      reviews: reviewData.map((d) => {
        if (d.friend_reviews.length > 0) {
          return d.friend_reviews.map((f) => f.user).join(", ");
        } else {
          return "尚無朋友評論";
        }
      }),
      details: reviewData.map((d) => {
        if (d.friend_reviews.length > 0) {
          return d.friend_reviews.map((f) => f.user + ": " + f.comment);
        } else {
          return ["尚無朋友評論"];
        }
      }),
    },
  ];
  // 在初始化時调取函数
  //   initializeReviews(data);
  console.log(data);
  d3.select(".comment_comment")
    .on("mouseover", function () {
      d3.select(this).style("cursor", "pointer").style("color", "#DC8686"); // 使用 style 来设置颜色
    })
    .on("mouseout", function () {
      d3.select(this).style("color", "black"); // 恢复原始颜色，可以设置为空或原来的颜色值
    });

  const svg = d3
    .select(".upper-section")
    .append("svg")
    .attr("width", 600)
    .attr("height", 230);

  let Fixed = false;
  const groups = svg
    .selectAll(".block-group")
    .data(data)
    .enter()
    .append("g")
    .attr("class", "block-group")
    .attr("transform", (d, i) => `translate(0, ${i * 45})`)
    .on("mouseover", function (event, d) {
      if (!Fixed) {
        // 清除之前的線條和區塊
        svg
          .selectAll(".link, .review-block, .detail-link, .detail-block")
          .remove();
        //標籤變化 - 背景
        d3.select(this).select("rect").attr("fill", "#94a89e"); // 當滑鼠懸停時變深
        // - 文字
        d3.select(this)
          .select("text")
          .attr("fill", "#7B6F5A") // 文字變深
          .attr("font-weight", "bold");

        // 只有在未固定時才顯示評論細節
        showReviews(svg, d, this);
      }
    })
    .on("mouseout", function () {
      if (!Fixed) {
        svg.selectAll(`.detail-group`).remove();
        d3.select(this).select("rect").attr("fill", "#c3d4cc"); // 恢復原背景顏色
        d3.select(this)
          .select("text")
          .attr("fill", "black") // 恢復原文字顏色
          .attr("font-weight", "normal");
        // 只有在未固定時才隱藏評論細節
        svg.selectAll(".link, .review-group").remove();
      }
    })
    .on("click", function (event, d) {
      // 點擊時鎖定顯示評論細節
      if (Fixed) {
        //要收起來時
        //全部標籤變正常
        d3.selectAll(".block-group").select("rect").attr("fill", "#c3d4cc"); // 恢復原背景顏色
        d3.selectAll(".block-group")
          .select("text")
          .attr("fill", "black") // 恢復原文字顏色
          .attr("font-weight", "normal");

        // 當取消固定時，隱藏所有評論細節
        svg.selectAll(".link, .review-group").remove();
        Fixed = false; // 切換固定狀態
      } else {
        // 點擊時保留已顯示的評論細節
        showReviews(svg, d, this);
        Fixed = true;
      }
    });

  groups
    .append("rect")
    .attr("class", "block")
    .attr("width", 100)
    .attr("height", 30)
    .attr("rx", 15) // 圓角矩形的半徑
    .attr("ry", 15)
    .attr("fill", "#c3d4cc"); // 正確設定背景顏色

  groups
    .append("text")
    .attr("class", "block-text")
    .attr("x", 50)
    .attr("y", 18)
    .attr("text-anchor", "middle")
    .attr("dominant-baseline", "middle")
    .attr("fill", "black")
    .text((d) => d.text);

  const colors = ["#ffc6df", "#acccff", "#ffeab0"]; // 定義顏色陣列
    

  // 一開始就顯示第一個類別的總評和第一家餐廳的評論細節
  const firstCategory = data[0];
  console.log("spider", firstCategory);
  showReviews(svg, firstCategory, groups.nodes()[0]); // 顯示第一個類別的總評
  drawCommentDetails(svg, firstCategory, 0, colors, 140, 30); // 顯示第一家餐廳的三則細節評論
  // svg, d, i, colors, endX, endY
}

function showReviews(svg, d, blockGroup) {
  console.log("Data passed to showReviews:", d); // 检查传入的数据
  if (!d.reviews || !Array.isArray(d.reviews)) {
    console.error("Reviews data is missing or not an array:", d.reviews);
    return;
  }
  // 清除之前的線條和區塊
  svg.selectAll(".link, .review-block, .detail-link, .detail-block").remove();

  const blockX = 20;
  const blockY = 30; // 這裡可以根據需要調整Y座標 一開始高度
  const blockWidth = 150;
  const colors = ["#ffc6df", "#acccff", "#ffeab0"]; // 定義顏色陣列

  // 獲取總評區塊的位置，作為連線的起點
  const blockTransform = d3.select(blockGroup).attr("transform");
  const [translateX, translateY] = blockTransform
    .match(/translate\(([^,]+),([^)]+)\)/)
    .slice(1)
    .map(Number);

  let isFixed = false; // 初始化为未固定状态
  let previousEndY = blockY + 10; // 初始Y位置

  d.reviews.forEach((review, i) => {
    // 计算文本高度
    const tempText = svg
      .append("text")
      .attr("class", "temp-text")
      .attr("x", 0) // 放在視圖外，避免影響佈局
      .attr("y", 0)
      .attr("text-anchor", "start")
      .attr("dominant-baseline", "hanging")
      .attr("visibility", "hidden")
      .attr("width", 130) // 根據區塊寬度設置文本寬度
      .text(review); // 将多个评论总评连接成一个文本

    wrapText(tempText, 130);

    const numberOfLines = tempText.attr("data-lines");
    console.log("Number of lines:", numberOfLines); // 可以在這裡讀取確切的行數

    const bbox = tempText.node().getBBox();
    const textHeight = bbox.height + 10; // 加上適當的 padding
    tempText.remove();

    console.log("bbox", bbox);
    // 根據文本高度調整區塊高度
    const blockHeight = Math.max(textHeight - numberOfLines*6, 45); // 設置最小高度為 45，避免過小
    const startX = translateX - 50 + blockWidth;
    const startY = translateY + 15; //線連在同一個點上
    const endX = blockX + 120;
    // const endY = previousEndY; // 更新endY位置
    // const endY = blockY + i * 50 + 35; // 調整間距以容納評論細節
    const endY = previousEndY; // 更新endY位置

    // 更新下一個區塊的起始位置
    previousEndY += blockHeight + 5; // 20 是區塊之間的間距
    // previousEndY += blockHeight * 5 + 50; // 20 是區塊之間的間距

    // 繪製非直線曲線並添加動畫效果
    svg
      .append("path")
      .attr("class", "link")
      .attr("fill", "none")
      .attr("stroke", colors[i]) // 使用顏色陣列中的顏色
      .attr("stroke-width", 2)
      .attr(
        "d",
        d3
          .linkHorizontal()
          .x((d) => d[0])
          .y((d) => d[1])({
          source: [startX, startY],
          target: [startX, startY], // 動畫起始點在源點
        })
      )
      .transition() // 動畫過渡
      .duration(300) // 持續時間300ms
      .attr(
        "d",
        d3
          .linkHorizontal()
          .x((d) => d[0])
          .y((d) => d[1])({
          source: [startX, startY],
          target: [endX - 20, endY - 10],
        })
      );

    const reviewGroup = svg
      .append("g")
      .attr("class", `review-group review-${i + 1}`)
      .attr(
        "transform",
        `translate(${endX + 10}, ${endY - 40})` // - textHeight / ((i + 1) * 2)
      ) //調整區塊間距
      .datum(d) // 使用 .datum 绑定数据对象
      .on("mouseover", function (event, d) {
        console.log("Data passed to drawCommentDetails on mouseover:", d);
        if (!isFixed) {
          svg.selectAll(`.detail-group`).remove();
          drawCommentDetails(svg, d, i, colors, endX, endY);
          d3.select(this).style("cursor", "pointer");
          d3.select(this).select("rect").attr("fill-opacity", 0.8);
          d3.select(this)
            .select("text")
            .attr("fill", "#7B6F5A")
            .attr("font-weight", "bold");
        }
      })
      .on("mouseout", function () {
        if (!isFixed) {
          // 只有在未固定时才移除详细评论
          svg.selectAll(`.detail-group`).remove();
          d3.select(this).select("rect").attr("fill", colors[i]);
          d3.select(this)
            .select("text")
            .attr("fill", "black")
            .attr("font-weight", "normal");
        }
      })
      .on("click", function (event, d) {
        console.log("Data passed to drawCommentDetails on click:", d);
        if (isFixed) {
          svg.selectAll(`.detail-group`).remove();
          isFixed = false;
        } else {
          drawCommentDetails(svg, d, i, colors, endX, endY);
          isFixed = true;
        }
      });

    // 添加区块
    reviewGroup
      .append("rect")
      .attr("width", 0) // 動畫起始寬度為0
      .attr("height", blockHeight)
      .attr("rx", 10)
      .attr("ry", 10)
      .attr("fill", colors[i]) // 使用顏色陣列中的顏色作為背景
      .attr("fill-opacity", 0.5)
      .attr("transform", "translate(-30, 0)") // 向左移動區塊20像素
      .transition() // 動畫過渡
      .duration(300) // 持續時間300ms
      .attr("width", 190) // 最終寬度為130
      .attr("height", blockHeight);

    // 添加文字
    reviewGroup
      .append("text")
      .attr("class", "block-text")
      .attr("x", -20)
      .attr("y", 20)
      .attr("text-anchor", "start")
      .attr("dominant-baseline", "middle")
      .text(review)
      .transition() // 動畫過渡
      .duration(300) // 持續時間300ms
      .call(wrapText, 180) // 自动换行
      .attr("opacity", 1); // 最終不透明度為1
  });
}

function drawCommentDetails(svg, d, i, colors, endX, endY) {
  console.log("Details passed to drawCommentDetails:", d.details);
  //   const comments = d.details; // 假设 details 是传递的评论细节数组
  const comments = d.details[i]; // Access the correct array for the selected review

  if (!comments || comments.length === 0) {
    console.warn("No details available for this review.");
    return; // 如果没有 details 数据，直接退出函数
  }

  let previousEndY = 0; // 評論細節一開始在的高度

  comments.forEach((comment, j) => {
    const detailEndX = endX + 170; // 调整评论细节的X坐标以增加曲线效果
    const detailStartY = previousEndY;

    // 计算文字高度，考虑换行
    const tempText = svg
      .append("text")
      .attr("class", "temp-text")
      .attr("x", detailEndX + 10)
      .attr("y", 0)
      .attr("text-anchor", "start")
      .attr("dominant-baseline", "hanging")
      .attr("visibility", "hidden")
      .attr("width", 150)
      .text(comment);

    wrapText(tempText, 150); // 处理文本换行
    // console.log(comment);
    const bbox = tempText.node().getBBox();
    const textHeight = bbox.height + 10; // 加上适当的 padding
    tempText.remove();

    const detailBlockHeight = textHeight;
    previousEndY += detailBlockHeight + 10;

    // 创建一个 g 元素将评论细节包裹起来
    const detailGroup = svg
      .append("g")
      .attr("class", `detail-group detail-${d.id}-${j}`);

    // 绘制细节连接线，添加到 g 元素中
    detailGroup
      .append("path")
      .attr("class", "detail-link")
      .attr("fill", "none")
      .attr("stroke", colors[i]) // 使用颜色数组中的颜色
      .attr("stroke-width", 1.5)
      .attr(
        "d",
        d3
          .linkHorizontal()
          .x((d) => d[0])
          .y((d) => d[1])({
          source: [endX + 170, endY], // 連接線起點
          target: [endX + 170, endY], // 動畫起始點在源點
        })
      )
      .transition() // 添加動畫過渡
      .duration(300) // 設定持續時間
      .attr(
        "d",
        d3
          .linkHorizontal()
          .x((d) => d[0])
          .y((d) => d[1])({
          source: [endX + 170, endY], // 連接線起點
          target: [detailEndX + 25, detailStartY + detailBlockHeight / 2], // 連接線終點
        })
      );

    // 显示评论细节区块，添加到 g 元素中
    detailGroup
      .append("rect")
      .attr("class", "detail-block")
      .attr("x", detailEndX + 25)
      .attr("y", detailStartY)
      .attr("height", textHeight)
      .attr("rx", 10)
      .attr("ry", 10)
      .attr("fill", colors[i]) // 使用颜色数组中的颜色作为背景
      .attr("fill-opacity", 0) // 初始透明度為0
      .attr("width", 0) // 初始寬度為0
      .transition() // 動畫過渡
      .duration(300) // 持續時間300ms
      .attr("fill-opacity", 0.5)
      .attr("width", 170); // 最终宽度为200

    // 添加评论文本
    detailGroup
      .append("text")
      .attr("class", "detail-text")
      .attr("x", detailEndX + 40)
      .attr("y", detailStartY + 10)
      .attr("text-anchor", "start")
      .attr("dominant-baseline", "hanging")
      .attr("opacity", 0) // 初始透明度為0
      .text(comment)
      .call(wrapText, 150) // 自動換行
      .transition() // 添加動畫過渡
      .duration(300) // 設定持續時間
      .attr("opacity", 1); // 最終透明度為1
  });
}

// 自動換行函數
function wrapText(text, width) {
  text.each(function () {
    const text = d3.select(this);
    // 將文本拆分為單個字符（或詞組），更適合中文處理
    const words = text.text().split("").reverse(); // 將每個字符視為一個“單詞”
    let word,
      line = [],
      lineNumber = 0,
      lineHeight = 1.1, // ems
      y = text.attr("y"),
      x = text.attr("x"),
      dy = parseFloat(text.attr("dy") || 0),
      tspan = text
        .text(null)
        .append("tspan")
        .attr("x", x)
        .attr("y", y)
        .attr("dy", dy + "em");

    // 確保行數的計算正確
    let numberOfLines = 1; // 初始化為 1 行

    while ((word = words.pop())) {
      // console.log(word); // 輸出每個字符以檢查
      line.push(word);
      tspan.text(line.join(" "));
      if (tspan.node().getComputedTextLength() > width) {
        line.pop(); // 移除最後一個字符，讓它移到下一行
        tspan.text(line.join(" "));
        line = [word];
        tspan = text
          .append("tspan")
          .attr("x", x)
          .attr("y", y)
          .attr("dy", ++lineNumber * lineHeight + dy + "em")
          .text(word);
        numberOfLines++; // 每次換行時增加行數
      }
    }
    // 將確切行數添加到當前的文本元素作為屬性
    text.attr("data-lines", numberOfLines); // 這樣可以在其他地方讀取行數
  });
}
