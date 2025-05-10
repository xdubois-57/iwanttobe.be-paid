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
        
        if (!Array.isArray(wordsArray) || wordsArray.length === 0) {
            console.warn('[WordCloudRenderer] Empty or invalid word array');
            return;
        }

        // Handle different property name formats
        // Convert to [word, weight] pairs, accommodating different naming conventions
        const list = wordsArray.map(item => {
            // Find the text value (could be in word, text, value, or answer property)
            const text = item.word || item.text || item.value || item.answer || '';
            // Find the weight value (could be in count, weight, votes property)
            const weight = Number(item.count || item.weight || item.votes || 1);
            return [text, weight];
        });
        
        // Filter out any invalid items
        const validList = list.filter(([text, weight]) => 
            text && typeof text === 'string' && text.trim() !== '' && !isNaN(weight));
            
        if (validList.length === 0) {
            console.warn('[WordCloudRenderer] No valid words found after processing');
            return;
        }
            
        const opts = Object.assign({}, this.options, { 
            list: validList, 
            clearCanvas: true, 
            gridSize: Math.max(8, Math.floor(this.canvas.width / 50)) 
        });
        WordCloud(this.canvas, opts);
    }
}

window.WordCloudRenderer = WordCloudRenderer;
