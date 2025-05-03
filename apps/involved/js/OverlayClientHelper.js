/**
 * OverlayClientHelper for handling client-side interactions with the overlay system
 * - Handles "like" actions 
 * - Manages presence tracking with periodic "hello there" calls
 */
class OverlayClientHelper {
    constructor() {
        this.currentUrl = null;
        this.presenceInterval = null;
        this.presenceIntervalTime = 30000; // 30 seconds in milliseconds
    }
    
    /**
     * Initializes the helper with the current page URL
     */
    initialize() {
        this.calculateCurrentUrl();
        console.log('[OverlayClientHelper] Initialized with URL:', this.currentUrl);
    }
    
    /**
     * Calculate and store the current URL for tracking
     */
    calculateCurrentUrl() {
        // Get the current URL without hash or query parameters for consistency
        const urlObj = new URL(window.location.href);
        urlObj.hash = '';
        urlObj.search = '';
        this.currentUrl = urlObj.toString();

        // Check if we're on a wordcloud page
        const pathSegments = urlObj.pathname.split('/');
        
        // URL format: /lang/involved/key/wordcloud/wcid[/add]
        if (pathSegments.length >= 6 && pathSegments[2] === 'involved' && pathSegments[4] === 'wordcloud') {
            // We're on a wordcloud page, normalize the URL to always track likes for the main wordcloud page
            const baseUrl = urlObj.origin;
            const lang = pathSegments[1];
            const eventKey = pathSegments[3];
            const wcid = pathSegments[5];
            
            // Normalize to base wordcloud URL, removing any extra paths like '/add'
            this.currentUrl = `${baseUrl}/${lang}/involved/${eventKey}/wordcloud/${wcid}`;
            console.log('[OverlayClientHelper] Normalized URL for tracking:', this.currentUrl);
        }
    }
    
    /**
     * Increment likes for the current page
     * @returns {Promise} Promise resolving to the new like count
     */
    like() {
        if (!this.currentUrl) {
            this.calculateCurrentUrl();
        }
        
        console.log('[OverlayClientHelper] Liking URL:', this.currentUrl);
        
        const formData = new FormData();
        formData.append('url', this.currentUrl);
        
        return fetch('/ajax/like', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('[OverlayClientHelper] Like successful. New count:', data.likes);
                return data.likes;
            } else {
                console.error('[OverlayClientHelper] Like failed:', data.error);
                throw new Error(data.error);
            }
        })
        .catch(error => {
            console.error('[OverlayClientHelper] Error liking:', error);
            throw error;
        });
    }
    
    /**
     * Start sending presence updates at regular intervals
     */
    startPresenceTracking() {
        if (this.presenceInterval) {
            this.stopPresenceTracking();
        }
        
        if (!this.currentUrl) {
            this.calculateCurrentUrl();
        }
        
        // Send initial presence update
        this.sendPresenceUpdate();
        
        // Set interval for regular updates
        this.presenceInterval = setInterval(() => {
            this.sendPresenceUpdate();
        }, this.presenceIntervalTime);
        
        console.log('[OverlayClientHelper] Started presence tracking');
    }
    
    /**
     * Stop sending presence updates
     */
    stopPresenceTracking() {
        if (this.presenceInterval) {
            clearInterval(this.presenceInterval);
            this.presenceInterval = null;
            console.log('[OverlayClientHelper] Stopped presence tracking');
        }
    }
    
    /**
     * Send a single presence update to the server
     * @returns {Promise} Promise resolving to the active users count
     */
    sendPresenceUpdate() {
        if (!this.currentUrl) {
            this.calculateCurrentUrl();
        }
        
        const formData = new FormData();
        formData.append('url', this.currentUrl);
        
        return fetch('/ajax/presence', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('[OverlayClientHelper] Presence update successful. Active users:', data.active_users);
                return data.active_users;
            } else {
                console.error('[OverlayClientHelper] Presence update failed:', data.error);
                throw new Error(data.error);
            }
        })
        .catch(error => {
            console.error('[OverlayClientHelper] Error updating presence:', error);
            // Don't throw here to avoid breaking the interval
            return 0;
        });
    }
}

// Create a global instance
window.OverlayClientHelper = new OverlayClientHelper();
