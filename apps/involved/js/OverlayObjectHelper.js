class OverlayObjectHelper {
    constructor() {
        this.active = false;
        this.overlay = null;
        this.title = null;
        this.emojiCount = 0;
        this.previousEmojiCount = 0;
        this.currentUrl = null;
        this.pollingInterval = null;
        this.presenceInterval = null;
        this.animationQueue = [];
        this.qrContainer = null;
        this.qrData = null;
        this.animationInProgress = false;
        this.pollingFrequency = 7500; // Poll every 7.5 seconds (4 times more often)
        this.qrBlockConfig = null; // New property to store QR block configuration
        this.emojiInterval = null; // New property to store emoji polling interval
        
        // Initialize the helper when created
        this.initialize();
    }

    initialize() {
        // Initialize presence polling
        this.startPresencePolling();
        
        // Initialize fullscreen QR block handling
        this.initializeFullscreenQR();
        
        // Initialize presence counter
        this.initializePresenceCounter();
    }

    initializePresenceCounter() {
        // Create presence counter element if it doesn't exist
        const presenceElement = document.getElementById('presence-count-display');
        if (!presenceElement) {
            // Create presence counter in top right, aligned with title
            const presenceContainer = document.createElement('div');
            presenceContainer.style.position = 'absolute';
            presenceContainer.style.display = 'flex';
            presenceContainer.style.justifyContent = 'center';
            presenceContainer.style.alignItems = 'center';
            presenceContainer.style.top = '40px'; // Same top position as title
            presenceContainer.style.right = '20px'; // Right side of screen
            presenceContainer.style.zIndex = '11001';
            
            // Presence count
            const presenceElement = document.createElement('div');
            presenceElement.id = 'presence-count-display';
            presenceElement.innerHTML = `👤 0`;
            presenceElement.style.fontSize = '1.5rem';
            
            // Add to container
            presenceContainer.appendChild(presenceElement);
            
            // Add container to overlay if it exists
            if (this.overlay) {
                this.overlay.appendChild(presenceContainer);
            }
        }
        
        // Start presence polling if not already started
        if (!this.presenceInterval) {
            this.startPresencePolling();
        }
    }

    activate() {
        if (this.active) return;
        this.active = true;
        this.overlay = document.createElement('div');
        this.overlay.style.position = 'fixed';
        this.overlay.style.top = '0';
        this.overlay.style.left = '0';
        this.overlay.style.width = '100vw';
        this.overlay.style.height = '100vh';
        this.overlay.style.zIndex = '11000';
        this.overlay.style.background = 'transparent';
        this.overlay.style.pointerEvents = 'none';
        this.overlay.id = 'overlay-object-helper';
        document.body.appendChild(this.overlay);
        
        // Create QR container
        this.qrContainer = document.createElement('div');
        this.qrContainer.style.position = 'absolute';
        this.qrContainer.style.bottom = '20px';
        this.qrContainer.style.right = '20px';
        this.qrContainer.style.zIndex = '11001';
        this.qrContainer.style.background = 'var(--background-primary-translucent, rgba(255, 255, 255, 0.9))';
        this.qrContainer.style.padding = '18px';
        this.qrContainer.style.borderRadius = '8px';
        this.qrContainer.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.2)';
        this.qrContainer.style.maxWidth = '360px';
        this.qrContainer.style.display = 'none';
        this.qrContainer.id = 'overlay-qr-container';
        this.overlay.appendChild(this.qrContainer);
        
        // Get the title from the h1 element
        const h1Element = document.querySelector('h1');
        if (h1Element) {
            this.title = h1Element.textContent.trim();
        }
        
        this.render();
        
        // Calculate current URL for emoji tracking
        this.calculateCurrentUrl();
        
        // Start emoji polling
        this.startEmojiPolling();
        
        console.log('[OverlayObjectHelper] Activated:', { 
            title: this.title,
            url: this.currentUrl
        });
    }

    /**
     * Deactivate the overlay helper
     * Removes the overlay from the DOM and cleans up resources
     */
    deactivate() {
        console.log('[OverlayObjectHelper] Deactivating overlay');
        
        // Clean up overlay
        if (this.overlay && this.overlay.parentNode) {
            this.overlay.parentNode.removeChild(this.overlay);
            this.overlay = null;
        }
        
        // Stop polling intervals
        this.stopEmojiPolling();
        
        // Reset state
        this.active = false;
        
        console.log('[OverlayObjectHelper] Overlay deactivated');
    }

    addEmoji() {
        // Store previous count for animation
        this.previousEmojiCount = this.emojiCount;
        this.emojiCount++;
        
        // Check for increase in emojis to trigger animation
        const difference = this.emojiCount - this.previousEmojiCount;
        if (difference > 0) {
            for (let i = 0; i < difference; i++) {
                this.animateEmoji();
            }
        }
        
        this.render();
    }

    /**
     * Set the emoji count and trigger animations if needed
     * @param {number} count - The new emoji count
     */
    setEmojiCount(count) {
        if (typeof count !== 'number' || isNaN(count)) {
            console.warn('[OverlayObjectHelper] Invalid emoji count provided:', count);
            return;
        }
        
        // Store previous count for animation
        this.previousEmojiCount = this.emojiCount;
        this.emojiCount = count;
        
        // Check for increase in emojis to trigger animation
        const difference = this.emojiCount - this.previousEmojiCount;
        if (difference > 0) {
            for (let i = 0; i < difference; i++) {
                this.animateEmoji();
            }
        }
        
        this.render();
        console.log(`[OverlayObjectHelper] Emoji count updated to: ${count}`);
    }

    isDarkMode() {
        // Check if the browser/OS prefers dark mode
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        // Check for HTML data-theme attribute
        const htmlTheme = document.documentElement.getAttribute('data-theme');
        
        // If HTML explicitly sets theme, use that
        if (htmlTheme) {
            return htmlTheme === 'dark';
        }
        
        // Otherwise use browser/OS preference
        return prefersDark;
    }

    initializeFullscreenQR() {
        document.addEventListener('fullscreenchange', () => {
            if (document.fullscreenElement) {
                // When entering fullscreen, ensure overlay is visible
                this.overlay.style.display = 'block';
                this.overlay.style.zIndex = '2147483647'; // Maximum possible z-index
                this.showQrBlock();
            } else {
                this.hideQrBlock();
                // When exiting fullscreen, reset overlay display
                this.overlay.style.display = 'none';
            }
        });
    }

    calculateCurrentUrl() {
        // Get the current URL without hash or query parameters for consistency
        const urlObj = new URL(window.location.href);
        urlObj.hash = '';
        urlObj.search = '';
        this.currentUrl = urlObj.toString();
        
        // Check if we're on a wordcloud page
        const pathSegments = urlObj.pathname.split('/');
        // URL format: /lang/involved/key/wordcloud/wcid
        if (pathSegments.length >= 6 && pathSegments[2] === 'involved' && pathSegments[4] === 'wordcloud') {
            // We're on a wordcloud page, normalize the URL to always track likes for the main wordcloud page
            const baseUrl = urlObj.origin;
            const lang = pathSegments[1];
            const eventKey = pathSegments[3];
            const wcid = pathSegments[5];
            
            // Construct the main wordcloud URL for emoji tracking
            // Remove '/add' or any other extra paths to ensure consistent tracking
            this.currentUrl = `${baseUrl}/${lang}/involved/${eventKey}/wordcloud/${wcid}`;
            console.log('[OverlayObjectHelper] Normalized URL for emoji tracking:', this.currentUrl);
        }
    }

    startPresencePolling() {
        // Poll every 10 seconds
        this.presenceInterval = setInterval(() => {
            this.fetchPresence();
        }, 10000);
        
        // Also fetch immediately
        this.fetchPresence();
    }

    fetchPresence() {
        if (!this.currentUrl) {
            this.calculateCurrentUrl();
        }
        
        // Make sure we have a valid URL before proceeding
        if (!this.currentUrl) {
            console.warn('[OverlayObjectHelper] Cannot fetch presence: No current URL available');
            return;
        }
        
        console.log('[OverlayObjectHelper] Fetching presence for URL:', this.currentUrl);
        
        fetch('/ajax/presence?url=' + encodeURIComponent(this.currentUrl))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    console.log('[OverlayObjectHelper] Presence update raw data:', JSON.stringify(data));
                    const presenceElement = document.getElementById('presence-count-display');
                    if (presenceElement) {
                        presenceElement.innerHTML = `👤 ${data.count}`;
                    }
                } else {
                    console.warn('[OverlayObjectHelper] Presence update failed:', data.error || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('[OverlayObjectHelper] Error fetching presence:', error);
            });
    }

    startEmojiPolling() {
        if (this.emojiInterval) {
            clearInterval(this.emojiInterval);
        }
        // Poll every 5 seconds
        this.emojiInterval = setInterval(() => this.fetchEmojis(), 5000);
        // immediate first call
        this.fetchEmojis();
    }

    stopEmojiPolling() {
        if (this.emojiInterval) {
            clearInterval(this.emojiInterval);
            this.emojiInterval = null;
        }
    }

    fetchEmojis() {
        if (!this.currentUrl) {
            this.calculateCurrentUrl();
        }
        fetch('/ajax/emoji?url=' + encodeURIComponent(this.currentUrl) + '&max=15')
            .then(r => r.json())
            .then(data => {
                if (data.success && Array.isArray(data.emojis) && data.emojis.length > 0) {
                    // Log that we received emojis for debugging
                    console.log('[OverlayObjectHelper] Received emojis:', data.emojis);
                    // Distribute animations randomly over the next 4 seconds (first emoji: no delay)
                    data.emojis.forEach((emoji, idx) => {
                        let delay = (idx === 0) ? 0 : Math.random() * 4000; // 0-4000ms for all but first
                        setTimeout(() => {
                            this.animateEmoji(emoji);
                        }, delay);
                        console.log(`[OverlayObjectHelper] Scheduled emoji ${emoji} to animate in ${delay.toFixed(0)}ms`);
                    });
                } else {
                    console.log('[OverlayObjectHelper] No new emojis to show');
                }
            })
            .catch(err => console.error('[OverlayObjectHelper] fetchEmojis error', err));
    }

    animateEmoji(emoji) {
        if (!this.overlay) return;
        
        console.log('[OverlayObjectHelper] Animating emoji:', emoji);
        
        const el = document.createElement('div');
        el.textContent = emoji;
        el.style.position = 'absolute';
        el.style.left = '20px';
        el.style.bottom = '0px';
        el.style.fontSize = '2.5rem';
        el.style.zIndex = '11002';
        el.style.transition = 'transform 3s linear, opacity 3s linear';
        // random horizontal destination at top
        const endX = Math.random() * (window.innerWidth - 40);
        const translate = `translate(${endX}px, -${window.innerHeight + 100}px)`;
        this.overlay.appendChild(el);
        // force reflow then animate
        requestAnimationFrame(() => {
            el.style.transform = translate;
            el.style.opacity = '0';
        });
        el.addEventListener('transitionend', () => {
            if (el.parentNode) el.parentNode.removeChild(el);
        });
    }

    render() {
        if (!this.overlay) return;
        
        console.log('[OverlayObjectHelper] Rendering overlay:', { 
            active: this.active, 
            title: this.title, 
            emojiCount: this.emojiCount,
            overlay: this.overlay ? 'created' : 'null'
        });
        
        // Clear main content (keeping QR container)
        const contentElements = Array.from(this.overlay.children).filter(el => el !== this.qrContainer);
        contentElements.forEach(el => this.overlay.removeChild(el));
        
        // Create title element
        if (this.title) {
            console.log('[OverlayObjectHelper] Rendering title:', this.title);
            const titleDiv = document.createElement('div');
            titleDiv.textContent = this.title;
            titleDiv.style.position = 'absolute';
            titleDiv.style.top = '40px';
            titleDiv.style.left = '50%';
            titleDiv.style.transform = 'translateX(-50%)';
            titleDiv.style.fontSize = '2rem';
            titleDiv.style.fontWeight = 'bold';
            titleDiv.style.padding = '8px 16px';
            titleDiv.style.textAlign = 'center';
            titleDiv.style.maxWidth = '70%';
            titleDiv.style.zIndex = '2147483647'; // Maximum z-index
            
            // Detect dark mode and apply appropriate styles
            if (this.isDarkMode()) {
                titleDiv.style.color = 'var(--text-primary, #ffffff)';
            } else {
                titleDiv.style.color = '#000000';
            }
            
            this.overlay.appendChild(titleDiv);
        } else {
            console.warn('[OverlayObjectHelper] No title to render');
        }
        
        // Add instruction message below title
        const instructionDiv = document.createElement('div');
        let instructionText = 'Scan the QR code to answer';
        if (window.lang && typeof window.lang.translate === 'function') {
            instructionText = window.lang.translate('scan_qr_to_answer');
        }
        instructionDiv.textContent = instructionText;
        instructionDiv.style.position = 'absolute';
        instructionDiv.style.top = '100px';
        instructionDiv.style.left = '50%';
        instructionDiv.style.transform = 'translateX(-50%)';
        instructionDiv.style.fontSize = '1.25rem';
        instructionDiv.style.padding = '8px 16px';
        instructionDiv.style.textAlign = 'center';
        instructionDiv.style.maxWidth = '70%';
        instructionDiv.style.zIndex = '2147483647';
        
        // Detect dark mode and apply appropriate styles
        if (this.isDarkMode()) {
            instructionDiv.style.color = 'var(--text-secondary, #cccccc)';
        } else {
            instructionDiv.style.color = '#555555';
        }
        
        this.overlay.appendChild(instructionDiv);
        
        // Initialize presence counter
        this.initializePresenceCounter();
    }

    sendEmoji() {
        // backward compatibility – send default 👍 emoji
        if (!window.OverlayClientHelper || typeof window.OverlayClientHelper.sendEmoji !== 'function') {
            console.warn('[OverlayObjectHelper] OverlayClientHelper.sendEmoji missing');
            return Promise.resolve();
        }
        return window.OverlayClientHelper.sendEmoji('👍');
    }

    cleanup() {
        if (this.overlay) {
            // Remove overlay from DOM
            if (this.overlay.parentNode) {
                this.overlay.parentNode.removeChild(this.overlay);
            }
            this.overlay = null;
        }
        
        // Clear all intervals
        this.stopEmojiPolling();
        
        // Clear animation queue
        this.animationQueue = [];
        this.animationInProgress = false;
    }

    /**
     * Set the title for the overlay
     * @param {string} newTitle - The title text to display
     */
    addTitle(newTitle) {
        if (typeof newTitle === 'string' && newTitle.trim() !== '') {
            this.title = newTitle.trim();
            console.log('[OverlayObjectHelper] Title set to:', this.title);
            this.render();
        } else {
            console.warn('[OverlayObjectHelper] Invalid title provided:', newTitle);
        }
    }

    /**
     * Shows the QR code block in the overlay
     */
    showQrBlock() {
        if (!this.qrContainer) {
            console.warn('[OverlayObjectHelper] Cannot show QR block: container is missing');
            return;
        }

        // Clear previous content if any
        this.qrContainer.innerHTML = '';
        
        // Use configured QR data if available, or extract from current URL as fallback
        let url, eventCode, eventPassword, additionalText, showShareButton;
        
        if (this.qrBlockConfig) {
            // Use configured data
            ({ url, eventCode, eventPassword, additionalText, showShareButton } = this.qrBlockConfig);
            console.log('[OverlayObjectHelper] Using configured QR data:', { 
                eventCode, 
                url: url.substring(0, 50) + (url.length > 50 ? '...' : '') 
            });
        } else {
            // Extract data from URL as fallback
            if (!this.currentUrl) {
                console.warn('[OverlayObjectHelper] Cannot show QR block: URL is missing');
                return;
            }
            
            url = this.currentUrl;
            const urlObj = new URL(url);
            const pathSegments = urlObj.pathname.split('/');
            
            // URL format: /lang/involved/key/wordcloud/wcid
            if (pathSegments.length >= 4 && pathSegments[2] === 'involved') {
                eventCode = pathSegments[3];
                // No password available from URL
                eventPassword = null;
                additionalText = '';
                showShareButton = true;
            } else {
                console.warn('[OverlayObjectHelper] Not on an event page, cannot show QR');
                this.qrContainer.style.display = 'none';
                return;
            }
        }
        
        // Show the container
        this.qrContainer.style.display = 'block';
        
        // Create the EventQrBlock to render inside the container
        if (typeof EventQrBlock === 'function') {
            // Create new EventQrBlock with all parameters
            console.log('[OverlayObjectHelper] Creating EventQrBlock with code:', eventCode);
            this.qrData = new EventQrBlock(
                this.qrContainer,
                url,
                eventCode,
                eventPassword,
                additionalText || '',
                showShareButton !== false
            );
        } else {
            console.error('[OverlayObjectHelper] EventQrBlock class not found');
            // Fallback to basic QR display if EventQrBlock is not available
            this.qrContainer.innerHTML = `
                <div style="text-align: center;">
                    <div style="margin-bottom: 10px;">Scan to join:</div>
                    <div id="qr-placeholder" style="width:150px;height:150px;background:#eee;margin:0 auto;display:flex;align-items:center;justify-content:center;">
                        Loading QR...
                    </div>
                    <div style="margin-top: 10px;">Event code: ${eventCode}</div>
                    ${eventPassword ? `<div style="margin-top: 5px;">Password: ${eventPassword}</div>` : ''}
                </div>
            `;
            
            // Try to fetch a QR code using the generic QR endpoint
            fetch('/qr/svg?data=' + encodeURIComponent(url))
                .then(res => res.text())
                .then(svg => {
                    const placeholder = this.qrContainer.querySelector('#qr-placeholder');
                    if (placeholder) {
                        placeholder.innerHTML = svg;
                    }
                })
                .catch(err => {
                    console.error('[OverlayObjectHelper] Failed to fetch QR:', err);
                });
        }
    }

    /**
     * Hides the QR code block
     */
    hideQrBlock() {
        if (this.qrContainer) {
            this.qrContainer.style.display = 'none';
            // Clear any EventQrBlock instance
            this.qrData = null;
        }
    }

    /**
     * Sets QR data parameters for the overlay QR block
     * @param {string} url - URL to encode in the QR code
     * @param {string} eventCode - Event code to display
     * @param {string|null} eventPassword - Optional event password
     * @param {string} additionalText - Optional additional text to display
     * @param {boolean} showShareButton - Whether to show the share button
     */
    setQrData(url, eventCode, eventPassword = null, additionalText = '', showShareButton = true) {
        console.log('[OverlayObjectHelper] Setting QR data:', { url, eventCode, password: !!eventPassword });
        // Always hide the share button in the overlay QR block
        this.qrBlockConfig = {
            url: url,
            eventCode: eventCode,
            eventPassword: eventPassword,
            additionalText: additionalText,
            showShareButton: false // Force hiding the share button
        };
        // If the QR block is already visible, update it
        if (this.qrContainer && this.qrContainer.style.display === 'block') {
            this.showQrBlock();
        }
    }
}

// Create a global instance
window.OverlayObjectHelper = new OverlayObjectHelper();