// EventItemDisplayManager.js
// Central manager for displaying event items, presence, emojis and QR.
// Requires EventQrBlock, WordCloudRenderer, ChartRenderer, and WordCloud2.js / Chart.js libraries to be loaded.

class EventItemDisplayManager {
    /**
     * @param {string|HTMLElement} containerSelector - Selector or element in which to render.
     * @param {{eventCode: string, eventItemId: number, itemType: string, pollInterval?: number}} options
     */
    constructor(containerSelector, options) {
        const {
            eventCode,
            eventItemId,
            itemType,
            pollInterval = 5000
        } = options;

        if (!eventCode || !eventItemId) {
            console.error('[EventItemDisplayManager] eventCode and eventItemId are required');
            return;
        }

        this.container = (typeof containerSelector === 'string') ? document.querySelector(containerSelector) : containerSelector;
        if (!this.container) {
            console.error('[EventItemDisplayManager] Container not found:', containerSelector);
            return;
        }

        this.eventCode = eventCode;
        this.eventItemId = eventItemId;
        this.itemType = itemType;
        this.pollInterval = pollInterval;
        this.lang = document.documentElement.getAttribute('lang') || 'en';

        this.renderer = this.createRenderer();
        this.previousWordsHash = null;

        this.startPolling();
    }

    createRenderer() {
        switch (this.itemType) {
            case 'wordcloud':
                if (typeof WordCloudRenderer !== 'undefined') {
                    return new WordCloudRenderer(this.container);
                }
                break;
            case 'bar_chart':
            case 'pie_chart':
            case 'doughnut_chart':
                if (typeof ChartRenderer !== 'undefined') {
                    return new ChartRenderer(this.container, this.itemType);
                }
                break;
            default:
                console.warn('[EventItemDisplayManager] Unknown itemType, defaulting to WordCloud:', this.itemType);
                if (typeof WordCloudRenderer !== 'undefined') {
                    return new WordCloudRenderer(this.container);
                }
        }
        return null;
    }

    startPolling() {
        this.poll();
        this.intervalId = setInterval(() => this.poll(), this.pollInterval);
    }

    stopPolling() {
        if (this.intervalId) clearInterval(this.intervalId);
    }

    async poll() {
        try {
            const res = await fetch(`/${this.lang}/involved/${this.eventCode}/eventitem/${this.eventItemId}/data`);
            const data = await res.json();
            if (!data.success) {
                console.error('[EventItemDisplayManager] Data fetch failed:', data.error);
                return;
            }

            this.handleData(data);
        } catch (e) {
            console.error('[EventItemDisplayManager] Poll error:', e);
        }
    }

    handleData(data) {
        if (!data || typeof data !== 'object') {
            console.error('[EventItemDisplayManager] Invalid data received:', data);
            return;
        }
        
        // Presence
        this.updatePresenceDisplay(data.presence || 0);

        // Emojis
        if (Array.isArray(data.emojis) && data.emojis.length) {
            this.animateEmojis(data.emojis);
        }

        // Words handling - now more robust
        try {
            // Handle potential structures: {words: [...]} or {answers: [...]} or direct array
            const wordsArray = data.words || data.answers || [];
            
            if (Array.isArray(wordsArray)) {
                // Only update if we have renderer and data has changed
                if (this.renderer && typeof this.renderer.update === 'function') {
                    const hash = JSON.stringify(wordsArray);
                    if (hash !== this.previousWordsHash) {
                        this.previousWordsHash = hash;
                        this.renderer.update(wordsArray);
                    }
                }
            } else {
                console.warn('[EventItemDisplayManager] Invalid words data format:', wordsArray);
            }
        } catch (e) {
            console.error('[EventItemDisplayManager] Error processing words data:', e);
        }

        // QR
        if (!this.qrBlock && data.active_url) {
            this.renderQr(data.active_url);
        }
    }

    updatePresenceDisplay(count) {
        let el = this.container.querySelector('#presence-count-display');
        if (!el) {
            el = document.createElement('div');
            el.id = 'presence-count-display';
            el.style.position = 'absolute';
            el.style.top = '10px';
            el.style.right = '10px';
            el.style.fontSize = '1.5rem';
            el.style.zIndex = '3';
            this.container.appendChild(el);
        }
        el.textContent = `👤 ${count}`;
    }

    // Simple emoji floating animation
    animateEmojis(list) {
        list.forEach((emoji) => {
            const span = document.createElement('span');
            span.textContent = emoji;
            span.style.position = 'absolute';
            span.style.left = '0';
            span.style.bottom = '0';
            span.style.fontSize = '2.2rem';
            span.style.pointerEvents = 'none';
            this.container.appendChild(span);

            const targetX = Math.random() * this.container.clientWidth;
            const targetY = this.container.clientHeight + 50; // go up beyond top
            const duration = 4000 + Math.random() * 2000;

            span.animate([
                { transform: 'translate(0,0)', opacity: 1 },
                { transform: `translate(${targetX}px,-${targetY}px)`, opacity: 0 }
            ], {
                duration,
                easing: 'ease-out',
                fill: 'forwards'
            });

            setTimeout(() => span.remove(), duration);
        });
    }

    renderQr(url) {
        const qrWrapper = document.createElement('div');
        qrWrapper.style.position = 'absolute';
        qrWrapper.style.bottom = '20px';
        qrWrapper.style.right = '20px';
        qrWrapper.style.maxWidth = '200px';
        qrWrapper.style.zIndex = '2';
        this.container.appendChild(qrWrapper);

        // EventQrBlock comes from eventQrBlock.js
        if (typeof EventQrBlock !== 'undefined') {
            this.qrBlock = new EventQrBlock(qrWrapper, url, this.eventCode);
        } else {
            // Fallback simple QR SVG fetch
            qrWrapper.innerHTML = `<img src="/qr/svg?data=${encodeURIComponent(url)}" style="width:100%;height:auto;" alt="QR">`;
        }
    }
}

window.EventItemDisplayManager = EventItemDisplayManager;
