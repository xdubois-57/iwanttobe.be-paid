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
    
    if (nameInput) nameInput.disabled = false;
    if (ibanInput) ibanInput.disabled = false;
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
