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
        
        console.log('[OverlayClientHelper] Initialized with URL:', this.currentUrl, 'language:', this.currentLang);
        
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
     * Extract event code and language from the current URL
     */
    extractEventCodeAndLang() {
        const urlObj = new URL(window.location.href);
        const pathSegments = urlObj.pathname.split('/');
        
        // URL format: /{lang}/involved/{eventCode}/...
        if (pathSegments.length >= 4 && pathSegments[2].toLowerCase() === 'involved') {
            this.eventCode = pathSegments[3].toUpperCase();
            this.currentLang = pathSegments[1];
            console.log('[OverlayClientHelper] Extracted event code:', this.eventCode, 'and language:', this.currentLang);
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
            
            console.log('[OverlayClientHelper] Modified navigation to show admin link only');
        } else {
            console.log('[OverlayClientHelper] Could not find navigation menu to modify');
        }
    }
    
    /**
     * Send an emoji reaction for the current page
     * @param {string} emoji - Unicode emoji character
     * @returns {Promise}
     */
    sendEmoji(emoji) {
        if (!this.currentUrl) {
            this.calculateCurrentUrl();
        }

        // Make sure we have a valid language
        if (!this.currentLang) {
            this.currentLang = 'en'; // Default to English if language not available
            console.warn('[OverlayClientHelper] No language detected, defaulting to:', this.currentLang);
        }
        
        // Get the base URL (protocol + hostname + port)
        const baseUrl = window.location.origin;

        const formData = new FormData();
        formData.append('url', this.currentUrl);
        formData.append('emoji', emoji);

        return fetch(`${baseUrl}/${this.currentLang}/involved/ajax/emoji`, {
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
        
        // Make sure we have a valid language
        if (!this.currentLang) {
            this.currentLang = 'en'; // Default to English if language not available
            console.warn('[OverlayClientHelper] No language detected, defaulting to:', this.currentLang);
        }
        
        // Get the base URL (protocol + hostname + port)
        const baseUrl = window.location.origin;
        
        console.log('[OverlayClientHelper] Sending presence update for URL:', this.currentUrl);
        
        const formData = new FormData();
        formData.append('url', this.currentUrl);
        
        return fetch(`${baseUrl}/${this.currentLang}/involved/ajax/presence`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('[OverlayClientHelper] Presence update response:', data);
            
            // Check if there's an active URL to redirect to
            if (data.active_url && data.active_url !== window.location.href) {
                console.log('[OverlayClientHelper] Redirecting to active URL:', data.active_url);
                
                // Store that we're performing a redirection to avoid loops
                sessionStorage.setItem('redirected_from', window.location.href);
                
                // Redirect to the active URL
                window.location.href = data.active_url;
                return;
            }
            
            // Check if we just redirected and log it
            const redirectedFrom = sessionStorage.getItem('redirected_from');
            if (redirectedFrom) {
                console.log('[OverlayClientHelper] Was redirected from:', redirectedFrom);
                sessionStorage.removeItem('redirected_from');
            }
            
            if (data && data.count !== undefined) {
                return data.count;
            }
            return 0;
        })
        .catch(error => {
            console.error('[OverlayClientHelper] Error sending presence update:', error);
            return 0;
        });
    }
}

// Create a global instance
window.OverlayClientHelper = new OverlayClientHelper();
