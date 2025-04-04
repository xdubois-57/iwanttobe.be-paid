/**
 * Generates a QR code based on form data
 * @param {HTMLFormElement} form - The form containing the data
 * @param {HTMLButtonElement} submitButton - The submit button
 * @param {string} submitButtonOriginalText - The original text of the submit button
 * @returns {Promise} - Resolves when QR code is generated
 */
export function generateQRCode(form, submitButton, submitButtonOriginalText) {
    console.log('generateQRCode called');
    const formData = new FormData(form);
    submitButton.textContent = t('generating');
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
        console.log('QR code generated successfully');
        if (data.success) {
            const qrDisplay = document.getElementById('qr-display');
            const qrImage = document.getElementById('qr-image');
            const qrDownload = document.getElementById('qr-download');
            const qrShare = document.getElementById('qr-share');
            const supportQr = document.getElementById('support-qr');

            qrImage.src = data.qr_code;
            qrDownload.href = data.qr_code;
            qrShare.style.display = 'block';
            qrDisplay.style.display = 'block';
            supportQr.style.display = 'none';
            return true;
        } else {
            throw new Error(data.error || t('error_generating_qr'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || t('error_generating_qr'));
        throw error;
    })
    .finally(() => {
        console.log('QR generation completed, resetting button');
        submitButton.textContent = submitButtonOriginalText;
        submitButton.disabled = false;
    });
}
