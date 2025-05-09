/**
 * InvolvedRealtimeHelper for handling client-side interactions with the overlay system
 * - Handles "like" actions 
 * - Manages presence tracking with periodic "hello there" calls
 */
class InvolvedRealtimeHelper {
    constructor() {
        this.currentUrl = null;
        this.presenceInterval = null;
        this.presenceIntervalTime = 30000; // 30 seconds in milliseconds
        this.eventCode = null;
        this.currentLang = null;
    }
    
    /**
     * Initializes the helper with the current page URL
     */
    initialize() {
        this.calculateCurrentUrl();
        this.extractEventCodeAndLang();

        // Ensure we have a language set
        if (!this.currentLang) {
            // Fallback: extract language from URL
            const urlObj = new URL(window.location.href);
            const pathSegments = urlObj.pathname.split('/');
            if (pathSegments.length >= 2) {
                this.currentLang = pathSegments[1];
            } else {
                // Default to English if we can't determine the language
                this.currentLang = 'en';
            }
        }
        
        console.log('[InvolvedRealtimeHelper] Initialized with URL:', this.currentUrl, 'language:', this.currentLang);
        
        // Modify navigation if we're on an event page
        if (this.eventCode) {
            this.modifyNavigationForEventPage();
        }
    }
    
    /**
     * Calculate and store the current URL for tracking
     */
    calculateCurrentUrl() {
        // Get the current URL without hash or query parameters for consistency
        const urlObj = new URL(window.location.href);
        urlObj.hash = '';
        urlObj.search = '';
        let fullUrl = urlObj.toString();

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
            fullUrl = `${baseUrl}/${lang}/involved/${eventKey}/wordcloud/${wcid}`;
            console.log('[InvolvedRealtimeHelper] Normalized URL for tracking:', fullUrl);
        }
        // Always set currentUrl as fully qualified
        this.currentUrl = fullUrl;

    }
    
    /**
     * Extract event code and language from the current URL
     */
    /**
     * Extract event code from a URL string
     * @param {string} url - The URL to extract the event code from
     * @returns {string} The event code or 'unknown' if not found
     */
    extractEventCodeFromUrl(url) {
        try {
            const urlObj = new URL(url);
            const pathSegments = urlObj.pathname.split('/');
            
            // URL format: /{lang}/involved/{eventCode}/...
            if (pathSegments.length >= 4 && pathSegments[2].toLowerCase() === 'involved') {
                return pathSegments[3].toUpperCase();
            }
        } catch (e) {
            console.error('[InvolvedRealtimeHelper] Error extracting event code from URL:', e);
        }
        
        // If we can't determine the event code from the URL, use the stored one if available
        if (this.eventCode) {
            return this.eventCode;
        }
        
        console.warn('[InvolvedRealtimeHelper] Could not extract event code from URL:', url);
        return 'unknown';
    }
    
    /**
     * Extract event code and language from the current URL
     */
    extractEventCodeAndLang() {
        const urlObj = new URL(window.location.href);
        const pathSegments = urlObj.pathname.split('/');
        
        // URL format: /{lang}/involved/{eventCode}/...
        if (pathSegments.length >= 4 && pathSegments[2].toLowerCase() === 'involved') {
            this.eventCode = pathSegments[3].toUpperCase();
            this.currentLang = pathSegments[1];
            console.log('[InvolvedRealtimeHelper] Extracted event code:', this.eventCode, 'and language:', this.currentLang);
        }
    }
    
    /**
     * Modifies the main navigation header to include only the admin link
     */
    modifyNavigationForEventPage() {
        if (!this.eventCode || !this.currentLang) {
            return;
        }
        
        // Check if we've already modified the navigation
        if (document.querySelector('.event-admin-link')) {
            return;
        }
        
        // Set link text using the generic translation mechanism
        const linkText = window.t('admin_link_text');
        
        // Find the navigation menu
        const navLinks = document.querySelector('.nav-links ul');
        if (navLinks) {
            // Clear all existing menu items
            navLinks.innerHTML = '';
            
            // Create the admin link as a menu item
            const adminLi = document.createElement('li');
            const adminLink = document.createElement('a');
            adminLink.href = `/${this.currentLang}/involved/${this.eventCode}`;
            adminLink.className = 'event-admin-link';
            adminLink.textContent = linkText;
            
            // Add the admin link to the menu
            adminLi.appendChild(adminLink);
            navLinks.appendChild(adminLi);
            
            console.log('[InvolvedRealtimeHelper] Modified navigation to show admin link only');
        } else {
            console.log('[InvolvedRealtimeHelper] Could not find navigation menu to modify');
        }
    }
    
    /**
     * Send an emoji reaction for the current event
     * @param {string} emoji - Unicode emoji character
     * @param {number} [eventItemId] - Optional event item ID for targeted reactions
     * @returns {Promise}
     */
    sendEmoji(emoji, eventItemId = null) {
        // Make sure we have a valid language
        if (!this.currentLang) {
            this.currentLang = 'en'; // Default to English if language not available
            console.warn('[InvolvedRealtimeHelper] No language detected, defaulting to:', this.currentLang);
        }
        
        // Make sure we have an event code
        if (!this.eventCode) {
            this.extractEventCodeAndLang();
        }
        
        if (!this.eventCode) {
            console.error('[InvolvedRealtimeHelper] No event code available for emoji submission');
            return Promise.reject(new Error('No event code available'));
        }
        
        // Get the base URL (protocol + hostname + port)
        const baseUrl = window.location.origin;

        const formData = new FormData();
        formData.append('emoji', emoji);
        
        // Add event item ID if provided
        if (eventItemId) {
            formData.append('eventItemId', eventItemId);
        }
        
        // Use eventCode directly from the instance
        return fetch(`${baseUrl}/${this.currentLang}/involved/${this.eventCode}/emoji`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json());
    }

    /**
     * Increment a default 👍 emoji (backward compat for old like() callers)
     * @returns {Promise}
     */
    like() {
        return this.sendEmoji('👍');
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
        
        console.log('[InvolvedRealtimeHelper] Started presence tracking');
    }
    
    /**
     * Stop sending presence updates
     */
    stopPresenceTracking() {
        if (this.presenceInterval) {
            clearInterval(this.presenceInterval);
            this.presenceInterval = null;
            console.log('[InvolvedRealtimeHelper] Stopped presence tracking');
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
        
        // Make sure we have a valid language
        if (!this.currentLang) {
            this.currentLang = 'en'; // Default to English if language not available
            console.warn('[InvolvedRealtimeHelper] No language detected, defaulting to:', this.currentLang);
        }
        
        // Get the base URL (protocol + hostname + port)
        const baseUrl = window.location.origin;
        
        console.log('[InvolvedRealtimeHelper] Sending presence update for URL:', this.currentUrl);
        
        const formData = new FormData();
        formData.append('url', this.currentUrl);
        
        // Get the event code from the URL
        const eventCode = this.extractEventCodeFromUrl(this.currentUrl);
        return fetch(`${baseUrl}/${this.currentLang}/involved/${eventCode}/presence`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('[InvolvedRealtimeHelper] Presence update response:', data);
            
            // Check if there's an active URL to redirect to
            if (data.active_url && data.active_url !== window.location.href) {
                console.log('[InvolvedRealtimeHelper] Redirecting to active URL:', data.active_url);
                
                // Store that we're performing a redirection to avoid loops
                sessionStorage.setItem('redirected_from', window.location.href);
                
                // Redirect to the active URL
                window.location.href = data.active_url;
                return;
            }
            
            // Check if we just redirected and log it
            const redirectedFrom = sessionStorage.getItem('redirected_from');
            if (redirectedFrom) {
                console.log('[InvolvedRealtimeHelper] Was redirected from:', redirectedFrom);
                sessionStorage.removeItem('redirected_from');
            }
            
            if (data && data.count !== undefined) {
                return data.count;
            }
            return 0;
        })
        .catch(error => {
            console.error('[InvolvedRealtimeHelper] Error sending presence update:', error);
            return 0;
        });
    }
}

// Create a global instance
window.InvolvedRealtimeHelper = new InvolvedRealtimeHelper();
