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

        this.lastPresenceCount = 0;
        this.observeFutureAnimations();

        this.renderer = this.createRenderer();
        this.previousWordsHash = null;

        this.startPolling();

        // Robust fallback: after DOMContentLoaded, repeatedly check for .future-animations
        let tries = 0;
        const tryUpdatePresence = () => {
            const fa = this.container.querySelector('.future-animations');
            if (fa) {
                this.updatePresenceDisplay(this.lastPresenceCount || 0);
            } else if (tries < 20) {
                tries++;
                setTimeout(tryUpdatePresence, 200);
            }
        };
        window.addEventListener('DOMContentLoaded', tryUpdatePresence);
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

        // QR - create a URL if it doesn't exist in data
        if (!this.qrBlock) {
            const url = data.active_url || `/${this.lang}/involved/${this.eventCode}/event`;
            console.debug('[QrDebug] Creating QR with URL:', url);
            this.renderQr(url);
        }
    }

    observeFutureAnimations() {
        const observer = new MutationObserver(() => {
            const futureAnimations = this.container.querySelector('.future-animations');
            if (futureAnimations) {
                this.updatePresenceDisplay(this.lastPresenceCount || 0);
                observer.disconnect();
            }
        });
        observer.observe(this.container, { childList: true, subtree: true });
    }

    updatePresenceDisplay(count) {
        this.lastPresenceCount = count;
        let el = document.getElementById('presence-count-display');
        
        // Create the future-animations element if it doesn't exist
        if (!this.container.querySelector('.future-animations')) {
            console.debug('[PresenceDebug] Creating .future-animations element');
            const futureAnimationsEl = document.createElement('div');
            futureAnimationsEl.className = 'future-animations';
            
            // Make it take the full size of its parent
            futureAnimationsEl.style.position = 'relative';
            futureAnimationsEl.style.width = '100%';
            futureAnimationsEl.style.height = '100%';
            
            // Move existing canvas into future-animations (if it exists)
            const canvas = this.container.querySelector('canvas');
            if (canvas) {
                console.debug('[PresenceDebug] Moving canvas into .future-animations');
                futureAnimationsEl.appendChild(canvas);
            }
            
            // Add future-animations to the container
            this.container.appendChild(futureAnimationsEl);
        }
        
        const futureAnimations = this.container.querySelector('.future-animations');
        console.debug('[PresenceDebug] .future-animations found?', !!futureAnimations, futureAnimations);
        if (futureAnimations) {
            // Ensure future-animations is positioned relatively
            if (getComputedStyle(futureAnimations).position === 'static') {
                futureAnimations.style.position = 'relative';
            }
            // Move or create the indicator inside future-animations
            if (!el || el.parentNode !== futureAnimations) {
                if (el && el.parentNode) el.parentNode.removeChild(el);
                el = document.createElement('div');
                el.id = 'presence-count-display';
                futureAnimations.appendChild(el);
                console.debug('[PresenceDebug] Appended indicator to future-animations', el);
            }
            el.className = 'presence-inside-future-animations';
            console.debug('[PresenceDebug] Set class to presence-inside-future-animations', el);
            el.style.position = 'absolute';
            el.style.top = '0';
            el.style.right = '0';
            el.style.left = 'auto';
        } else {
            // fallback: top right of container
            if (!el || el.parentNode !== this.container) {
                if (el && el.parentNode) el.parentNode.removeChild(el);
                el = document.createElement('div');
                el.id = 'presence-count-display';
                this.container.appendChild(el);
                console.debug('[PresenceDebug] Appended indicator to container', el);
            }
            el.className = '';
            console.debug('[PresenceDebug] Set class to empty', el);
            el.style.position = 'absolute';
            el.style.top = '10px';
            el.style.right = '10px';
            el.style.left = 'auto';
        }
        // Shared styles
        el.style.fontSize = '1.5rem';
        el.style.zIndex = '30000';
        el.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
        el.style.padding = '8px 12px';
        el.style.borderRadius = '4px';
        el.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
        el.textContent = `👤 ${count}`;
        
        // Document-wide debug of .future-animations
        console.debug('[PresenceDebug] Document-wide .future-animations search:', document.querySelectorAll('.future-animations'));
        console.debug('[PresenceDebug] Similar elements search:', document.querySelectorAll('[class*="future"]'));
        console.debug('[PresenceDebug] Container children:', this.container.children);
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
        let qrWrapper = document.getElementById('event-qr-wrapper');
        
        // Find or create the future-animations element
        let futureAnimations = this.container.querySelector('.future-animations');
        if (!futureAnimations) {
            console.debug('[QrDebug] Creating .future-animations element for QR');
            futureAnimations = document.createElement('div');
            futureAnimations.className = 'future-animations';
            
            // Make it take the full size of its parent
            futureAnimations.style.position = 'relative';
            futureAnimations.style.width = '100%';
            futureAnimations.style.height = '100%';
            
            // Add future-animations to the container
            this.container.appendChild(futureAnimations);
        }
        
        // Ensure future-animations is positioned relatively
        if (getComputedStyle(futureAnimations).position === 'static') {
            futureAnimations.style.position = 'relative';
        }
        
        // Move or create the QR wrapper inside future-animations
        if (!qrWrapper || qrWrapper.parentNode !== futureAnimations) {
            if (qrWrapper && qrWrapper.parentNode) qrWrapper.parentNode.removeChild(qrWrapper);
            qrWrapper = document.createElement('div');
            qrWrapper.id = 'event-qr-wrapper';
            futureAnimations.appendChild(qrWrapper);
            console.debug('[QrDebug] Appended QR wrapper to future-animations', qrWrapper);
        }
        
        // Set position styles
        qrWrapper.style.position = 'absolute';
        qrWrapper.style.bottom = '20px';
        qrWrapper.style.right = '20px';
        qrWrapper.style.maxWidth = '180px';
        qrWrapper.style.zIndex = '2000';

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

// Global debug timeout - runs after page load
setTimeout(() => {
    console.debug('[GLOBAL] .future-animations exists?', document.querySelectorAll('.future-animations').length > 0);
    document.querySelectorAll('.future-animations').forEach(el => {
        console.debug('[GLOBAL] Found .future-animations:', el);
        console.debug('[GLOBAL] Parent chain:', getParentChain(el));
    });
    
    // Helper to show parent chain
    function getParentChain(el) {
        const chain = [];
        let current = el;
        while (current) {
            chain.push({
                tag: current.tagName,
                id: current.id,
                classes: current.className
            });
            current = current.parentElement;
        }
        return chain;
    }
    
    // Check for similarly named elements
    document.querySelectorAll('[class*="future"]').forEach(el => {
        console.debug('[GLOBAL] Found element with "future" in class:', el.className, el);
    });
}, 1000);
