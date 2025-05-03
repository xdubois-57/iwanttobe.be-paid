/**
 * QR Transfer
 * Copyright (C) 2025 Xavier Dubois
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

class WordCloudManager {
    constructor(containerId, options = {}, canvasHeight = null, clickable = true) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            throw new Error(`Container with id '${containerId}' not found`);
        }

        this.canvas = null;
        this.previousData = null;
        // Track max weight so weightFactor can scale words dynamically
        this.currentMaxWeight = 1;
        this.options = this.getDefaultOptions(options);
        this.userCanvasHeight = canvasHeight;
        this.isClickable = clickable;
        this.initializeCanvas();
        this.isFullScreen = false;
        this.fullScreenOverlay = null;
        
        if (this.isClickable) {
            this.setupFullScreenToggle();
        }
        
        // Setup theme change observer
        this.setupThemeObserver();
    }
    
    setupThemeObserver() {
        // Watch for changes to the data-theme attribute
        const htmlElement = document.documentElement;
        const observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                    console.log('Theme changed to:', htmlElement.getAttribute('data-theme'));
                    // Update options with new color scheme
                    const isDarkMode = this.detectDarkMode();
                    const currentColor = this.options.color;
                    const newColor = isDarkMode ? 'random-light' : 'random-dark';
                    
                    // Only update if color scheme changed
                    if ((isDarkMode && currentColor !== 'random-light') || 
                        (!isDarkMode && currentColor !== 'random-dark')) {
                        console.log('Updating word cloud colors to:', newColor);
                        this.options.color = newColor;
                        
                        // Redraw if we have data
                        if (this.options.list && this.options.list.length > 0) {
                            WordCloud(this.canvas, this.options);
                        }
                    }
                }
            }
        });
        
        // Start observing the html element for data-theme changes
        observer.observe(htmlElement, { attributes: true, attributeFilter: ['data-theme'] });
    }

    setupFullScreenToggle() {
        // Add click event to toggle fullscreen
        this.canvas.addEventListener('click', this.toggleFullScreen.bind(this));
    }

    toggleFullScreen() {
        if (this.isFullScreen) {
            this.exitFullScreen();
        } else {
            this.enterFullScreen();
        }
    }

    enterFullScreen() {
        // Create fullscreen overlay
        this.fullScreenOverlay = document.createElement('div');
        this.fullScreenOverlay.className = 'word-cloud-fullscreen-overlay';
        
        // Get background color of the page
        const pageBackgroundColor = window.getComputedStyle(document.body).backgroundColor;
        
        // Style the overlay
        Object.assign(this.fullScreenOverlay.style, {
            position: 'fixed',
            top: '0',
            left: '0',
            width: '100vw',
            height: '100vh',
            zIndex: '10000',
            background: pageBackgroundColor || 'white',
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            padding: '2rem',
            boxSizing: 'border-box',
            overflow: 'hidden'
        });
        
        // Create new canvas for fullscreen
        const fullScreenCanvas = document.createElement('canvas');
        fullScreenCanvas.className = 'word-cloud-fullscreen-canvas';
        
        // Get window dimensions for the canvas
        const width = window.innerWidth - 40; // 20px padding on each side
        const height = window.innerHeight - 40;
        
        // Set canvas dimensions
        fullScreenCanvas.width = width;
        fullScreenCanvas.height = height;
        
        // Add click event to exit fullscreen
        fullScreenCanvas.addEventListener('click', this.toggleFullScreen.bind(this));
        
        this.fullScreenOverlay.appendChild(fullScreenCanvas);
        document.body.appendChild(this.fullScreenOverlay);
        
        // Create new options optimized for fullscreen
        const fullScreenOptions = this.getFullscreenOptions(width, height);
        
        WordCloud(fullScreenCanvas, fullScreenOptions);
        
        // Set fullscreen state
        this.isFullScreen = true;
        
        // Use OverlayObjectHelper if available
        if (window.OverlayObjectHelper) {
            // Extract question text from header if available
            let questionText = '';
            let likeCount = 0;
            
            // Try to find the question text from the h1 in the main article
            const h1 = document.querySelector('main article h1');
            if (h1) {
                questionText = h1.textContent.trim();
                // Check if h1 has a data-likes attribute
                if (h1.hasAttribute('data-likes')) {
                    likeCount = parseInt(h1.getAttribute('data-likes'), 10) || 0;
                }
            }
            
            console.log('[WordCloudManager] Activating OverlayObjectHelper with:', {
                questionText: questionText,
                likeCount: likeCount
            });
            
            // Always activate first before setting properties
            window.OverlayObjectHelper.activate();
            
            // Then set title and heart
            if (questionText) {
                console.log('[WordCloudManager] Setting title:', questionText);
                window.OverlayObjectHelper.addTitle(questionText);
            }
            
            console.log('[WordCloudManager] Setting heart count:', likeCount);
            window.OverlayObjectHelper.addHeart(likeCount);
        } else {
            console.warn('[WordCloudManager] OverlayObjectHelper not found');
        }
        
        // Dispatch custom event for fullscreen change
        window.dispatchEvent(new CustomEvent('wordcloud-fullscreen-change', {
            detail: { isFullScreen: true }
        }));
        
        // Add escape key listener
        this.escKeyHandler = (e) => {
            if (e.key === 'Escape') {
                this.exitFullScreen();
            }
        };
        document.addEventListener('keydown', this.escKeyHandler);
    }

    getFullscreenOptions(width, height) {
        // Create optimized options for fullscreen mode
        const fullScreenOptions = Object.assign({}, this.options);
        fullScreenOptions.list = this.options.list;
        
        // Use finer grid for better quality
        fullScreenOptions.gridSize = Math.max(1, Math.floor(this.options.gridSize * 0.75));
        
        // Adjust ellipticity for the new dimensions
        fullScreenOptions.ellipticity = height / width;
        
        // Recompute fontSizes for the larger canvas
        fullScreenOptions.weightFactor = (weight) => {
            // Get the shorter dimension for scaling
            const shortestSide = Math.min(width, height);
            // Calculate ideal max font size (25% of shorter dimension)
            const targetMaxFont = shortestSide * 0.25;
            // Scale linearly based on weight
            return (weight / this.currentMaxWeight) * targetMaxFont;
        };
        
        // Slightly reduce rotation for better readability
        fullScreenOptions.rotateRatio = Math.max(0.1, this.options.rotateRatio * 0.8);
        
        return fullScreenOptions;
    }

    exitFullScreen() {
        if (this.fullScreenOverlay && document.body.contains(this.fullScreenOverlay)) {
            document.body.removeChild(this.fullScreenOverlay);
            document.removeEventListener('keydown', this.escKeyHandler);
            this.fullScreenOverlay = null;
            this.isFullScreen = false;
            
            // Deactivate OverlayObjectHelper if available
            if (window.OverlayObjectHelper) {
                window.OverlayObjectHelper.deactivate();
            }
            
            // Dispatch custom event for fullscreen change
            window.dispatchEvent(new CustomEvent('wordcloud-fullscreen-change', {
                detail: { isFullScreen: false }
            }));
        }
    }
    
    initializeCanvas() {
        // Create canvas element with full container width
        this.canvas = document.createElement('canvas');
        const containerWidth = this.container.offsetWidth;
        this.canvas.width = containerWidth;
        
        // Set height to user-provided value or fill available space
        this.canvas.height = this.userCanvasHeight !== null ? this.userCanvasHeight : this.calculateAvailableHeight();
        
        // Add cursor style to indicate it's clickable only if it's clickable
        if (this.isClickable) {
            this.canvas.style.cursor = 'pointer';
        }
        
        this.container.appendChild(this.canvas);
    }

    calculateAvailableHeight() {
        // Use the user-provided canvas height if set
        if (this.userCanvasHeight !== null) {
            return this.userCanvasHeight;
        }
        // Use the container's height if available, otherwise use 30% of viewport height
        const containerHeight = this.container.offsetHeight;
        if (containerHeight > 0) {
            return containerHeight;
        }
        return window.innerHeight * 0.3;
    }

    getDefaultOptions(customOptions = {}) {
        // Get override setting for dark mode colors
        const forceColorScheme = localStorage.getItem('wordCloudColorScheme');
        
        // Check if dark mode is active 
        let isDarkMode = this.detectDarkMode();
        
        // Allow manual override if set
        if (forceColorScheme === 'light') {
            console.log('Using light color scheme (forced by user setting)');
            isDarkMode = true;
        } else if (forceColorScheme === 'dark') {
            console.log('Using dark color scheme (forced by user setting)');
            isDarkMode = false;
        } else {
            console.log('Word cloud using color scheme:', isDarkMode ? 'random-light (dark mode)' : 'random-dark (light mode)');
        }
        
        const defaultOptions = {
            gridSize: 2, // Smaller grid for maximum word density
            // Dynamically scale words so the largest one takes ~20% of the
            // shortest canvas dimension. This keeps the cloud full for any size.
            weightFactor: (weight) => {
                const shortestSide = Math.min(this.canvas.width, this.canvas.height);
                const targetMaxFont = shortestSide * 0.20; // 20% of shorter side
                return (weight / this.currentMaxWeight) * targetMaxFont;
            },
            fontFamily: 'Arial, sans-serif',
            fontWeight: 'bold', // Make all words bold
            color: isDarkMode ? 'random-light' : 'random-dark', // Use light colors in dark mode
            rotateRatio: 0.3, // Minimal rotation for better space usage
            rotationSteps: 2, // Number of different rotation steps
            backgroundColor: 'transparent',
            shape: 'rectangle', // Rectangle shape fits better with screen width
            ellipticity: window.innerHeight / window.innerWidth, // Responsive ellipticity
            shrinkToFit: true, // Don't shrink - fill the canvas
            drawOutOfBound: false,
            classes: function(word) {
                // Add classes for app names to style them differently
                return ['Paid!', 'Driven!', 'Involved!'].includes(word) ? 'app-name' : '';
            }
        };
        
        return Object.assign({}, defaultOptions, customOptions);
    }

    detectDarkMode() {
        // Check if dark mode is enabled by looking at the data-theme attribute
        const theme = document.documentElement.getAttribute('data-theme');
        // If theme is null, check localStorage for saved setting
        if (theme === null) {
            const savedTheme = localStorage.getItem('theme');
            console.log('Theme attribute not found, checking localStorage:', savedTheme);
            if (savedTheme === 'dark') {
                return true;
            } else if (savedTheme === 'auto') {
                // Use system preference when set to auto
                return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            }
            return false;
        }
        
        console.log('HTML data-theme:', theme, '(dark mode:', theme === 'dark', ')');
        return theme === 'dark';
    }

    updateWordCloud(data) {
        if (!data || !data.length) {
            // Handle empty data gracefully - clear the canvas and show placeholder text
            this.clearCanvas();
            return;
        }

        // Consolidate duplicate words by summing weights
        const aggregated = [];
        const wordMap = new Map();
        data.forEach(item => {
            const word = Array.isArray(item) ? item[0] : item.word;
            const weight = Array.isArray(item) ? item[1] : (item.weight || 1);
            const current = wordMap.get(word) || 0;
            wordMap.set(word, current + weight);
        });
        wordMap.forEach((weight, word) => aggregated.push([word, weight]));
        // Sort by weight desc for consistency
        aggregated.sort((a, b) => b[1] - a[1]);

        // Check if data has changed
        const dataChanged = !this.previousData || 
            JSON.stringify(aggregated) !== JSON.stringify(this.previousData);
            
        if (!dataChanged) {
            return; // Skip redraw if data hasn't changed
        }

        // Store current data for future comparison
        this.previousData = JSON.parse(JSON.stringify(aggregated));
        
        // Update the current maximum weight for adaptive scaling
        this.currentMaxWeight = Math.max(...aggregated.map(item => item[1]));

        // Update options with new data
        this.options.list = aggregated;
        // Generate the word cloud
        WordCloud(this.canvas, this.options);
        
        // Also update fullscreen canvas if it exists
        if (this.isFullScreen && this.fullScreenOverlay) {
            const fullScreenCanvas = this.fullScreenOverlay.querySelector('.word-cloud-fullscreen-canvas');
            if (fullScreenCanvas) {
                const width = fullScreenCanvas.width;
                const height = fullScreenCanvas.height;
                const fullScreenOptions = this.getFullscreenOptions(width, height);
                WordCloud(fullScreenCanvas, fullScreenOptions);
            }
        }
    }

    clearCanvas() {
        if (!this.canvas) return;
        
        const ctx = this.canvas.getContext('2d');
        // Clear the canvas
        ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        // Add placeholder text if desired
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = this.detectDarkMode() ? '#666666' : '#cccccc';
        ctx.font = '16px sans-serif';
        ctx.fillText('No data available', this.canvas.width / 2, this.canvas.height / 2);
    }

    loadStaticData(data) {
        this.updateWordCloud(data);
    }

    startPolling(apiUrl, interval = 2000) {
        if (!this.container.hasAttribute('data-wordcloud-url')) {
            throw new Error('Container must have data-wordcloud-url attribute for polling');
        }

        const fetchData = () => {
            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    this.updateWordCloud(data);
                })
                .catch(error => {
                    console.error('Error fetching word cloud data:', error);
                });
        };

        // Initial load
        fetchData();
        
        // Set up polling
        setInterval(fetchData, interval);
    }

    makeResponsive() {
        window.addEventListener('resize', () => {
            const newWidth = this.container.offsetWidth;
            this.canvas.width = newWidth;
            
            // Update ellipticity based on new dimensions
            this.options.ellipticity = window.innerHeight / window.innerWidth;
            
            // Redraw with current data
            if (this.options.list && this.options.list.length > 0) {
                WordCloud(this.canvas, this.options);
            }
        });
    }

    addMobileScrollBehavior() {
        function isMobile() {
            return window.innerWidth <= 900 || /Mobi|Android/i.test(navigator.userAgent);
        }

        function scrollToFirstArticle() {
            const main = document.querySelector('main.container');
            if (!main) return;
            
            const articles = main.querySelectorAll('article');
            if (articles.length > 0) {
                articles[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        this.container.addEventListener('click', (e) => {
            if (isMobile()) {
                scrollToFirstArticle();
            }
        });
    }

    static toggleColorScheme() {
        const current = localStorage.getItem('wordCloudColorScheme');
        if (current === 'light') {
            localStorage.setItem('wordCloudColorScheme', 'dark');
            console.log('Word cloud will use dark colors on next render');
        } else {
            localStorage.setItem('wordCloudColorScheme', 'light');
            console.log('Word cloud will use light colors on next render');
        }
        
        // Add a small notification to show the setting was changed
        const notification = document.createElement('div');
        notification.textContent = current === 'light' ? 
            'Word cloud: using dark colors (reload to see change)' : 
            'Word cloud: using light colors (reload to see change)';
        notification.style.position = 'fixed';
        notification.style.bottom = '20px';
        notification.style.left = '20px';
        notification.style.padding = '10px';
        notification.style.background = 'rgba(0,0,0,0.7)';
        notification.style.color = 'white';
        notification.style.borderRadius = '5px';
        notification.style.zIndex = '10000';
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 3000);
        
        return false; // prevent default link behavior
    }

    static addColorToggle(container) {
        // Create toggle container
        const toggleContainer = document.createElement('div');
        toggleContainer.className = 'wordcloud-controls';
        toggleContainer.style.textAlign = 'right';
        toggleContainer.style.marginTop = '10px';
        toggleContainer.style.fontSize = '0.8rem';
        
        // Create toggle link
        const toggle = document.createElement('a');
        toggle.href = '#';
        toggle.style.textDecoration = 'none';
        toggle.style.padding = '4px 8px';
        toggle.style.borderRadius = '4px';
        toggle.style.background = 'rgba(0,0,0,0.1)';
        
        // Set text based on current setting
        const currentScheme = localStorage.getItem('wordCloudColorScheme');
        toggle.textContent = currentScheme === 'light' ? 
            '💡 Using bright colors (click for dark)' : 
            '🔆 Using dark colors (click for bright)';
            
        // Add click handler
        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            WordCloudManager.toggleColorScheme();
            // Reload the page to apply changes
            window.location.reload();
            return false;
        });
        
        // Add to container
        toggleContainer.appendChild(toggle);
        
        // Insert after the word cloud container
        if (container.parentNode) {
            container.parentNode.insertBefore(toggleContainer, container.nextSibling);
        }
    }

    static initialize() {
        const container = document.getElementById('word-cloud-container');
        if (!container) return;
        
        // Check if a custom canvas height is specified
        const canvasHeight = container.hasAttribute('data-canvas-height') 
            ? parseInt(container.getAttribute('data-canvas-height'), 10) 
            : 400;
            
        // Check if the word cloud should be clickable
        const clickable = container.hasAttribute('data-clickable') 
            ? container.getAttribute('data-clickable') === 'true' 
            : true;
        
        // Prioritize dynamic word cloud if data-wordcloud-url is present
        if (container.hasAttribute('data-wordcloud-url')) {
            const apiUrl = container.getAttribute('data-wordcloud-url');
            const wordCloud = new WordCloudManager('word-cloud-container', {}, canvasHeight, clickable);
            wordCloud.startPolling(apiUrl);
            wordCloud.makeResponsive();
            wordCloud.addMobileScrollBehavior();
        } else if (container.hasAttribute('data-words')) {
            const wordCloudData = JSON.parse(container.getAttribute('data-words'));
            const wordCloud = new WordCloudManager('word-cloud-container', {}, canvasHeight, clickable);
            wordCloud.loadStaticData(wordCloudData);
            wordCloud.makeResponsive();
            wordCloud.addMobileScrollBehavior();
        }
    }
}

document.addEventListener('DOMContentLoaded', WordCloudManager.initialize);
