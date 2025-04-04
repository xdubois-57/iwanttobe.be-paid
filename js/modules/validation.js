import constants from './constants.js';

/**
 * Validates a form field based on its ID and value
 * @param {string} fieldId - The ID of the field to validate
 * @param {string} value - The value to validate
 * @returns {boolean} - Whether the field is valid
 */
function validateField(fieldId, value) {
    const field = document.getElementById(fieldId);
    if (!field) return false;

    const pattern = constants.VALIDATION_PATTERNS[fieldId];
    if (!pattern) return true; // No pattern means no validation required

    const isValid = pattern.test(value);
    field.classList.toggle('is-invalid', !isValid);
    
    // Special handling for amount field
    if (fieldId === 'amount' && isValid) {
        const numValue = parseFloat(value);
        if (isNaN(numValue) || numValue <= 0) {
            field.classList.add('is-invalid');
            return false;
        }
    }

    return isValid;
}

/**
 * Validates all form fields
 * @param {Object} inputs - Object containing input elements
 * @returns {boolean} - Whether all required fields are valid
 */
function validateAllFields(inputs) {
    let allValid = true;
    for (let inputId in inputs) {
        const value = inputs[inputId].value;
        if (!validateField(inputId, value)) {
            if (inputId !== 'communication') { // Don't fail validation for optional field
                allValid = false;
                break;
            }
        }
    }
    return allValid;
}

export default {
    validateField,
    validateAllFields
};
