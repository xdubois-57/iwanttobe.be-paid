import validation from './validation.js';
import translations from './translations.js';

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
        // Get form data
        const formData = new FormData(form);

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
            const qrContainer = document.getElementById('qr-code-container');
            if (!qrContainer) {
                console.error('QR code container not found');
                throw new Error(translations.translate('qr_generation_failed'));
            }

            // Check if we have QR code data in the response
            if (!data.qr_code) {
                console.error('No QR code in response:', data);
                throw new Error(translations.translate('qr_generation_failed'));
            }

            qrContainer.innerHTML = data.qr_code;
            qrContainer.style.display = 'block';
        } else {
            throw new Error(data.message || translations.translate('qr_generation_failed'));
        }
    } catch (error) {
        console.error('Error generating QR code:', error);
        alert(translations.translate('qr_generation_failed'));
    } finally {
        // Reset button state
        submitButton.textContent = submitButtonOriginalText;
        submitButton.disabled = false;
    }
}

const qrGenerator = {
    generateQRCode
};

export default qrGenerator;
