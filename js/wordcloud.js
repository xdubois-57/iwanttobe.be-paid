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
        this.options = this.getDefaultOptions(options);
        this.userCanvasHeight = canvasHeight;
        this.initializeCanvas();
    }

    initializeCanvas() {
        // Create canvas element with full container width
        this.canvas = document.createElement('canvas');
        const containerWidth = this.container.offsetWidth;
        this.canvas.width = containerWidth;
        
        // Set height to user-provided value or fill available space
        this.canvas.height = this.userCanvasHeight !== null ? this.userCanvasHeight : this.calculateAvailableHeight();
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
            weightFactor: (size) => {
                // Get canvas dimensions from the WordCloudManager instance
                const area = this.container.offsetWidth * this.calculateAvailableHeight();
                return Math.pow(size, 1.3) * Math.sqrt(area) / 120;
            },
            fontFamily: 'Arial, sans-serif',
            fontWeight: 'bold', // Make all words bold
            color: 'random-dark', // Colors words in a dark palette
            rotateRatio: 0.1, // Minimal rotation for better space usage
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

        // Check if data has changed
        const dataChanged = !this.previousData || 
            JSON.stringify(data) !== JSON.stringify(this.previousData);
            
        if (!dataChanged) {
            return; // Skip redraw if data hasn't changed
        }

        // Store current data for future comparison
        this.previousData = JSON.parse(JSON.stringify(data));
        
        // Update options with new data
        this.options.list = data;
        // Generate the word cloud
        WordCloud(this.canvas, this.options);
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
