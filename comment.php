<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <title>總評視覺化</title>
    <link rel="stylesheet" href="./comment.css">
    <script src="https://d3js.org/d3.v7.min.js"></script>
</head>

<body>
    <?php
    // 获取餐厅ID
    $r_id1 = $_GET['r_id1'];
    $r_id2 = $_GET['r_id2'];
    $r_id3 = $_GET['r_id3'];

    // 连接数据库并查询数据
    $conn = new mysqli('localhost', 'root', '', 'foodee');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM additional WHERE r_id IN ('$r_id1', '$r_id2', '$r_id3')";
    $result = $conn->query($sql);

    $data = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // 将数据转换为 JSON 格式
    $json_data = json_encode($data);
    // 关闭数据库连接
    $conn->close();

    ?>

    <div class="upper-section">
        <span class="comment_comment">評論</span>
    </div>

    <script type="text/javascript">
        // 在PHP中将JSON数据传递给JS
        const reviewData = <?php echo $json_data; ?>;
        // 你可以先檢查reviewData的內容
        console.log(reviewData);
    </script>

    <script type="module">
        import './comment2.0.js';
    </script>

    <!-- <script src="./comment.js">
        print('helloe');
        
    </script> -->



    <!-- <script src="https://d3js.org/d3.v7.min.js"></script>
    <script>
        // 在初始化時调取函数
        initializeReviews();

        d3.select('.label')
            .on('mouseover', function() {
                d3.select(this)
                    .style("cursor", "pointer")
                    .style('color', '#DC8686'); // 使用 style 来设置颜色
            })
            .on('mouseout', function() {
                d3.select(this)
                    .style('color', ''); // 恢复原始颜色，可以设置为空或原来的颜色值
            });

        // 在点击“评论”时再调取这个函数
        d3.select('.label').on('click', function() {
            // 对现有内容执行淡出动画
            d3.selectAll('#chart svg')
                .transition()
                .duration(300) // 设置动画持续时间
                .style('opacity', 0) // 设置动画目标样式
                .remove(); // 在动画结束后移除元素

            // 在动画结束后再执行初始化函数
            setTimeout(() => {
                initializeReviews();
            }, 300); // 延迟时间与过渡持续时间相同
        });


        // 封装初始化绘制的函数
        function initializeReviews() {
            const data = [{
                    id: 1,
                    text: '食物總評',
                    reviews: ['餐廳1總評', '餐廳2總評', '餐廳3總評']
                },
                {
                    id: 2,
                    text: '服務總評',
                    reviews: ['餐廳1總評', '餐廳2總評', '餐廳3總評']
                },
                {
                    id: 3,
                    text: '划算總評',
                    reviews: ['餐廳1總評', '餐廳2總評', '餐廳3總評']
                },
                {
                    id: 4,
                    text: '環境總評',
                    reviews: ['餐廳1總評', '餐廳2總評', '餐廳3總評']
                }
            ];

            const svg = d3.select('#chart').append('svg')
                .attr('width', 800)
                .attr('height', 400);

            const groups = svg.selectAll('.block-group')
                .data(data)
                .enter()
                .append('g')
                .attr('class', 'block-group')
                .attr('transform', (d, i) => `translate(70, ${20 + i * 50})`)
                .on('mouseover', function() {
                    d3.select(this).select('rect')
                        .attr('fill', '#E0D4C2'); // 當滑鼠懸停時變深
                    d3.select(this).select('text')
                        .attr('fill', '#7B6F5A') // 文字變深
                        .attr('font-weight', 'bold');
                })
                .on('mouseout', function() {
                    d3.select(this).select('rect')
                        .attr('fill', '#F8EDE3'); // 恢復原背景顏色
                    d3.select(this).select('text')
                        .attr('fill', 'black') // 恢復原文字顏色
                        .attr('font-weight', 'normal');
                })
                .on('click', function(event, d) {
                    d3.selectAll('.block-group')
                        .style('display', 'none');

                    d3.select(this)
                        .style('display', 'block')
                        .transition()
                        .attr('transform', `translate(70, 96)`); // 移動到四個區塊的中間位置 其他消失

                    showReviews(d);
                });

            groups.append('rect')
                .attr('class', 'block')
                .attr('width', 100)
                .attr('height', 30)
                .attr('rx', 15) // 圓角矩形的半徑
                .attr('ry', 15)
                .attr('fill', '#F8EDE3'); // 正確設定背景顏色

            groups.append('text')
                .attr('class', 'block-text')
                .attr('x', 50)
                .attr('y', 18)
                .attr('text-anchor', 'middle')
                .attr('dominant-baseline', 'middle')
                .attr('fill', 'black')
                .text(d => d.text);

            function showReviews(d) {
                // 清除之前的線條和區塊
                svg.selectAll('.link, .review-block, .detail-link, .detail-block').remove();

                const blockX = 20;
                const blockY = 80; // 這裡可以根據需要調整Y座標
                const blockWidth = 150;
                const colors = ["#FF70AE", "#85B4FF", "#FFCE47"]; // 定義顏色陣列

                let isFixed = false; // 初始化为未固定状态

                d.reviews.forEach((review, i) => {
                    const startX = blockX + blockWidth;
                    const startY = blockY + 30;
                    const endX = blockX + 200;
                    const endY = blockY - 45 + (i * 50) + 35; // 調整間距以容納評論細節

                    // 繪製非直線曲線並添加動畫效果
                    svg.append('path')
                        .attr('class', 'link')
                        .attr('fill', 'none')
                        .attr('stroke', colors[i]) // 使用顏色陣列中的顏色
                        .attr('stroke-width', 2)
                        .attr('d', d3.linkHorizontal()
                            .x(d => d[0])
                            .y(d => d[1])({
                                source: [startX, startY],
                                target: [startX, startY] // 動畫起始點在源點
                            })
                        )
                        .transition() // 動畫過渡
                        .duration(300) // 持續時間300ms
                        .attr('d', d3.linkHorizontal()
                            .x(d => d[0])
                            .y(d => d[1])({
                                source: [startX, startY],
                                target: [endX + 65, endY]
                            })
                        );

                    // 将 rect 和 text 包装成一个 group
                    const reviewGroup = svg.append('g')
                        .attr('class', `review-group review-${i + 1}`)
                        .attr('transform', `translate(${endX + 65}, ${endY - 30})`);

                    // 添加区块
                    reviewGroup.append('rect')
                        .attr('width', 0) // 動畫起始寬度為0
                        .attr('height', 60)
                        .attr('rx', 10)
                        .attr('ry', 10)
                        .attr('fill', colors[i]) // 使用顏色陣列中的顏色作為背景
                        .attr('fill-opacity', 0.5)
                        .transition() // 動畫過渡
                        .duration(300) // 持續時間300ms
                        .attr('width', 130) // 最終寬度為130
                        .attr('height', 40);

                    // 添加文字
                    reviewGroup.append('text')
                        .attr('class', 'block-text')
                        .attr('x', 65)
                        .attr('y', 20)
                        .attr('text-anchor', 'middle')
                        .attr('dominant-baseline', 'middle')
                        .text(review)
                        .transition() // 動畫過渡
                        .duration(300) // 持續時間300ms
                        .attr('opacity', 1); // 最終不透明度為1

                    // 为 group 添加鼠标事件
                    reviewGroup
                        .on('mouseover', function(event, d) {
                            if (!isFixed) { // 只有在未固定时才显示详细评论
                                drawCommentDetails(svg, d, i, colors, endX, endY);
                                d3.select(this).style("cursor", "pointer");
                                d3.select(this).select('rect').attr('fill-opacity', 0.8); // 当鼠标悬停时变深
                                d3.select(this).select('text').attr('fill', '#7B6F5A').attr('font-weight', 'bold'); // 文字变深
                            }
                        })
                        .on('mouseout', function() {
                            if (!isFixed) { // 只有在未固定时才移除详细评论
                                svg.selectAll(`.detail-group`).remove();
                                d3.select(this).select('rect').attr('fill', colors[i]);
                                d3.select(this).select('text').attr('fill', 'black').attr('font-weight', 'normal');
                            }
                        })
                        .on('click', function(event, d) {
                            if (isFixed) {
                                // 如果已经固定，再次点击时移除细节
                                svg.selectAll(`.detail-group`).remove();
                                isFixed = false;
                            } else {
                                // 如果未固定，点击时显示并固定细节
                                drawCommentDetails(svg, d, i, colors, endX, endY);
                                isFixed = true;
                            }
                        });

                });

            };
        }


        function drawCommentDetails(svg, d, i, colors, endX, endY) {
            const comments = [
                [{
                        id: 1,
                        comment: "這是餐廳1的第一個評論細節，內容比較長。"
                    },
                    {
                        id: 1,
                        comment: "這是餐廳1的第二個評論細節。"
                    },
                    {
                        id: 1,
                        comment: "這是餐廳1的第三個評論細節，這裡的文字稍微多一點。"
                    }
                ],
                [{
                        id: 2,
                        comment: "這是餐廳2的第一個評論細節，簡短一些。"
                    },
                    {
                        id: 2,
                        comment: "這是餐廳2的第二個評論細節。"
                    },
                    {
                        id: 2,
                        comment: "這是餐廳2的第三個評論細節，這裡的文字稍微多一點。"
                    }
                ],
                [{
                        id: 3,
                        comment: "這是餐廳3的第一個評論細節，內容詳細。"
                    },
                    {
                        id: 3,
                        comment: "這是餐廳3的第二個評論細節，稍微簡短一些。"
                    },
                    {
                        id: 3,
                        comment: "這是餐廳3的第三個評論細節，這裡的文字稍微多一點。"
                    }
                ]
            ][i]; // 根据餐厅总评数据索引选择对应的评论细节

            let previousEndY = endY - 45; // 初始化为总评区块的结束位置

            comments.forEach((commentObj, j) => {
                const detailEndX = endX + 180; // 调整评论细节的X坐标以增加曲线效果
                const detailStartY = previousEndY + 10;

                // 计算文字高度，考虑换行
                const tempText = svg.append('text')
                    .attr('class', 'temp-text')
                    .attr('x', detailEndX + 10)
                    .attr('y', 0)
                    .attr('text-anchor', 'start')
                    .attr('dominant-baseline', 'hanging')
                    .attr('visibility', 'hidden')
                    .attr('width', 150)
                    .text(commentObj.comment);

                wrapText(tempText, 150); // 处理文本换行

                const bbox = tempText.node().getBBox();
                const textHeight = bbox.height + 10; // 加上适当的 padding
                tempText.remove();

                const detailBlockHeight = textHeight;
                previousEndY += detailBlockHeight + 10; // 为下一个评论预留空间

                // 创建一个 g 元素将评论细节包裹起来
                const detailGroup = svg.append('g')
                    .attr('class', `detail-group detail-${commentObj.id}-${j}`);

                // 绘制细节连接线，添加到 g 元素中
                detailGroup.append('path')
                    .attr('class', 'detail-link')
                    .attr('fill', 'none')
                    .attr('stroke', colors[i]) // 使用颜色数组中的颜色
                    .attr('stroke-width', 1.5)
                    .attr('d', d3.linkHorizontal()
                        .x(d => d[0])
                        .y(d => d[1])({
                            source: [endX + 195, endY], // 连接线起点
                            target: [detailEndX + 65, detailStartY + detailBlockHeight / 2] // 连接线终点
                        })
                    );

                // 显示评论细节区块，添加到 g 元素中
                detailGroup.append('rect')
                    .attr('class', 'detail-block')
                    .attr('x', detailEndX + 65)
                    .attr('y', detailStartY)
                    .attr('height', textHeight)
                    .attr('rx', 10)
                    .attr('ry', 10)
                    .attr('fill', colors[i]) // 使用颜色数组中的颜色作为背景
                    .attr('fill-opacity', 0.5)
                    .attr('width', 200); // 最终宽度为200

                // 添加评论文本
                detailGroup.append('text')
                    .attr('class', 'detail-text')
                    .attr('x', detailEndX + 75)
                    .attr('y', detailStartY + 10)
                    .attr('text-anchor', 'start')
                    .attr('dominant-baseline', 'hanging')
                    .text(commentObj.comment)
                    .call(wrapText, 180); // 自动换行
            });
        }

        // 自動換行函數
        function wrapText(text, width) {
            text.each(function() {
                const text = d3.select(this);
                // 將文本拆分為單個字符（或詞組），更適合中文處理
                const words = text.text().split('').reverse(); // 將每個字符視為一個“單詞”
                let word,
                    line = [],
                    lineNumber = 0,
                    lineHeight = 1.1, // ems
                    y = text.attr("y"),
                    x = text.attr("x"),
                    dy = parseFloat(text.attr("dy") || 0),
                    tspan = text.text(null).append("tspan").attr("x", x).attr("y", y).attr("dy", dy + "em");

                while (word = words.pop()) {
                    // console.log(word); // 輸出每個字符以檢查
                    line.push(word);
                    tspan.text(line.join(" "));
                    if (tspan.node().getComputedTextLength() > width) {
                        line.pop(); // 移除最後一個字符，讓它移到下一行
                        tspan.text(line.join(" "));
                        line = [word];
                        tspan = text.append("tspan")
                            .attr("x", x)
                            .attr("y", y)
                            .attr("dy", ++lineNumber * lineHeight + dy + "em")
                            .text(word);
                    }
                }
            });
        }
    </script> -->
</body>

</html>