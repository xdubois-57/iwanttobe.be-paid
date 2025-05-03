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
    constructor(containerId, options = {}, canvasHeight = null) {
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
        this.initializeCanvas();
        this.isFullScreen = false;
        this.fullScreenOverlay = null;
        this.setupFullScreenToggle();
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
            width: '100%',
            height: '100%',
            zIndex: '10000',
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            backgroundColor: pageBackgroundColor,
            padding: '20px',
            boxSizing: 'border-box'
        });
        
        // Create new canvas for fullscreen
        const fullScreenCanvas = document.createElement('canvas');
        fullScreenCanvas.className = 'word-cloud-fullscreen-canvas';
        
        // Use almost the full viewport size to maximize space
        const width = window.innerWidth - 40; // 20px padding on each side
        const height = window.innerHeight - 40;
        
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
        
        // Add cursor style to indicate it's clickable
        this.canvas.style.cursor = 'pointer';
        
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
            color: 'random-dark', // Colors words in a dark palette
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

    updateWordCloud(data) {
        if (!data || !data.length) {
            console.error('No word cloud data provided');
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

    static initialize() {
        const container = document.getElementById('word-cloud-container');
        if (!container) return;
        // Prioritize dynamic word cloud if data-wordcloud-url is present
        if (container.hasAttribute('data-wordcloud-url')) {
            const apiUrl = container.getAttribute('data-wordcloud-url');
            const wordCloud = new WordCloudManager('word-cloud-container', {}, 400);
            wordCloud.startPolling(apiUrl);
            wordCloud.makeResponsive();
            wordCloud.addMobileScrollBehavior();
        } else if (container.hasAttribute('data-words')) {
            const wordCloudData = JSON.parse(container.getAttribute('data-words'));
            const wordCloud = new WordCloudManager('word-cloud-container', {}, 400);
            wordCloud.loadStaticData(wordCloudData);
            wordCloud.makeResponsive();
            wordCloud.addMobileScrollBehavior();
        }
    }
}

document.addEventListener('DOMContentLoaded', WordCloudManager.initialize);
