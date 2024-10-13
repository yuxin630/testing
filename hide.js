function toggleCircles(innerRadius, circleRadius) {
    document.getElementById("hide").addEventListener("click", function () {
        const button = this;
        const isHidden = button.classList.contains("hidden");

        d3.selectAll(".circle-group").each(function (d, i) {
            const circleGroup = d3.select(this);

            // 使用每個 circle 對應的 patternId
            const patternId = `pattern-${i}`; // 假設 patternId 與 circle 順序相關聯
            const patternGroup = d3.select(`#${patternId}`);  // 選擇對應的 pattern

            if (isHidden) {
                // 恢復原狀：顯示外圈並縮小內圈
                circleGroup.selectAll("path.left, text.days, path.right, circle.small-circle, text.icon-text").style("display", "block");

                circleGroup.select("circle")
                    .transition()
                    .duration(500)
                    .attr("r", innerRadius);  // 縮小內圈（根據傳入的 innerRadius 變數）

                patternGroup.select("image")
                    .transition()
                    .duration(500)
                    .attr("width", 2 * innerRadius)  // 縮小圖片的寬度
                    .attr("height", 2 * innerRadius);  // 縮小圖片的高度
            } else {
                // 隱藏外圈並放大內圈
                circleGroup.selectAll("path.left, text.days, path.right, circle.small-circle, text.icon-text").style("display", "none");

                circleGroup.select("circle")
                    .transition()
                    .duration(500)
                    .attr("r", circleRadius);  // 放大內圈（根據傳入的 circleRadius 變數）

                patternGroup.select("image")
                    .transition()
                    .duration(500)
                    .attr("width", 2 * circleRadius)  // 放大圖片的寬度
                    .attr("height", 2 * circleRadius);  // 放大圖片的高度
            }
        });

        // 更新按鈕狀態
        if (isHidden) {
            button.classList.remove("hidden");
            button.style.backgroundColor = "";  // 恢復原本的背景色
            button.textContent = "隱藏外圈";
        } else {
            button.classList.add("hidden");
            button.style.backgroundColor = "rgb(255, 240, 179)";  // 改變按鈕背景色
            button.textContent = "顯示外圈";
        }
    });
}


