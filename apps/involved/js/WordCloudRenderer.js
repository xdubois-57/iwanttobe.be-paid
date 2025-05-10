// WordCloudRenderer.js
// Wraps wordcloud2.js usage for a simple update interface.
class WordCloudRenderer {
    constructor(container, options = {}) {
        this.container = container;
        this.options = Object.assign({
            weightFactor: 4,
            gridSize: 8,
            backgroundColor: 'transparent',
            color: '#1f2937',
            rotateRatio: 0,
            shrinkToFit: true,
            origin: [container.clientWidth / 2, container.clientHeight / 2]
        }, options);

        this.canvas = document.createElement('canvas');
        this.canvas.width = container.clientWidth;
        this.canvas.height = container.clientHeight;
        this.canvas.style.width = '100%';
        this.canvas.style.height = '100%';
        container.appendChild(this.canvas);
    }

    update(wordsArray) {
        if (!window.WordCloud) {
            console.error('[WordCloudRenderer] WordCloud2 library is missing');
            return;
        }

        const list = wordsArray.map(({ word, count }) => [word, count]);
        const opts = Object.assign({}, this.options, { list, clearCanvas: true, gridSize: Math.max(8, Math.floor(this.canvas.width / 50)) });
        WordCloud(this.canvas, opts);
    }
}

window.WordCloudRenderer = WordCloudRenderer;
