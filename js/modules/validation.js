import constants from './constants.js';
import translations from './translations.js';

/**
 * Validates a single field
 * @param {string} fieldId - The ID of the field to validate
 * @param {string} value - The value to validate
 * @returns {boolean} - Whether the field is valid
 */
function validateField(fieldId, value) {
    const field = document.getElementById(fieldId);
    if (!field) return false;

    // Skip validation for disabled fields (they are considered valid)
    if (field.disabled) {
        const validationIndicator = field.nextElementSibling;
        if (validationIndicator && validationIndicator.classList.contains('validation-indicator')) {
            validationIndicator.textContent = '✓';
            field.classList.add('is-valid');
            field.classList.remove('is-invalid');
        }
        return true;
    }

    const validationIndicator = field.nextElementSibling;
    if (!validationIndicator || !validationIndicator.classList.contains('validation-indicator')) {
        console.error('No validation indicator found for field:', fieldId);
        return false;
    }

    let isValid = false;

    switch (fieldId) {
        case 'beneficiary_name':
            isValid = value && value.trim().length >= 2;
            validationIndicator.textContent = isValid ? '✓' : translations.translate('invalid_name');
            break;

        case 'beneficiary_iban':
            isValid = value && /^[A-Z]{2}[0-9]{2}[A-Z0-9]{4}[0-9]{7}([A-Z0-9]?){0,16}$/.test(value.replace(/\s/g, ''));
            validationIndicator.textContent = isValid ? '✓' : translations.translate('invalid_iban');
            break;

        case 'amount':
            isValid = value && /^\d+(\.\d{0,2})?$/.test(value) && parseFloat(value) > 0;
            validationIndicator.textContent = isValid ? '✓' : translations.translate('invalid_amount');
            break;

        case 'communication':
            // Communication is optional, so empty is valid
            isValid = !value || /^[A-Za-z0-9\s\-_.,]+$/.test(value);
            validationIndicator.textContent = isValid ? (value ? '✓' : '') : translations.translate('invalid_communication');
            break;

        default:
            console.error('Unknown field ID:', fieldId);
            return false;
    }

    field.classList.toggle('is-valid', isValid);
    field.classList.toggle('is-invalid', !isValid);

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
