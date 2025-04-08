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
            isValid = field.disabled || (value && /^[A-Z]{2}[0-9]{2}[A-Z0-9]{4}[0-9]{7}([A-Z0-9]?){0,16}$/.test(value.replace(/\s/g, '')));
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
