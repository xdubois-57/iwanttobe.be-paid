import validation from './validation.js';
import translations from './translations.js';

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
        } else {
            throw new Error(data.message || translations.translate('qr_generation_failed'));
        }
    } catch (error) {
        console.error('Error generating QR code:', error);
        alert(translations.translate('qr_generation_failed'));
        // Show support QR on error
        resetRightPanel();
    } finally {
        // Reset button state
        submitButton.textContent = submitButtonOriginalText;
        submitButton.disabled = false;
    }
}

const qrGenerator = {
    generateQRCode,
    resetRightPanel
};

// Make resetRightPanel globally available
window.resetRightPanel = resetRightPanel;

export default qrGenerator;
