import validation from './validation.js';
import translations from './translations.js';

// Store the last generated QR code values
let lastGeneratedValues = null;

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
 * Gets current form values as a string for comparison
 * @param {Object} inputs - Object containing form input elements
 * @returns {string} - String representation of form values
 */
function getFormValuesString(inputs) {
    return Object.entries(inputs)
        .map(([key, input]) => `${key}:${input.value}`)
        .join('|');
}

/**
 * Updates the generate button state based on form values
 * @param {Object} inputs - Object containing form input elements
 * @param {HTMLButtonElement} generateButton - The generate button element
 */
function updateGenerateButtonState(inputs, generateButton) {
    if (!generateButton) return;

    // If there are no last generated values, enable the button
    if (!lastGeneratedValues) {
        generateButton.disabled = false;
        return;
    }

    const currentValues = getFormValuesString(inputs);
    // Enable button if current values are different from last generated values
    generateButton.disabled = currentValues === lastGeneratedValues;
}

/**
 * Generates a QR code based on form data
 * @param {HTMLFormElement} form - The form element
 * @param {HTMLButtonElement} submitButton - The submit button
 * @param {string} submitButtonOriginalText - The original text of the submit button
 * @returns {Promise<void>} - A promise that resolves when the QR code is generated
 */
async function generateQRCode(form, submitButton, submitButtonOriginalText) {
    const inputs = {
        beneficiary_name: document.getElementById('beneficiary_name'),
        beneficiary_iban: document.getElementById('beneficiary_iban'),
        amount: document.getElementById('amount'),
        communication: document.getElementById('communication')
    };

    // Validate all fields
    if (!validation.validateAllFields(inputs)) {
        alert(translations.translate('missing_required_fields'));
        return;
    }

    // Update button state
    submitButton.textContent = translations.translate('generating');
    submitButton.disabled = true;

    try {
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
            // Update button state after successful generation
            updateGenerateButtonState(inputs, submitButton);
        } else {
            throw new Error(data.message || data.error || translations.translate('qr_generation_failed'));
        }
    } catch (error) {
        console.error('Error generating QR code:', error);
        alert(translations.translate('qr_generation_failed'));
        // Show support QR on error
        resetRightPanel();
        // Reset last generated values on error
        lastGeneratedValues = null;
    } finally {
        // Reset button state
        submitButton.textContent = submitButtonOriginalText;
        // Update button state
        updateGenerateButtonState(inputs, submitButton);
    }
}

/**
 * Initializes form input event listeners
 */
function initializeFormListeners() {
    const form = document.getElementById('transfer-form');
    const generateButton = document.getElementById('generate-qr-button');
    if (!form || !generateButton) return;

    const inputs = {
        beneficiary_name: document.getElementById('beneficiary_name'),
        beneficiary_iban: document.getElementById('beneficiary_iban'),
        amount: document.getElementById('amount'),
        communication: document.getElementById('communication')
    };

    // Add input event listeners to all form fields
    Object.values(inputs).forEach(input => {
        if (input) {
            ['input', 'change'].forEach(eventType => {
                input.addEventListener(eventType, () => {
                    updateGenerateButtonState(inputs, generateButton);
                });
            });
        }
    });

    // Reset last generated values when form is cleared
    document.getElementById('clear-form')?.addEventListener('click', () => {
        lastGeneratedValues = null;
        updateGenerateButtonState(inputs, generateButton);
    });
}

const qrGenerator = {
    generateQRCode,
    resetRightPanel,
    initializeFormListeners
};

// Make resetRightPanel globally available
window.resetRightPanel = resetRightPanel;

// Initialize form listeners when the module is loaded
document.addEventListener('DOMContentLoaded', initializeFormListeners);

export default qrGenerator;
