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
    // Helper function to get translations
    function t(key) {
        return window.t(key);
    }

    // Check if Web Share API is supported
    if (navigator.share) {
        document.querySelectorAll('[data-share]').forEach(button => {
            button.classList.add('share-supported');
            button.addEventListener('click', async function() {
                try {
                    const image = this.dataset.image || document.querySelector('#qr-image').src;
                    const title = this.dataset.title || 'QR Transfer';
                    
                    // Fetch the image and convert it to a blob
                    const response = await fetch(image);
                    const blob = await response.blob();
                    const file = new File([blob], 'qr-code.png', { type: 'image/png' });

                    await navigator.share({
                        title: title,
                        text: t('share_text'),
                        files: [file]
                    });
                } catch (error) {
                    console.error('Error sharing:', error);
                }
            });
        });
    }

    // Update share button when new QR code is generated
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'src') {
                const shareButton = document.querySelector('#share-qr');
                if (shareButton) {
                    const newSrc = mutation.target.getAttribute('src');
                    shareButton.dataset.image = newSrc;
                }
            }
        });
    });

    const qrImage = document.querySelector('#qr-image');
    if (qrImage) {
        observer.observe(qrImage, {
            attributes: true,
            attributeFilter: ['src']
        });
    }
});
