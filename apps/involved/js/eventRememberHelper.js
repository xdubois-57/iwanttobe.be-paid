/**
 * Event Remember Helper
 * 
 * Handles saving and retrieving event codes from local storage
 * - Saves created events with creation date
 * - Provides dropdown of previously created events
 * - Automatically removes events older than one month
 */

class EventRememberHelper {
    constructor() {
        this.storageKey = 'involved_remembered_events';
        this.lastSelectedKey = 'involved_last_selected_event';
        this.eventsList = this.getStoredEvents();
        this.purgeOldEvents();
    }

    /**
     * Get all stored events from local storage
     */
    getStoredEvents() {
        try {
            const stored = localStorage.getItem(this.storageKey);
            return stored ? JSON.parse(stored) : [];
        } catch (e) {
            console.error('Error retrieving stored events:', e);
            return [];
        }
    }

    /**
     * Save the list of events to local storage
     */
    saveEvents() {
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(this.eventsList));
        } catch (e) {
            console.error('Error saving events to storage:', e);
        }
    }

    /**
     * Remove events older than one month
     */
    purgeOldEvents() {
        const oneMonthAgo = new Date();
        oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1);
        
        const purged = this.eventsList.filter(event => {
            const eventDate = new Date(event.created);
            return eventDate >= oneMonthAgo;
        });
        
        if (purged.length !== this.eventsList.length) {
            this.eventsList = purged;
            this.saveEvents();
        }
    }

    /**
     * Add a new event to storage
     * @param {string} eventCode - The code for the event
     */
    addEvent(eventCode) {
        if (!eventCode) return;
        
        // Check if event already exists
        const existingIndex = this.eventsList.findIndex(e => e.code === eventCode);
        if (existingIndex >= 0) {
            // Update creation date for existing event
            this.eventsList[existingIndex].created = new Date().toISOString();
            // Move to end of list
            const event = this.eventsList.splice(existingIndex, 1)[0];
            this.eventsList.push(event);
        } else {
            // Add new event
            this.eventsList.push({
                code: eventCode,
                created: new Date().toISOString()
            });
            // Keep only the 5 most recent events
            if (this.eventsList.length > 5) {
                this.eventsList.shift(); // Remove oldest event
            }
        }
        
        this.saveEvents();
    }
    
    /**
     * Get the last selected event code
     */
    getLastSelected() {
        try {
            return localStorage.getItem(this.lastSelectedKey) || '';
        } catch (e) {
            console.error('Error retrieving last selected event:', e);
            return '';
        }
    }
    
    /**
     * Set the last selected event code
     */
    setLastSelected(eventCode) {
        try {
            localStorage.setItem(this.lastSelectedKey, eventCode);
        } catch (e) {
            console.error('Error saving last selected event:', e);
        }
    }
    
    /**
     * Format the date for display
     * @param {string} isoDate - ISO date string
     * @returns {string} Formatted date string
     */
    formatDate(isoDate) {
        try {
            const date = new Date(isoDate);
            return date.toLocaleDateString();
        } catch (e) {
            return '';
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only run if we're on the involved home page with event code input
    const eventCodeInput = document.getElementById('event-code');
    if (!eventCodeInput) return;
    
    const helper = new EventRememberHelper();
    const createForm = document.querySelector('form[action*="/involved/create"]');
    const eventCodeSelect = document.getElementById('remembered-events');
    
    // Handle creation of new events
    if (createForm) {
        createForm.addEventListener('submit', function() {
            // The actual code is generated server-side, so we'll need to 
            // intercept the response or add a callback after creation
            // This code will be complemented by server-side additions
        });
    }
    
    // Handle selection from dropdown
    if (eventCodeSelect) {
        eventCodeSelect.addEventListener('change', function() {
            const selected = this.value;
            if (selected && selected !== 'default') {
                eventCodeInput.value = selected;
                helper.setLastSelected(selected);
            }
        });
        
        // Reset dropdown to default when typing in the input
        eventCodeInput.addEventListener('input', function() {
            eventCodeSelect.value = 'default';
        });
    }
});
