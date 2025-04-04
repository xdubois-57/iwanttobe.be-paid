import { validateField } from './modules/validation.js';
import { generateQRCode } from './modules/qr-generator.js';
import { loadFavorites, loadFavorite, saveFavorite, deleteFavorite } from './modules/favorites.js';
import { saveFormData, loadFormData } from './modules/form-storage.js';

document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const form = document.querySelector('#transfer-form');
    const submitButton = document.getElementById('generate-qr-button');
    const submitButtonOriginalText = submitButton.textContent;
    const favoritesSelect = document.getElementById('favorites');
    const saveButton = document.getElementById('save-favorite');
    const deleteButton = document.getElementById('delete-favorite');
    const saveButtonOriginalText = saveButton.textContent;
    const updateButtonText = saveButton.getAttribute('data-update-text');
    const amountField = document.getElementById('amount');

    const inputs = {
        beneficiary_name: document.getElementById('beneficiary_name'),
        beneficiary_iban: document.getElementById('beneficiary_iban'),
        amount: amountField,
        communication: document.getElementById('communication')
    };

    // Initialize form
    loadFormData(form);
    loadFavorites(favoritesSelect);

    // Set up event handlers
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        window.generateQRCode();
    });

    // Make generateQRCode globally available
    window.generateQRCode = () => generateQRCode(form, submitButton, submitButtonOriginalText);

    // Add input event listeners for real-time validation
    for (let inputId in inputs) {
        const input = inputs[inputId];
        input.addEventListener('input', function(e) {
            console.log(`Input event on ${inputId}:`, e.target.value);
            validateField(inputId, e.target.value);
        });

        // Initial validation state
        if (inputId !== 'communication') {
            validateField(inputId, input.value);
        }
    }

    // Save form data when modified
    form.addEventListener('change', () => saveFormData(form));

    // Set up favorites handlers
    window.loadFavorite = () => loadFavorite({
        favoritesSelect,
        inputs,
        amountField,
        form,
        submitButton,
        submitButtonOriginalText,
        saveButton,
        updateButtonText,
        deleteButton
    });

    window.saveFavorite = () => saveFavorite({
        form,
        favoritesSelect,
        inputs,
        saveButton,
        saveButtonOriginalText,
        deleteButton
    });

    window.deleteFavorite = () => deleteFavorite({
        favoritesSelect,
        saveButton,
        saveButtonOriginalText,
        deleteButton
    });
});
