import formOperations from './form-operations.js';
import translations from './translations.js';

const FAVORITES_KEY = 'qr_transfer_favorites';

/**
 * Check if a favorite exists with given name and IBAN
 * @param {string} name - Beneficiary name
 * @param {string} iban - Beneficiary IBAN
 * @returns {number} Index of the favorite if found, -1 otherwise
 */
function findFavoriteIndex(name, iban) {
    if (!name || !iban) return -1;
    const favorites = JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]');
    return favorites.findIndex(f => 
        f.beneficiary_name === name && f.beneficiary_iban === iban
    );
}

/**
 * Update the save button text based on whether a favorite exists
 * @param {HTMLButtonElement} saveButton - The save button element
 * @param {string} name - Beneficiary name
 * @param {string} iban - Beneficiary IBAN
 */
function updateSaveButtonText(saveButton, name, iban) {
    const existingIndex = findFavoriteIndex(name, iban);
    saveButton.textContent = existingIndex !== -1 ? translations.translate('update_favorite') : translations.translate('save_favorite');
}

/**
 * Save current form data as a favorite
 * @param {Object} inputs - Form input elements
 * @param {HTMLSelectElement} favoritesSelect - Favorites select element
 * @param {HTMLButtonElement} saveButton - Save button element
 * @param {HTMLButtonElement} deleteButton - Delete button element
 */
function saveFavorite(inputs, favoritesSelect, saveButton, deleteButton) {
    const name = inputs.beneficiary_name.value.trim();
    const iban = inputs.beneficiary_iban.value.trim();
    const amount = inputs.amount.value.trim();
    const communication = inputs.communication.value.trim();

    if (!name || !iban) {
        alert(translations.translate('fill_required_fields'));
        return;
    }

    let favorites = JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]');
    const selectedIndex = favoritesSelect.value;

    // Check for duplicates
    const existingIndex = findFavoriteIndex(name, iban);

    if (existingIndex !== -1 && existingIndex !== parseInt(selectedIndex)) {
        const shouldUpdate = confirm(translations.translate('favorite_exists_update'));
        if (!shouldUpdate) {
            return;
        }
        // Remove the existing favorite if we're going to update it
        favorites.splice(existingIndex, 1);
    }

    const favorite = {
        beneficiary_name: name,
        beneficiary_iban: iban,
        amount: amount,
        communication: communication
    };

    if (selectedIndex !== '') {
        // Update existing favorite
        favorites[selectedIndex] = favorite;
    } else {
        // Add new favorite
        favorites.push(favorite);
    }

    localStorage.setItem(FAVORITES_KEY, JSON.stringify(favorites));
    loadFavorites(favoritesSelect);

    // Select the saved favorite
    const newIndex = selectedIndex !== '' ? selectedIndex : (favorites.length - 1).toString();
    favoritesSelect.value = newIndex;

    // Update UI state
    inputs.beneficiary_name.disabled = true;
    inputs.beneficiary_iban.disabled = true;
    deleteButton.disabled = false;
    saveButton.textContent = translations.translate('update_favorite');
}

/**
 * Loads favorites from storage and populates the select
 * @param {HTMLSelectElement} [favoritesSelect] - Optional select element, will query if not provided
 */
function loadFavorites(favoritesSelect) {
    favoritesSelect = favoritesSelect || document.getElementById('favorites');
    if (!favoritesSelect) {
        console.error('Favorites select not found');
        return;
    }

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
 * Loads a selected favorite into the form
 */
function loadFavorite() {
    console.log('loadFavorite called');
    const favoritesSelect = document.getElementById('favorites');
    const deleteButton = document.getElementById('delete-favorite');
    const saveButton = document.getElementById('save-favorite');
    const nameInput = document.getElementById('beneficiary_name');
    const ibanInput = document.getElementById('beneficiary_iban');
    const amountInput = document.getElementById('amount');
    const communicationInput = document.getElementById('communication');
    const form = document.getElementById('transfer-form');

    if (!favoritesSelect || !deleteButton || !saveButton || !nameInput || !ibanInput || !form) {
        console.error('Missing required elements for loading favorite');
        return;
    }

    const selectedIndex = favoritesSelect.value;
    const favorites = JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]');

    if (selectedIndex === '') {
        // No favorite selected, enable inputs and reset buttons
        console.log('No favorite selected, enabling inputs');
        nameInput.disabled = false;
        ibanInput.disabled = false;
        deleteButton.disabled = true;
        updateSaveButtonText(saveButton, nameInput.value.trim(), ibanInput.value.trim());
        
        // Trigger validation on enabled fields
        nameInput.dispatchEvent(new Event('input', { bubbles: true }));
        ibanInput.dispatchEvent(new Event('input', { bubbles: true }));
        return;
    }

    const favorite = favorites[selectedIndex];
    if (!favorite) {
        console.error('Selected favorite not found:', selectedIndex);
        return;
    }

    // Load favorite data
    console.log('Loading favorite:', favorite);
    nameInput.value = favorite.beneficiary_name;
    ibanInput.value = favorite.beneficiary_iban;
    amountInput.value = favorite.amount || '';
    communicationInput.value = favorite.communication || '';

    // Disable inputs when a favorite is selected
    nameInput.disabled = true;
    ibanInput.disabled = true;
    deleteButton.disabled = false;
    updateSaveButtonText(saveButton, nameInput.value.trim(), ibanInput.value.trim());

    // Trigger validation and change events on fields
    nameInput.dispatchEvent(new Event('input', { bubbles: true }));
    ibanInput.dispatchEvent(new Event('input', { bubbles: true }));
    amountInput.dispatchEvent(new Event('input', { bubbles: true }));
    communicationInput.dispatchEvent(new Event('input', { bubbles: true }));
    nameInput.dispatchEvent(new Event('change', { bubbles: true }));
    ibanInput.dispatchEvent(new Event('change', { bubbles: true }));
    amountInput.dispatchEvent(new Event('change', { bubbles: true }));
    communicationInput.dispatchEvent(new Event('change', { bubbles: true }));
}

