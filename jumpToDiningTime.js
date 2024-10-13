//用餐時間按鈕跳轉
function jumpToDiningTime(diningTime) {
    if (!loaded.length) {
        console.error('Data is not loaded yet.');
        return;
    }
    // 處理滾動時用餐時間為空的資料
    if (diningTime === 'all') {
        
        let foundText = null;

        d3.selectAll('text').each(function() {
            const text = d3.select(this);
            const textContent = text.text();
            if (textContent.includes("用餐時間:")) {
                foundText = text.node();
            }
        });

        if (foundText) {
            foundText.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            console.warn(`未找到匹配的文本`);
        }
        return;
    }

    const matchingData = loaded.find(d => Number(d.r_time_low) === Number(diningTime));
    console.log('Matching data:', matchingData);
    
    if (matchingData) {
        // 使用 D3.js 選擇所有的 text 元素
        const texts = d3.selectAll('text');
        let foundText = null;

        texts.each(function() {
            const text = d3.select(this);
            const textContent = text.text();
            if (textContent.includes(`用餐時間: ${diningTime}`)) {
                foundText = text.node();
            }
        });

        if (foundText) {
            // 滾動到這個 text 的位置
            foundText.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            console.warn(`未找到匹配的文本`);
        }
    } else {
        console.error(`未找到用餐時間 ${diningTime} 的數據`);
    }
}