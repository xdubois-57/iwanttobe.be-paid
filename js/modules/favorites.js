import constants from './constants.js';
import validation from './validation.js';
import qrGenerator from './qr-generator.js';
import translations from './translations.js';

/**
 * Find the index of a favorite with matching name and IBAN
 * @param {Array} favorites - List of favorites
 * @param {Object} newFavorite - New favorite to check
 * @param {number} excludeIndex - Index to exclude from the search (for updates)
 * @returns {number} - Index of matching favorite or -1 if not found
 */
function findMatchingFavorite(favorites, newFavorite, excludeIndex = -1) {
    return favorites.findIndex((favorite, index) => 
        index !== excludeIndex &&
        favorite.beneficiary_name === newFavorite.beneficiary_name && 
        favorite.beneficiary_iban === newFavorite.beneficiary_iban
    );
}

/**
 * Loads favorites into the select dropdown
 * @param {HTMLSelectElement} favoritesSelect - The favorites select element
 */
function loadFavorites(favoritesSelect) {
    const favorites = JSON.parse(localStorage.getItem(constants.FAVORITES_KEY) || '[]');
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
function loadFavorite(params) {
    const {
        favoritesSelect,
        inputs,
        amountField,
        form,
        submitButton,
        submitButtonOriginalText,
        saveButton,
        updateButtonText,
        deleteButton
    } = params;

    console.log('loadFavorite called');
    const selectedIndex = favoritesSelect.value;
    if (!selectedIndex) {
        saveButton.textContent = saveButton.dataset.saveText || saveButton.textContent;
        deleteButton.disabled = true;
        return;
    }

    try {
        const favorites = JSON.parse(localStorage.getItem(constants.FAVORITES_KEY) || '[]');
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
            if (validation.validateAllFields(inputs)) {
                console.log('All fields valid, generating QR code');
                qrGenerator.generateQRCode(form, submitButton, submitButtonOriginalText).catch(() => {
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
function saveFavorite(params) {
    const {
        form,
        favoritesSelect,
        inputs,
        saveButton,
        saveButtonOriginalText,
        deleteButton
    } = params;

    const favorites = JSON.parse(localStorage.getItem(constants.FAVORITES_KEY) || '[]');
    const selectedIndex = parseInt(favoritesSelect.value) || -1;
    
    const newFavorite = {
        beneficiary_name: inputs.beneficiary_name.value,
        beneficiary_iban: inputs.beneficiary_iban.value,
        amount: inputs.amount.value,
        communication: inputs.communication.value
    };

    // Check for existing favorite with same name and IBAN
    const existingIndex = findMatchingFavorite(favorites, newFavorite, selectedIndex);
    
    if (existingIndex !== -1) {
        // Found duplicate but not updating the same favorite
        if (!confirm(translations.translate('favorite_exists_confirm_update'))) {
            return;
        }
        // Update existing favorite
        favorites[existingIndex] = newFavorite;
        favoritesSelect.value = existingIndex;
    } else if (selectedIndex !== -1) {
        // Update selected favorite
        favorites[selectedIndex] = newFavorite;
    } else {
        // Add new favorite
        favorites.push(newFavorite);
    }

    localStorage.setItem(constants.FAVORITES_KEY, JSON.stringify(favorites));
    loadFavorites(favoritesSelect);
    
    // Select the newly added/updated favorite
    if (existingIndex !== -1) {
        favoritesSelect.value = existingIndex;
    } else if (selectedIndex !== -1) {
        favoritesSelect.value = selectedIndex;
    } else {
        favoritesSelect.value = (favorites.length - 1).toString();
    }
    
    // Update UI state
    saveButton.textContent = saveButton.dataset.saveText || saveButtonOriginalText;
    deleteButton.disabled = false;
}

/**
 * Deletes the currently selected favorite
 * @param {Object} params - Parameters for deleting a favorite
 */
function deleteFavorite(params) {
    const {
        favoritesSelect,
        saveButton,
        saveButtonOriginalText,
        deleteButton
    } = params;

    const selectedIndex = favoritesSelect.value;
    if (!selectedIndex || selectedIndex === '0') return;

    const favorites = JSON.parse(localStorage.getItem(constants.FAVORITES_KEY) || '[]');
    favorites.splice(selectedIndex, 1);
    localStorage.setItem(constants.FAVORITES_KEY, JSON.stringify(favorites));
    
    loadFavorites(favoritesSelect);
    favoritesSelect.value = '0';
    
    // Update UI state
    saveButton.textContent = saveButton.dataset.saveText || saveButtonOriginalText;
    deleteButton.disabled = true;
}

const favoritesModule = {
    loadFavorites,
    loadFavorite,
    saveFavorite,
    deleteFavorite
};

export default favoritesModule;