/**
 * Deletes the currently selected favorite
 */
function deleteFavorite() {
    const favoritesSelect = document.getElementById('favorites');
    const deleteButton = document.getElementById('delete-favorite');
    const saveButton = document.getElementById('save-favorite');
    const nameInput = document.getElementById('beneficiary_name');
    const ibanInput = document.getElementById('beneficiary_iban');
    const amountInput = document.getElementById('amount');
    const communicationInput = document.getElementById('communication');
    const form = document.getElementById('transfer-form');

    if (!favoritesSelect || !deleteButton || !saveButton || !nameInput || !ibanInput || !form) {
        console.error('Missing required elements for deleting favorite');
        return;
    }

    const selectedIndex = favoritesSelect.value;
    if (selectedIndex === '') {
        console.error('No favorite selected for deletion');
        return;
    }

    const favorites = JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]');
    favorites.splice(selectedIndex, 1);
    localStorage.setItem(FAVORITES_KEY, JSON.stringify(favorites));

    // Clear form and enable inputs
    formOperations.default.clearForm(form);
    nameInput.disabled = false;
    ibanInput.disabled = false;
    amountInput.disabled = false;
    communicationInput.disabled = false;
    deleteButton.disabled = true;
    updateSaveButtonText(saveButton, '', '');

    // Reload favorites list
    loadFavorites(favoritesSelect);
    favoritesSelect.value = '';

    // Trigger validation and change events on fields
    nameInput.dispatchEvent(new Event('input', { bubbles: true }));
    ibanInput.dispatchEvent(new Event('input', { bubbles: true }));
    amountInput.dispatchEvent(new Event('input', { bubbles: true }));
    communicationInput.dispatchEvent(new Event('input', { bubbles: true }));
    nameInput.dispatchEvent(new Event('change', { bubbles: true }));
    ibanInput.dispatchEvent(new Event('change', { bubbles: true }));
    amountInput.dispatchEvent(new Event('change', { bubbles: true }));
    communicationInput.dispatchEvent(new Event('change', { bubbles: true }));
}

/**
 * Initializes favorites functionality
 */
function initializeFavorites() {
    const favoritesSelect = document.getElementById('favorites');
    const saveButton = document.getElementById('save-favorite');
    const nameInput = document.getElementById('beneficiary_name');
    const ibanInput = document.getElementById('beneficiary_iban');

    if (!favoritesSelect || !saveButton || !nameInput || !ibanInput) {
        console.error('Missing required elements for initializing favorites');
        return;
    }

    // Load initial favorites
    loadFavorites(favoritesSelect);

    // Add event listeners
    favoritesSelect.addEventListener('change', loadFavorite);

    // Function to update button text
    const updateButtonText = () => {
        updateSaveButtonText(saveButton, nameInput.value.trim(), ibanInput.value.trim());
    };

    // Listen for both input and change events to catch all value changes
    nameInput.addEventListener('input', updateButtonText);
    nameInput.addEventListener('change', updateButtonText);
    ibanInput.addEventListener('input', updateButtonText);
    ibanInput.addEventListener('change', updateButtonText);

    // Set initial button text
    updateButtonText();
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializeFavorites);

const favorites = {
    loadFavorites,
    loadFavorite,
    saveFavorite,
    deleteFavorite,
    initializeFavorites,
    updateSaveButtonText
};

export default favorites;
