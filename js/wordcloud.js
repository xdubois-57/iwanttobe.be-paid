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

document.addEventListener('DOMContentLoaded', function() {
    const wordCloudContainer = document.getElementById('word-cloud-container');
    if (!wordCloudContainer) return;

    // Get word cloud data from the container's data attribute
    const wordCloudData = JSON.parse(wordCloudContainer.getAttribute('data-words'));
    
    if (!wordCloudData || !wordCloudData.length) {
        console.error('No word cloud data found');
        return;
    }
    
    // Calculate available height (viewport height minus other elements)
    function calculateAvailableHeight() {
        // Simply use 70% of viewport height
        return window.innerHeight * 0.5;
    }
    
    // Create canvas element with full container width
    const canvas = document.createElement('canvas');
    const containerWidth = wordCloudContainer.offsetWidth;
    canvas.width = containerWidth;
    
    // Set height to fill available screen space
    canvas.height = calculateAvailableHeight();
    wordCloudContainer.appendChild(canvas);
    
    // Default options
    const options = {
        list: wordCloudData,
        gridSize: 2, // Much smaller grid for maximum word density
        weightFactor: function(size) {
            // Dramatically increase word size
            const area = canvas.width * canvas.height;
            return Math.pow(size, 1.2) * Math.sqrt(area) / 200;
        },
        fontFamily: 'Arial, sans-serif',
        fontWeight: 'bold', // Make all words bold
        color: 'random-dark', // Colors words in a dark palette
        rotateRatio: 0.1, // Minimal rotation for better space usage
        rotationSteps: 2, // Number of different rotation steps
        backgroundColor: 'transparent',
        shape: 'rectangle', // Rectangle shape fits better with screen width
        ellipticity: 0.5, // Match canvas proportions
        shrinkToFit: true, // Don't shrink - fill the canvas
        drawOutOfBound: false,
        classes: function(word) {
            // Add classes for app names to style them differently
            return ['Paid!', 'Drive', 'Involved!'].includes(word) ? 'app-name' : '';
        }
    };
    
    // Generate the word cloud
    WordCloud(canvas, options);
    
    // Make it responsive
    window.addEventListener('resize', function() {
        const newWidth = wordCloudContainer.offsetWidth;
        const newHeight = calculateAvailableHeight();
        
        // Only redraw if dimensions changed significantly
        if (Math.abs(canvas.width - newWidth) > 10 || Math.abs(canvas.height - newHeight) > 10) {
            canvas.width = newWidth;
            canvas.height = newHeight;
            
            // Update ellipticity based on new dimensions
            options.ellipticity = newWidth / newHeight;
            
            // Update weight factor based on new dimensions
            options.weightFactor = function(size) {
                const area = newWidth * newHeight;
                return Math.pow(size, 1.2) * Math.sqrt(area) / 200;
            };
            
            WordCloud(canvas, options);
        }
    });
});
