document.addEventListener('DOMContentLoaded', function() {
    const themeSelector = document.getElementById('theme-selector');
    const htmlElement = document.documentElement;
    
    // Check for saved theme preference or use auto as default
    const savedTheme = localStorage.getItem('theme') || 'auto';
    
    // Apply the saved theme on page load
    applyTheme(savedTheme);
    
    // Set the selector to match the current theme
    if (themeSelector) {
        themeSelector.value = savedTheme;
        
        // Add event listener for theme changes
        themeSelector.addEventListener('change', function() {
            const newTheme = this.value;
            applyTheme(newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }
    
    // Function to apply the selected theme
    function applyTheme(theme) {
        if (theme === 'light') {
            htmlElement.setAttribute('data-theme', 'light');
        } else if (theme === 'dark') {
            htmlElement.setAttribute('data-theme', 'dark');
        } else {
            // Auto - use system preference
            htmlElement.removeAttribute('data-theme');
            
            // Check system preference for initial load
            checkSystemPreference();
            
            // Listen for system preference changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', checkSystemPreference);
        }
    }
    
    // Function to check system color scheme preference
    function checkSystemPreference() {
        const isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (isDarkMode) {
            htmlElement.setAttribute('data-theme', 'dark');
        } else {
            htmlElement.setAttribute('data-theme', 'light');
        }
    }
    
    // If theme is auto, listen for system preference changes
    if (savedTheme === 'auto') {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', checkSystemPreference);
    }
});
