<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
require_once __DIR__ . '/../../../lib/QrHelper.php';
$lang = LanguageController::getInstance();
?>
<style>
    .event-item-wrapper {
        position: relative;
        min-height: 400px;
        margin-bottom: 2rem;
    }
    .answer-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 1rem;
        background: var(--background-secondary,#f4f4f4);
        margin: 0.3rem 0;
        border-radius: 0.8rem;
    }
    .answer-value {
        font-size: 1rem;
    }
    .delete-btn {
        background:none; border:none; color:var(--text-secondary,#666); cursor:pointer; font-size:1.2rem;
    }
    .delete-btn:hover { color:#dc3545; }
</style>
<?php require_once __DIR__ . '/../../../views/header.php'; ?>
<main class="container">
    <article>
        <h1><?php echo htmlspecialchars($eventItem['question']); ?></h1>
        <p>
            <a href="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($event['key']); ?>">
                ← Back to event <?php echo htmlspecialchars($event['key']); ?>
            </a>
        </p>
    </article>

    <div class="grid" style="margin-top:2rem; gap:2rem;">
        <!-- Left: content area -->
        <article style="grid-column: span 3;">
            <?php if ($eventItem['type'] === 'wordcloud'): ?>
                <div id="word-cloud-container" class="event-item-wrapper"></div>
            <?php else: ?>
                <canvas id="chart-container" class="event-item-wrapper" style="transform:scale(0.7); transform-origin:center; cursor:pointer;"></canvas>
            <?php endif; ?>
            <div style="text-align:center; margin-top:1rem;">
                <a href="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($event['key']); ?>/eventitem/<?php echo $eventItem['id']; ?>/answer" class="primary" role="button" target="_blank" style="padding:0.8rem 2rem; font-size:1.1rem;">
                    <?php echo htmlspecialchars($lang->translate('add_your_answer', 'Add your answer')); ?>
                </a>
            </div>
        </article>
        <!-- Right: QR code only -->
        <article style="grid-column: span 1; text-align:center;">
            <div id="event-qr-block" style="margin:1rem 0;"></div>
            <script src="/apps/involved/js/eventQrBlock.js"></script>
            <script>
            document.addEventListener('DOMContentLoaded',function(){
                const scheme=window.location.protocol.replace(':','');
                const host=window.location.host;
                const lang='<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>';
                const eventKey='<?php echo htmlspecialchars($event["key"]); ?>';
                const itemId='<?php echo $eventItem['id']; ?>';
                const addAnswerUrl=`${scheme}://${host}/${lang}/involved/${eventKey}/eventitem/${itemId}/answer`;
                const eventPassword=<?php echo json_encode($event['password'] ?? null); ?>;
                new EventQrBlock('#event-qr-block', addAnswerUrl, eventKey, eventPassword, '', true);
            });
            </script>
        </article>
    </div>

    <!-- Add answers section -->
    <article style="margin-top:2rem;">
        <h3>Add answer</h3>
        <form id="add-answer-form" style="margin-top:0.5rem;">
            <input type="text" id="answer-value" placeholder="Answer" required style="width:100%; margin-bottom:0.5rem;">
            <button type="submit" class="primary" style="width:100%;">Add</button>
        </form>
        <ul id="answer-list" style="list-style:none;padding:0;margin-top:1rem;text-align:left;"></ul>
    </article>

</main>

<script src="/apps/involved/js/OverlayObjectHelper.js"></script>
<script>
(function(){
    // Common variables
    const lang='<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>';
    const eventKey='<?php echo htmlspecialchars($event['key']); ?>';
    const itemId=<?php echo (int)$eventItem['id']; ?>;
    const itemType='<?php echo $eventItem['type']; ?>';
    const answersEndpoint=`/${lang}/involved/${eventKey}/eventitem/${itemId}/answers`;
    const addEndpoint=`/${lang}/involved/${eventKey}/eventitem/${itemId}/answer/add`;
    const listEl=document.getElementById('answer-list');
    const form=document.getElementById('add-answer-form');
    const input=document.getElementById('answer-value');
    
    // Overlay helper instance
    let overlayHelper = null;
    if (typeof OverlayObjectHelper !== 'undefined') {
        overlayHelper = window.__itemOverlayHelper = (window.__itemOverlayHelper || new OverlayObjectHelper());
    }
    
    // Initialize based on item type
    if (itemType === 'wordcloud') {
        initWordCloud();
    } else {
        loadChartJs().then(initChart);
    }
    
    // Load Chart.js dynamically if needed
    function loadChartJs() {
        return new Promise((resolve) => {
            if (window.Chart) {
                resolve();
                return;
            }
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            script.onload = resolve;
            document.head.appendChild(script);
        });
    }
    
    // Word Cloud initialization
    function initWordCloud() {
        // Word cloud specific code will go here
        // Fetch and display words
        function renderWords(words) {
            // Process and display words
            if (!words || !words.length) return;
            
            if (typeof WordCloud !== 'undefined') {
                const container = document.getElementById('word-cloud-container');
                const width = container.offsetWidth;
                const height = container.offsetHeight || 400;
                
                // Prepare canvas if not exists
                let canvas = container.querySelector('canvas');
                if (!canvas) {
                    canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    container.appendChild(canvas);
                }
                
                // Format words for WordCloud2.js
                const list = words.map(w => [w.word, w.weight || 1]);
                
                // Generate word cloud
                WordCloud(canvas, {
                    list: list,
                    gridSize: Math.round(16 * width / 1024),
                    weightFactor: function (size) {
                        return Math.pow(size, 2.3) * width / 1024;
                    },
                    fontFamily: 'system-ui, -apple-system, "Segoe UI", "Roboto", sans-serif',
                    color: function (word, weight) {
                        return weight > 8 ? '#f44336' : weight > 6 ? '#ff9800' : weight > 4 ? '#2196f3' : weight > 2 ? '#4caf50' : '#9e9e9e';
                    },
                    rotateRatio: 0.5,
                    rotationSteps: 2,
                    backgroundColor: 'transparent'
                });
            }
        }
        
        // Fetch words
        function fetchWords() {
            fetch(answersEndpoint)
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        renderWords(d.answers);
                    }
                });
        }
        
        fetchWords();
        setInterval(fetchWords, 10000);
    }
    
    // Chart initialization
    function initChart() {
        const chartCtx = document.getElementById('chart-container').getContext('2d');
        const chartType = itemType.replace('_bar_chart', 'Bar').replace('vertical', 'bar').replace('horizontal', 'bar');
        let myChart = null;
        
        function renderAnswers(arr) {
            // Update list display
            listEl.innerHTML = '';
            arr.forEach(a => {
                const li = document.createElement('li');
                li.className = 'answer-list-item';
                li.innerHTML = `<span class="answer-value">${a.answer}</span><span>${a.votes || 1}</span>`;
                listEl.appendChild(li);
            });
            
            // Update chart
            const labels = arr.map(a => a.answer);
            const dataValues = arr.map(a => a.votes || 1);
            if (!myChart) {
                const config = {
                    type: getChartJsType(itemType),
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Votes',
                            data: dataValues,
                            backgroundColor: generateColors(labels.length)
                        }]
                    },
                    options: {
                        indexAxis: chartType === 'bar' && itemType === 'horizontal_bar_chart' ? 'y' : 'x',
                        animation: false
                    }
                };
                myChart = new Chart(chartCtx, config);
            } else {
                myChart.data.labels = labels;
                myChart.data.datasets[0].data = dataValues;
                myChart.update();
            }
        }
        
        // Fetch answers
        function fetchAnswers() {
            fetch(answersEndpoint)
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        renderAnswers(d.answers);
                    }
                });
        }
        
        // Fullscreen handling
        const chartCanvas = document.getElementById('chart-container');
        let isFullscreen = false;
        
        chartCanvas.addEventListener('click', function() {
            if (!isFullscreen) {
                if (chartCanvas.requestFullscreen) {
                    chartCanvas.requestFullscreen();
                } else if (chartCanvas.webkitRequestFullscreen) {
                    chartCanvas.webkitRequestFullscreen();
                } else if (chartCanvas.mozRequestFullScreen) {
                    chartCanvas.mozRequestFullScreen();
                } else if (chartCanvas.msRequestFullscreen) {
                    chartCanvas.msRequestFullscreen();
                }
            }
        });
        
        document.addEventListener('fullscreenchange', handleFsChange);
        document.addEventListener('webkitfullscreenchange', handleFsChange);
        document.addEventListener('mozfullscreenchange', handleFsChange);
        document.addEventListener('MSFullscreenChange', handleFsChange);
        
        function handleFsChange() {
            const fsElem = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement;
            isFullscreen = !!fsElem;
            if (isFullscreen && overlayHelper && typeof overlayHelper.activate === 'function') {
                overlayHelper.activate();
                chartCanvas.style.transform = 'scale(1)';
            } else {
                if (overlayHelper && typeof overlayHelper.deactivate === 'function') overlayHelper.deactivate();
                chartCanvas.style.transform = 'scale(0.7)';
            }
        }
        
        fetchAnswers();
        setInterval(fetchAnswers, 10000);
    }
    
    // Common form handling
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const val = input.value.trim();
        if (!val) return;
        
        const fd = new FormData();
        fd.append('value', val);
        
        fetch(addEndpoint, {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                input.value = '';
                // Refresh the data
                if (itemType === 'wordcloud') {
                    fetchWords();
                } else {
                    fetchAnswers();
                }
            }
        });
    });
    
    // Utility functions
    function getChartJsType(itemType) {
        switch(itemType) {
            case 'vertical_bar_chart': return 'bar';
            case 'horizontal_bar_chart': return 'bar';
            case 'pie_chart': return 'pie';
            case 'doughnut': return 'doughnut';
            default: return 'bar';
        }
    }
    
    function generateColors(n) {
        const colors = [];
        for (let i = 0; i < n; i++) {
            const hue = i * 360 / n;
            colors.push(`hsl(${hue},70%,50%)`);
        }
        return colors;
    }
})();
</script>

<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
