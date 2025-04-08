import validation from './validation.js';
import constants from './constants.js';

/**
 * Clears the form and resets validation indicators
 * @param {HTMLFormElement} form - The form to clear
 */
function clearForm(form) {
    // Clear selected favorite
    localStorage.removeItem(constants.SELECTED_FAVORITE_KEY);

    // Reset form fields
    form.reset();
    
    const nameInput = form.querySelector('#beneficiary_name');
    const ibanInput = form.querySelector('#beneficiary_iban');
    const saveButton = form.querySelector('#save-favorite');
    
    // Set inputs to readonly
    if (nameInput) nameInput.readOnly = false;
    if (ibanInput) ibanInput.readOnly = false;

    // Keep buttons disabled
    if (saveButton) saveButton.disabled = true;

    // Reset validation indicators
    const inputs = form.querySelectorAll('input');
    inputs.forEach(input => {
        const indicator = input.nextElementSibling;
        if (indicator && indicator.classList.contains('validation-indicator')) {
            indicator.textContent = '';
        }
    });
}

export default { clearForm };
