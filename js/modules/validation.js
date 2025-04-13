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

import constants from './constants.js';
import translations from './translations.js';
import IBANValidation from './iban-validation.js';

/**
 * Validates a single field
 * @param {string} fieldId - The ID of the field to validate
 * @param {string} value - The value to validate
 * @returns {boolean} - Whether the field is valid
 */
function validateField(fieldId, value) {
    const field = document.getElementById(fieldId);
    if (!field) {
        console.error('Field not found:', fieldId);
        return false;
    }

    let isValid = false;

    switch (fieldId) {
        case 'beneficiary_name':
            // Consider disabled fields valid
            isValid = field.disabled || (value && value.trim().length >= 2);
            break;

        case 'beneficiary_iban':
            // Consider disabled fields valid
            // Always remove spaces for validation regardless of where it's called from
            const cleanIban = value ? value.replace(/\s/g, '').toUpperCase() : '';
            isValid = field.disabled || (cleanIban && IBANValidation.validateIBAN(cleanIban));
            break;

        case 'amount':
            isValid = value && /^\d+(\.\d{0,2})?$/.test(value) && parseFloat(value) > 0;
            break;

        case 'communication':
            // Communication is optional, so empty is valid
            isValid = !value || /^[A-Za-z0-9\s\-_.,]+$/.test(value);
            break;

        default:
            console.error('Unknown field ID:', fieldId);
            return false;
    }

    field.setAttribute('aria-invalid', !isValid);
    
    // Remove any custom classes
    field.classList.remove('is-valid', 'is-invalid');

    // Also toggle on the parent container for better CSS targeting
    if (field.parentNode.classList.contains('input-container')) {
        field.parentNode.classList.toggle('has-valid', isValid);
        field.parentNode.classList.toggle('has-invalid', !isValid);
    }

    return isValid;
}

/**
 * Validates all required fields in the form
 * @param {Object} inputs - Object containing form input elements
 * @returns {boolean} - Whether all required fields are valid
 */
function validateAllFields(inputs) {
    const requiredFields = ['beneficiary_name', 'beneficiary_iban', 'amount'];
    let allValid = true;

    for (let fieldId of requiredFields) {
        const field = inputs[fieldId];
        if (!field) {
            console.error('Required field not found:', fieldId);
            return false;
        }

        // For disabled fields, we consider them valid but still need their values
        const isValid = field.disabled || validateField(fieldId, field.value);
        if (!isValid) {
            console.log(`Field ${fieldId} is invalid with value:`, field.value);
            allValid = false;
        }
    }

    // Communication is optional, but if present, must be valid
    if (inputs.communication && inputs.communication.value) {
        const isValid = validateField('communication', inputs.communication.value);
        if (!isValid) {
            allValid = false;
        }
    }

    return allValid;
}

const validationModule = {
    validateField,
    validateAllFields
};

export default validationModule;
