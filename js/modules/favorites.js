import { FAVORITES_KEY } from './constants.js';
import { validateAllFields } from './validation.js';
import { generateQRCode } from './qr-generator.js';

/**
 * Loads favorites into the select dropdown
 * @param {HTMLSelectElement} favoritesSelect - The favorites select element
 */
export function loadFavorites(favoritesSelect) {
    const favorites = JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]');
    // Clear existing options except the first one (placeholder)
    while (favoritesSelect.options.length > 1) {
        favoritesSelect.remove(1);
    }
    
    // Add favorites to select
    favorites.forEach((favorite, index) => {
        const option = document.createElement('option');
        option.value = index;
        option.textContent = favorite.beneficiary_name + ' - ' + favorite.beneficiary_iban;
        favoritesSelect.appendChild(option);
    });
}

/**
 * Loads a favorite into the form
 * @param {Object} params - Parameters for loading a favorite
 */
export function loadFavorite({
    favoritesSelect,
    inputs,
    amountField,
    form,
    submitButton,
    submitButtonOriginalText,
    saveButton,
    updateButtonText,
    deleteButton
}) {
    console.log('loadFavorite called');
    const selectedIndex = favoritesSelect.value;
    if (!selectedIndex) {
        saveButton.textContent = saveButtonOriginalText;
        deleteButton.disabled = true;
        return;
    }

    try {
        const favorites = JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]');
        const favorite = favorites[selectedIndex];
        if (favorite) {
            console.log('Loading favorite:', favorite);
            // Set text fields first
            inputs.beneficiary_name.value = favorite.beneficiary_name || '';
            inputs.beneficiary_iban.value = favorite.beneficiary_iban || '';
            inputs.communication.value = favorite.communication || '';

            // Handle amount field specially (convert to number)
            const amount = parseFloat(favorite.amount);
            if (!isNaN(amount)) {
                const formattedAmount = amount.toFixed(2);
                console.log('Setting amount field to:', formattedAmount);
                amountField.value = formattedAmount;
            }

            // Update UI state
            saveButton.textContent = updateButtonText;
            deleteButton.disabled = false;

            // Validate fields and generate QR code
            if (validateAllFields(inputs)) {
                console.log('All fields valid, generating QR code');
                generateQRCode(form, submitButton, submitButtonOriginalText).catch(() => {
                    console.log('QR generation failed in loadFavorite');
                });
            }

            // Force a DOM update and trigger events after setting all values
            setTimeout(() => {
                if (!isNaN(amount)) {
                    const formattedAmount = amount.toFixed(2);
                    console.log('Setting amount field in timeout:', formattedAmount);
                    amountField.value = formattedAmount;
                    amountField.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }, 0);
        }
    } catch (e) {
        console.error('Error loading favorite:', e);
    }
}

/**
 * Saves the current form data as a favorite
 * @param {Object} params - Parameters for saving a favorite
 */
export function saveFavorite({
    form,
    favoritesSelect,
    inputs,
    saveButton,
    saveButtonOriginalText,
    deleteButton
}) {
    const favorites = JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]');
    const selectedIndex = favoritesSelect.value;
    
    const favorite = {
        beneficiary_name: inputs.beneficiary_name.value,
        beneficiary_iban: inputs.beneficiary_iban.value,
        amount: inputs.amount.value,
        communication: inputs.communication.value
    };

    if (selectedIndex && selectedIndex !== '0') {
        // Update existing favorite
        favorites[selectedIndex] = favorite;
    } else {
        // Add new favorite
        favorites.push(favorite);
    }

    localStorage.setItem(FAVORITES_KEY, JSON.stringify(favorites));
    loadFavorites(favoritesSelect);
    
    // Select the newly added/updated favorite
    favoritesSelect.value = selectedIndex || (favorites.length - 1).toString();
    
    // Update UI state
    saveButton.textContent = saveButtonOriginalText;
    deleteButton.disabled = false;
}

/**
 * Deletes the currently selected favorite
 * @param {Object} params - Parameters for deleting a favorite
 */
export function deleteFavorite({
    favoritesSelect,
    saveButton,
    saveButtonOriginalText,
    deleteButton
}) {
    const selectedIndex = favoritesSelect.value;
    if (!selectedIndex || selectedIndex === '0') return;

    const favorites = JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]');
    favorites.splice(selectedIndex, 1);
    localStorage.setItem(FAVORITES_KEY, JSON.stringify(favorites));
    
    loadFavorites(favoritesSelect);
    favoritesSelect.value = '0';
    
    // Update UI state
    saveButton.textContent = saveButtonOriginalText;
    deleteButton.disabled = true;
}
