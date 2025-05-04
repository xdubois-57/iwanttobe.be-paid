/**
 * Wordcloud2.js error-fixing wrapper
 * This script fixes errors in the original wordcloud2.js library
 */
(function() {
    // Wait for wordcloud library to load
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof WordCloud !== 'undefined') {
            console.log('[WordCloud Wrapper] Patching WordCloud library...');
            
            // Store the original WordCloud function
            const originalWordCloud = WordCloud;
            
            // Replace with our patched version
            window.WordCloud = function(canvas, options) {
                // Make sure list exists and is an array
                if (!options.list || !Array.isArray(options.list) || options.list.length === 0) {
                    console.warn('[WordCloud Wrapper] Empty or invalid word list provided. Using placeholder.');
                    // Use translated string if available
                    let placeholder = 'Scan the QR code to answer';
                    if (window.lang && typeof window.lang.translate === 'function') {
                        placeholder = window.lang.translate('scan_qr_to_answer');
                    }
                    options.list = [[placeholder, 1]];
                }
                
                // Make sure settings is always an object
                options.settings = options.settings || {};
                
                // Call the original function with our fixed options
                return originalWordCloud(canvas, options);
            };
            
            // Copy over all properties from the original WordCloud
            for (let prop in originalWordCloud) {
                if (originalWordCloud.hasOwnProperty(prop)) {
                    window.WordCloud[prop] = originalWordCloud[prop];
                }
            }
            
            console.log('[WordCloud Wrapper] WordCloud library patched successfully');
        } else {
            console.error('[WordCloud Wrapper] WordCloud library not found');
        }
    });
})();
