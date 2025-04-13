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

import validation from './validation.js';
import translations from './translations.js';

// Store the last generated QR code values
let lastGeneratedValues = null;

// Flag to track if a QR code generation is in progress
let isGeneratingQR = false;

/**
 * Gets normalized form values as a string for comparison
 * @param {Object} inputs - Object containing form input elements
 * @returns {string} - Normalized string representation of form values
 */
function getFormValuesString(inputs) {
    return Object.entries(inputs)
        .map(([key, input]) => {
            // Normalize the value: trim and remove extra spaces
            const value = input.value.trim().replace(/\s+/g, '');
            return `${key}:${value}`;
        })
        .join('|');
}

/**
 * Checks if current form values match the last generated values
 * @param {Object} inputs - Object containing form input elements
 * @returns {boolean} - True if values match, false otherwise
 */
function formValuesMatchLastGenerated(inputs) {
    if (!lastGeneratedValues) return false;
    const currentValues = getFormValuesString(inputs);
    console.log('Comparing form values:', {
        current: currentValues,
        last: lastGeneratedValues,
        match: currentValues === lastGeneratedValues
    });
    return currentValues === lastGeneratedValues;
}

/**
 * Updates button state based on form values
 * @param {Object} inputs - Object containing form input elements
 * @param {HTMLButtonElement} button - The generate button
 */
function updateButtonState(inputs, button) {
    const shouldDisable = formValuesMatchLastGenerated(inputs);
    button.disabled = shouldDisable;
    console.log('Button state updated:', {
        shouldDisable,
        isDisabled: button.disabled,
        buttonText: button.textContent
    });
}

/**
 * Resets the right panel to show support QR
 */
function resetRightPanel() {
    const supportQr = document.getElementById('support-qr');
    const userQr = document.getElementById('user-qr');
    if (supportQr && userQr) {
        userQr.style.display = 'none';
        supportQr.style.display = 'block';
    }
}

/**
 * Creates FormData including disabled fields
 * @param {HTMLFormElement} form - The form element
 * @param {Object} inputs - Object containing form input elements
 * @returns {FormData} - FormData with all field values
 */
function createFormData(form, inputs) {
    const formData = new FormData();
    
    // Add all input values, including disabled fields
    for (const [key, input] of Object.entries(inputs)) {
        formData.append(key, input.value);
    }
    
    return formData;
}

/**
 * Generates a QR code based on form data
 * @param {HTMLFormElement} form - The form element
 * @param {HTMLButtonElement} submitButton - The submit button
 * @param {string} submitButtonOriginalText - The original text of the submit button
 * @param {boolean} [trustedEvent] - Whether the event triggering this generation is trusted
 * @returns {Promise<void>} - A promise that resolves when the QR code is generated
 */
async function generateQRCode(form, submitButton, submitButtonOriginalText, trustedEvent = false) {
    // Prevent multiple concurrent requests
    if (isGeneratingQR) {
        console.log('QR code generation already in progress');
        return;
    }
    isGeneratingQR = true;

    try {
        console.log('Starting QR code generation');
        console.log('Generating QR code', { trustedEvent });
        const inputs = {
            beneficiary_name: document.getElementById('beneficiary_name'),
            beneficiary_iban: document.getElementById('beneficiary_iban'),
            amount: document.getElementById('amount'),
            communication: document.getElementById('communication')
        };

        // Validate all fields
        if (!validation.validateAllFields(inputs)) {
            console.warn(translations.translate('missing_required_fields'));
            return;
        }

        // Update button state
        submitButton.textContent = translations.translate('generating');
        submitButton.disabled = true;

        // Get form data including disabled fields
        const formData = createFormData(form, inputs);

        // Make the AJAX request
        const response = await fetch('/generate-qr', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('QR code response:', data);
        
        if (data.success) {
            const supportQr = document.getElementById('support-qr');
            const userQr = document.getElementById('user-qr');
            const qrImage = document.getElementById('qr-image');
            const shareQr = document.getElementById('share-qr');

            if (!qrImage || !userQr || !supportQr || !shareQr) {
                console.error('Missing QR code elements:', {
                    userQr: !!userQr,
                    qrImage: !!qrImage,
                    supportQr: !!supportQr,
                    shareQr: !!shareQr
                });
                throw new Error(translations.translate('qr_generation_failed'));
            }

            // Check if we have image data in the response
            const imageUrl = data.image || data.qr_code;
            if (!imageUrl) {
                console.error('No image URL in response:', data);
                throw new Error(translations.translate('qr_generation_failed'));
            }

            qrImage.src = imageUrl;
            shareQr.dataset.image = imageUrl;
            userQr.style.display = 'block';
            supportQr.style.display = 'none';

            // Store the current values as last generated
            lastGeneratedValues = getFormValuesString(inputs);
            console.log('Stored last generated values:', lastGeneratedValues);
            
            // Enable button after successful generation
            submitButton.disabled = false;
            submitButton.textContent = submitButtonOriginalText;

            // Scroll to QR code on mobile mode only if the event is trusted
            if (trustedEvent) {
                const qrContainer = document.getElementById('user-qr');
                if (qrContainer) {
                    const isMobile = window.innerWidth < 768; // Using 768px as the breakpoint for mobile
                    if (isMobile) {
                        qrContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            }
        } else {
            throw new Error(data.message || data.error || translations.translate('qr_generation_failed'));
        }
    } catch (error) {
        console.error('Error generating QR code:', error);
        console.error(translations.translate('qr_generation_failed'));
        // Show support QR on error
        resetRightPanel();
        // Reset last generated values and enable button on error
        lastGeneratedValues = null;
        submitButton.disabled = false;
    } finally {
        // Always reset the state, even if there's an error
        isGeneratingQR = false;
        console.log('QR code generation completed');
        // Reset button text
        // submitButton.textContent = submitButtonOriginalText;
    }
}

/**
 * Initializes form input event listeners
 */
function initializeFormListeners() {
    const form = document.getElementById('transfer-form');
    const generateButton = document.getElementById('generate-qr-button');
    if (!form || !generateButton) {
        console.error('Missing form or generate button');
        return;
    }

    const inputs = {
        beneficiary_name: document.getElementById('beneficiary_name'),
        beneficiary_iban: document.getElementById('beneficiary_iban'),
        amount: document.getElementById('amount'),
        communication: document.getElementById('communication')
    };

    // Function to handle any input change
    const handleChange = (event) => {
        console.log('Form value changed:', event.target.id, event.target.value);
        updateButtonState(inputs, generateButton);
    };

    // Add input event listeners to all form fields
    Object.values(inputs).forEach(input => {
        if (input) {
            input.addEventListener('input', handleChange);
        }
    });

    // Handle form submit
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        console.log('Form submitted', { isTrusted: event.isTrusted });
        await generateQRCode(form, generateButton, generateButton.textContent, event.isTrusted);
    });

    // Initial button state
    console.log('Initializing button state');
    updateButtonState(inputs, generateButton);
}

const qrGenerator = {
    generateQRCode,
    resetRightPanel,
    initializeFormListeners,
    updateButtonState,
    isGeneratingQR
};

// Make resetRightPanel globally available
window.resetRightPanel = resetRightPanel;

// Initialize form listeners when the module is loaded
document.addEventListener('DOMContentLoaded', initializeFormListeners);

export default qrGenerator;
