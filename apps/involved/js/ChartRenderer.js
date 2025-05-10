// ChartRenderer.js
// Uses Chart.js for bar/pie/doughnut charts.
class ChartRenderer {
    /**
     * @param {HTMLElement} container 
     * @param {string} chartType - 'bar_chart' | 'pie_chart' | 'doughnut_chart'
     */
    constructor(container, chartType) {
        this.container = container;
        this.chartType = chartType;

        this.canvas = document.createElement('canvas');
        container.appendChild(this.canvas);
        this.resizeCanvasToParent();
        // Observe parent resize
        if (window.ResizeObserver) {
            this.resizeObserver = new ResizeObserver(() => this.resizeCanvasToParent());
            this.resizeObserver.observe(container);
        } else {
            window.addEventListener('resize', () => this.resizeCanvasToParent());
        }

        if (!window.Chart) {
            console.error('[ChartRenderer] Chart.js not loaded');
            return;
        }

        /** @type {Chart} */
        this.chart = new Chart(this.canvas.getContext('2d'), {
            type: this.mapChartType(chartType),
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: this.generateColors(20)
                }]
            },
            options: {
                animation: {
                    duration: 300
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    mapChartType(type) {
        switch (type) {
            case 'bar_chart':
            case 'horizontal_bar_chart':
            case 'vertical_bar_chart':
                return 'bar';
            case 'pie_chart':
                return 'pie';
            case 'doughnut_chart':
            case 'doughnut':
                return 'doughnut';
            default:
                console.warn('[ChartRenderer] Unknown chart type:', type, 'defaulting to bar');
                return 'bar';
        }
    }

    generateColors(count) {
        const colors = [];
        for (let i = 0; i < count; i++) {
            const hue = Math.floor((360 / count) * i);
            colors.push(`hsl(${hue}, 70%, 50%)`);
        }
        return colors;
    }

    update(wordsArray) {
        const labels = wordsArray.map(w => w.word);
        const data = wordsArray.map(w => w.count);

        this.chart.data.labels = labels;
        this.chart.data.datasets[0].data = data;
        this.chart.update();
    }
    resizeCanvasToParent() {
        const parent = this.canvas.parentElement;
        if (!parent) return;
        const w = parent.clientWidth;
        const h = parent.clientHeight;
        this.canvas.width = w;
        this.canvas.height = h;
        this.canvas.style.width = w + 'px';
        this.canvas.style.height = h + 'px';
    }
}

window.ChartRenderer = ChartRenderer;
