import translations from './translations.js';

/**
 * Generates a QR code based on form data
 * @param {HTMLFormElement} form - The form containing the data
 * @param {HTMLButtonElement} submitButton - The submit button
 * @param {string} submitButtonOriginalText - The original text of the submit button
 * @returns {Promise} - Resolves when QR code is generated
 */
function generateQRCode(form, submitButton, submitButtonOriginalText) {
    console.log('generateQRCode called');
    const formData = new FormData(form);
    submitButton.textContent = translations.translate('generating');
    submitButton.disabled = true;

    return fetch('/generate-qr', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
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
                throw new Error(translations.translate('error_generating_qr'));
            }

            // Check if we have image data in the response
            const imageUrl = data.qr_code || data.image;
            if (!imageUrl) {
                console.error('No image URL in response:', data);
                throw new Error(translations.translate('error_generating_qr'));
            }

            qrImage.src = imageUrl;
            shareQr.dataset.image = imageUrl;
            userQr.style.display = 'block';
            supportQr.style.display = 'none';
            return true;
        } else {
            throw new Error(data.error || translations.translate('error_generating_qr'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || translations.translate('error_generating_qr'));
        throw error;
    })
    .finally(() => {
        console.log('QR generation completed, resetting button');
        submitButton.textContent = submitButtonOriginalText;
        submitButton.disabled = false;
    });
}

export default { generateQRCode };
