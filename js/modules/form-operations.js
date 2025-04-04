import formStorage from './form-storage.js';
import validation from './validation.js';

/**
 * Clears the form and resets validation indicators
 * @param {HTMLFormElement} form - The form to clear
 */
function clearForm(form) {
    // Reset form fields
    form.reset();

    // Clear session storage
    formStorage.default.clearFormData();

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
