class OverlayObjectHelper {
    constructor() {
        this.active = false;
        this.overlay = null;
        this.title = null;
        this.heartCount = 0;
        this.previousHeartCount = 0;
        this.currentUrl = null;
        this.pollingInterval = null;
        this.animationQueue = [];
        this.qrContainer = null;
        this.qrData = null;
        this.animationInProgress = false;
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
        this.qrContainer.style.padding = '15px';
        this.qrContainer.style.borderRadius = '8px';
        this.qrContainer.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.2)';
        this.qrContainer.style.maxWidth = '300px';
        this.qrContainer.style.display = 'none';
        this.qrContainer.id = 'overlay-qr-container';
        this.overlay.appendChild(this.qrContainer);
        
        this.render();
        
        // Calculate current URL for like tracking
        this.calculateCurrentUrl();
        
        // Start polling for like updates
        this.startPolling();
        
        console.log('[OverlayObjectHelper] Activated:', { 
            title: this.title,
            heartCount: this.heartCount,
            url: this.currentUrl
        });
    }

    deactivate() {
        if (!this.active) return;
        this.active = false;
        
        // Stop polling when deactivated
        this.stopPolling();
        
        // Clear animation queue
        this.animationQueue = [];
        this.animationInProgress = false;
        
        if (this.overlay) {
            document.body.removeChild(this.overlay);
            this.overlay = null;
            this.qrContainer = null;
        }
    }

    addTitle(titleText) {
        this.title = titleText;
        this.render();
        console.log('[OverlayObjectHelper] Title set:', { title: this.title });
    }

    removeTitle() {
        this.title = null;
        this.render();
    }

    addHeart(count = 0) {
        // Store previous count for animation
        this.previousHeartCount = this.heartCount;
        this.heartCount = count;
        
        // Check for increase in hearts to trigger animation
        const difference = this.heartCount - this.previousHeartCount;
        if (difference > 0) {
            for (let i = 0; i < difference; i++) {
                this.queueHeartAnimation();
            }
        }
        
        this.render();
    }

    setHeartCount(count = 0) {
        // Store previous count for animation
        this.previousHeartCount = this.heartCount;
        this.heartCount = count;
        
        // Check for increase in hearts to trigger animation
        const difference = this.heartCount - this.previousHeartCount;
        if (difference > 0) {
            console.log(`[OverlayObjectHelper] Hearts increased by ${difference}! Animating...`);
            for (let i = 0; i < difference; i++) {
                this.queueHeartAnimation();
            }
        }
        
        this.render();
    }

    removeHeart() {
        this.heartCount = null;
        this.render();
    }
    
    // Set QR data for display
    setQrData(url, eventCode, eventPassword, additionalText = '', showShareButton = false) {
        this.qrData = {
            url,
            eventCode,
            eventPassword,
            additionalText,
            showShareButton
        };
        
        // If QR container exists, update it
        if (this.qrContainer && this.active) {
            this.renderQrBlock();
        }
        
        console.log('[OverlayObjectHelper] QR data set:', this.qrData);
    }
    
    // Show QR block
    showQrBlock() {
        if (this.qrContainer) {
            this.qrContainer.style.display = 'block';
            this.renderQrBlock();
        }
    }
    
    // Hide QR block
    hideQrBlock() {
        if (this.qrContainer) {
            this.qrContainer.style.display = 'none';
        }
    }
    
    // Render QR block with EventQrBlock
    async renderQrBlock() {
        if (!this.qrContainer || !this.qrData) return;
        
        // Clear container
        this.qrContainer.innerHTML = '';
        
        // Use EventQrBlock if available
        if (typeof EventQrBlock === 'function') {
            try {
                new EventQrBlock(
                    this.qrContainer, 
                    this.qrData.url, 
                    this.qrData.eventCode, 
                    this.qrData.eventPassword, 
                    this.qrData.additionalText,
                    this.qrData.showShareButton
                );
                this.qrContainer.setAttribute('data-initialized', 'true');
            } catch (error) {
                console.error('[OverlayObjectHelper] Error rendering QR block:', error);
                this.qrContainer.innerHTML = '<div style="padding:10px;color:#721c24;background:#f8d7da;border-radius:4px;">QR code could not be rendered</div>';
            }
        } else {
            console.warn('[OverlayObjectHelper] EventQrBlock class not available');
            this.qrContainer.innerHTML = '<div style="padding:10px;color:#856404;background:#fff3cd;border-radius:4px;">QR code generator not loaded</div>';
        }
    }
    
    // Queue a heart animation
    queueHeartAnimation() {
        this.animationQueue.push({
            id: Date.now() + Math.random(),
            processed: false
        });
        this.processAnimationQueue();
    }
    
    // Process heart animations from queue
    processAnimationQueue() {
        if (this.animationInProgress || !this.active || this.animationQueue.length === 0) return;
        
        // Get next animation from queue
        const animation = this.animationQueue.find(a => !a.processed);
        if (!animation) return;
        
        animation.processed = true;
        this.animationInProgress = true;
        
        // Create floating heart element
        const floatingHeart = document.createElement('div');
        floatingHeart.innerHTML = '❤️';
        floatingHeart.style.position = 'absolute';
        floatingHeart.style.fontSize = '2rem';
        floatingHeart.style.zIndex = '11002';
        floatingHeart.style.transition = 'all 1.5s ease-in-out';
        floatingHeart.style.opacity = '1';
        
        // Get target heart element position
        const targetHeart = this.overlay.querySelector('#heart-count-display');
        let targetX = 40;
        let targetY = 40;
        
        if (targetHeart) {
            const rect = targetHeart.getBoundingClientRect();
            targetX = rect.x;
            targetY = rect.y;
        }
        
        // Set random start position at bottom of screen
        const startX = Math.random() * (window.innerWidth - 100) + 50;
        floatingHeart.style.left = `${startX}px`;
        floatingHeart.style.top = `${window.innerHeight - 50}px`;
        
        // Add to overlay
        this.overlay.appendChild(floatingHeart);
        
        // Force reflow to ensure transition works
        void floatingHeart.offsetWidth;
        
        // Start animation
        floatingHeart.style.left = `${targetX}px`;
        floatingHeart.style.top = `${targetY}px`;
        floatingHeart.style.opacity = '0.8';
        
        // Clean up after animation
        setTimeout(() => {
            if (this.overlay && this.overlay.contains(floatingHeart)) {
                this.overlay.removeChild(floatingHeart);
            }
            this.animationInProgress = false;
            
            // Process next animation
            this.processAnimationQueue();
        }, 1600);
    }
    
    // Calculate the current URL for tracking likes
    calculateCurrentUrl() {
        const scheme = window.location.protocol.replace(':', '');
        const host = window.location.host;
        const pathname = window.location.pathname;
        
        // Extract path components to rebuild URL without any query or hash
        const pathParts = pathname.split('/');
        
        // Check if this is a wordcloud path
        if (pathParts.length >= 6 && pathParts[2] === 'involved' && pathParts[4] === 'wordcloud') {
            const lang = pathParts[1];
            const eventKey = pathParts[3];
            const wordCloudId = pathParts[5];
            
            // Format URL consistently: https://host/lang/involved/EVENT/wordcloud/ID
            this.currentUrl = `${scheme}://${host}/${lang}/involved/${eventKey}/wordcloud/${wordCloudId}`;
            console.log('[OverlayObjectHelper] URL set for tracking:', this.currentUrl);
        } else {
            // Not a valid wordcloud URL
            this.currentUrl = null;
            console.warn('[OverlayObjectHelper] Not a trackable wordcloud URL:', pathname);
        }
    }
    
    // Start polling for like updates
    startPolling() {
        this.stopPolling(); // Clear any existing interval
        
        if (!this.currentUrl) {
            console.warn('[OverlayObjectHelper] No URL to poll for likes');
            return;
        }
        
        // Poll every 5 seconds
        this.pollingInterval = setInterval(() => {
            this.fetchLikes();
        }, 5000);
        
        // Also fetch immediately
        this.fetchLikes();
    }
    
    // Stop polling
    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }
    
    // Fetch latest likes count
    fetchLikes() {
        if (!this.currentUrl) return;
        
        fetch('/ajax/likes?url=' + encodeURIComponent(this.currentUrl))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    console.log('[OverlayObjectHelper] Fetched likes:', data.likes);
                    this.setHeartCount(data.likes);
                }
            })
            .catch(error => console.error('[OverlayObjectHelper] Error fetching likes:', error));
    }

    render() {
        if (!this.overlay) return;
        
        console.log('[OverlayObjectHelper] Rendering overlay:', { 
            active: this.active, 
            title: this.title, 
            heartCount: this.heartCount,
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
            titleDiv.style.zIndex = '11001';
            
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
        
        // Render heart and counter
        if (typeof this.heartCount === 'number') {
            const heartContainer = document.createElement('div');
            heartContainer.style.position = 'absolute';
            heartContainer.style.top = '40px';
            heartContainer.style.right = '40px';
            heartContainer.style.display = 'flex';
            heartContainer.style.alignItems = 'center';
            heartContainer.style.fontSize = '1.8rem';
            heartContainer.style.zIndex = '11001';
            heartContainer.id = 'heart-count-display';
            
            // Apply theme-appropriate styles
            if (this.isDarkMode()) {
                heartContainer.style.color = 'var(--text-primary, #ffffff)';
            } else {
                heartContainer.style.color = '#000000';
            }
            
            // Heart emoji
            const heart = document.createElement('span');
            heart.textContent = '❤️';
            heart.style.marginRight = '6px';
            
            // Number to the RIGHT of the heart
            const number = document.createElement('span');
            number.textContent = this.heartCount;
            
            // Add to container in correct order: heart THEN number
            heartContainer.appendChild(heart);
            heartContainer.appendChild(number);
            
            this.overlay.appendChild(heartContainer);
        }
    }
    
    // Helper to detect dark mode
    isDarkMode() {
        // Check for dark theme via HTML attribute
        if (document.documentElement.getAttribute('data-theme') === 'dark') {
            return true;
        }
        
        // Check using prefers-color-scheme media query
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return true;
        }
        
        // Check if background color is dark
        const bgColor = getComputedStyle(document.body).backgroundColor;
        if (bgColor) {
            // Parse RGB colors
            const rgb = bgColor.match(/\d+/g);
            if (rgb && rgb.length >= 3) {
                // Calculate perceived brightness
                // Formula: (R * 0.299 + G * 0.587 + B * 0.114)
                const brightness = (parseInt(rgb[0]) * 0.299 + 
                                   parseInt(rgb[1]) * 0.587 + 
                                   parseInt(rgb[2]) * 0.114);
                // If brightness < 128, consider it dark
                return brightness < 128;
            }
        }
        
        return false;
    }
}

// Create a global instance
window.OverlayObjectHelper = new OverlayObjectHelper();