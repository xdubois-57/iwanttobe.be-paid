import translations from './translations.js';
import formOperations from './form-operations.js';

const FAVORITES_KEY = 'qr_transfer_favorites';

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
        saveButton.textContent = translations.translate('save_favorite');
        
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

    // Disable inputs when a favorite is selected
    nameInput.disabled = true;
    ibanInput.disabled = true;
    deleteButton.disabled = false;
    saveButton.textContent = translations.translate('update_favorite');

    // Trigger validation and change events on fields
    nameInput.dispatchEvent(new Event('input', { bubbles: true }));
    ibanInput.dispatchEvent(new Event('input', { bubbles: true }));
    nameInput.dispatchEvent(new Event('change', { bubbles: true }));
    ibanInput.dispatchEvent(new Event('change', { bubbles: true }));
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
    deleteButton.disabled = true;
    saveButton.textContent = translations.translate('save_favorite');

    // Reload favorites list
    loadFavorites(favoritesSelect);
    favoritesSelect.value = '';

    // Trigger validation and change events on fields
    nameInput.dispatchEvent(new Event('input', { bubbles: true }));
    ibanInput.dispatchEvent(new Event('input', { bubbles: true }));
    nameInput.dispatchEvent(new Event('change', { bubbles: true }));
    ibanInput.dispatchEvent(new Event('change', { bubbles: true }));
}

/**
 * Initializes favorites functionality
 */
function initializeFavorites() {
    const favoritesSelect = document.getElementById('favorites');
    if (!favoritesSelect) {
        console.error('Favorites select not found');
        return;
    }

    // Load initial favorites
    loadFavorites(favoritesSelect);

    // Add event listener for favorites selection
    favoritesSelect.addEventListener('change', loadFavorite);
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializeFavorites);

const favorites = {
    loadFavorites,
    loadFavorite,
    deleteFavorite,
    initializeFavorites
};

export default favorites;
