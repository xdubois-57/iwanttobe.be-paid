import constants from './constants.js';

/**
 * Saves form data to session storage
 * @param {HTMLFormElement} form - The form to save
 */
function saveFormData(form) {
    const formData = {};
    for (let input of form.elements) {
        if (input.name) {
            formData[input.name] = input.value;
        }
    }
    sessionStorage.setItem(constants.STORAGE_KEY, JSON.stringify(formData));
}

/**
 * Loads form data from session storage
 * @param {HTMLFormElement} form - The form to load data into
 */
function loadFormData(form) {
    const savedData = sessionStorage.getItem(constants.STORAGE_KEY);
    if (savedData) {
        const formData = JSON.parse(savedData);
        for (let input of form.elements) {
            if (input.name && formData[input.name]) {
                input.value = formData[input.name];
            }
        }
    }
}

export default {
    saveFormData,
    loadFormData
};
