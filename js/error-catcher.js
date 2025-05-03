/**
 * Error catching script to help debug JavaScript errors
 */
(function() {
    console.log('Error catcher loaded');
    
    // Store original console.error to enhance it
    const originalConsoleError = console.error;
    console.error = function() {
        // Call original function
        originalConsoleError.apply(console, arguments);
        
        // Get stack trace
        const stack = new Error().stack;
        console.log('Stack trace for error:', stack);
        
        // Check if error is related to fullscreenQR
        const errorMessage = Array.from(arguments).join(' ');
        if (errorMessage.includes('fullscreenQR')) {
            console.log('Found fullscreenQR related error in console.error');
            console.log('Scanning DOM for #fullscreen-qr elements...');
            const element = document.getElementById('fullscreen-qr');
            console.log('Element exists:', element !== null);
        }
    };
    
    window.addEventListener('error', function(event) {
        console.log('JavaScript error intercepted:', {
            message: event.message,
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno,
            error: event.error
        });
        
        // Check for fullscreenQR related errors specifically
        if (event.message && event.message.includes('fullscreenQR')) {
            console.log('Found fullscreenQR related error, checking DOM...');
            console.log('fullscreenQR element exists:', document.getElementById('fullscreen-qr') !== null);
            
            // Add code to trace all global script variables
            console.log('Checking for fullscreenQR in global scope:', typeof window.fullscreenQR);
            
            // Scan all script elements on the page
            const scripts = document.getElementsByTagName('script');
            for (let i = 0; i < scripts.length; i++) {
                const script = scripts[i];
                if (!script.src && script.textContent.includes('fullscreenQR')) {
                    console.log('Found script with fullscreenQR reference:', i);
                    console.log('Script content preview:', script.textContent.substring(0, 150) + '...');
                }
            }
        }
    });
    
    // Fix for the fullscreenQR issue - add a shim for any code trying to access it
    window.addEventListener('DOMContentLoaded', function() {
        if (window.fullscreenQR === undefined) {
            console.log('Adding fullscreenQR shim to prevent errors');
            window.fullscreenQR = {
                style: {
                    display: 'none'
                }
            };
        }
    });
})();
