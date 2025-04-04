import constants from './constants.js';

/**
 * Saves form data to session storage
 * @param {HTMLFormElement} form - The form to save
 */
function saveFormData(form) {
    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    sessionStorage.setItem(constants.FORM_DATA_KEY, JSON.stringify(data));
}

/**
 * Loads form data from session storage
 * @param {HTMLFormElement} form - The form to load data into
 */
function loadFormData(form) {
    const data = JSON.parse(sessionStorage.getItem(constants.FORM_DATA_KEY) || '{}');
    for (let key in data) {
        const input = form.elements[key];
        if (input) {
            input.value = data[key];
        }
    }
}

/**
 * Clear form data from session storage
 */
function clearFormData() {
    sessionStorage.removeItem(constants.FORM_DATA_KEY);
}

export default {
    saveFormData,
    loadFormData,
    clearFormData
};
