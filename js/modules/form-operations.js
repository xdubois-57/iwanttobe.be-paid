import validation from './validation.js';

/**
 * Clears the form and resets validation indicators
 * @param {HTMLFormElement} form - The form to clear
 */
function clearForm(form) {
    // Clear selected favorite
    localStorage.removeItem(constants.SELECTED_FAVORITE_KEY);

    // Reset form fields
    form.reset();

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
