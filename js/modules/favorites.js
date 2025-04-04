import formOperations from './form-operations.js';
import translations from './translations.js';
import constants from './constants.js';

const FAVORITES_KEY = constants.FAVORITES_KEY;
const SELECTED_FAVORITE_KEY = constants.SELECTED_FAVORITE_KEY;

/**
 * Check if a favorite exists with given name and IBAN
 * @param {string} name - Beneficiary name
 * @param {string} iban - Beneficiary IBAN
 * @returns {number} Index of the favorite if found, -1 otherwise
 */
function findFavoriteIndex(name, iban) {
    if (!name || !iban) return -1;
    const favorites = JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]');
    console.log('Finding favorite:', { name, iban, favorites });
    const index = favorites.findIndex(f => 
        f.beneficiary_name === name && f.beneficiary_iban === iban
    );
    console.log('Found at index:', index);
    return index;
}

/**
 * Update the save button text based on whether a favorite exists
 * @param {HTMLButtonElement} saveButton - The save button element
 * @param {string} name - Beneficiary name
 * @param {string} iban - Beneficiary IBAN
 */
function updateSaveButtonText(saveButton, name, iban) {
    console.log('updateSaveButtonText called with:', { name, iban });
    const existingIndex = findFavoriteIndex(name, iban);
    const exists = existingIndex !== -1;
    const newText = exists ? translations.translate('update_favorite') : translations.translate('save_favorite');
    console.log('Setting button text to:', newText);
    saveButton.textContent = newText;
}

/**
 * Save current form data as a favorite
 * @param {Object} inputs - Form input elements
 * @param {HTMLSelectElement} favoritesSelect - Favorites select element
 * @param {HTMLButtonElement} saveButton - Save button element
 * @param {HTMLButtonElement} deleteButton - Delete button element
 */
function saveFavorite(inputs, favoritesSelect, saveButton, deleteButton) {
    const name = inputs.beneficiary_name?.value?.trim();
    const iban = inputs.beneficiary_iban?.value?.trim();
    
    if (!name || !iban) {
        console.error('Missing required fields');
        return;
    }
    
    const favorite = {
        beneficiary_name: name,
        beneficiary_iban: iban,
        amount: inputs.amount?.value?.trim() || '',
        communication: inputs.communication?.value?.trim() || ''
    };
    
    let favorites = JSON.parse(localStorage.getItem(constants.FAVORITES_KEY) || '[]');
    const existingIndex = findFavoriteIndex(name, iban);
    
    if (existingIndex >= 0) {
        if (!confirm(translations.translate('favorite_exists_update'))) return;
        favorites[existingIndex] = favorite;
    } else {
        favorites.push(favorite);
    }
    
    localStorage.setItem(constants.FAVORITES_KEY, JSON.stringify(favorites));
    loadFavorites(favoritesSelect);
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

    const favorites = JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]').filter(f => 
        f?.beneficiary_name && f?.beneficiary_iban
    );
    
    // Clear existing options
    favoritesSelect.innerHTML = '';
    
    // Add default option
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = translations.translate('select_favorite');
    favoritesSelect.appendChild(defaultOption);
    
    // Add validated favorites
    favorites.forEach((favorite, index) => {
        const option = document.createElement('option');
        option.value = index;
        option.textContent = `${favorite.beneficiary_name} (${favorite.beneficiary_iban})`;
        favoritesSelect.appendChild(option);
    });
    
    // Update storage with cleaned favorites if any were removed
    if (favorites.length !== JSON.parse(localStorage.getItem(constants.FAVORITES_KEY) || '[]').length) {
        localStorage.setItem(constants.FAVORITES_KEY, JSON.stringify(favorites));
    }
    
    // Restore selected favorite if any
    const selectedIndex = localStorage.getItem(SELECTED_FAVORITE_KEY);
    if (selectedIndex !== null && favoritesSelect.options[selectedIndex]) {
        favoritesSelect.value = selectedIndex;
        loadFavorite();
    }
}

/**
 * Loads a selected favorite into the form
 */
function loadFavorite() {
    console.log('loadFavorite called');
    const favoritesSelect = document.getElementById('favorites');
    const selectedIndex = favoritesSelect?.value;
    
    if (!favoritesSelect) return;
    
    const nameInput = document.getElementById('beneficiary_name');
    const ibanInput = document.getElementById('beneficiary_iban');
    const saveButton = document.getElementById('save-favorite');
    
    if (selectedIndex === '') {
        // No favorite selected - enable fields
        nameInput.disabled = false;
        ibanInput.disabled = false;
        saveButton.disabled = !(nameInput.value && ibanInput.value);
        return;
    }
    
    const favorites = JSON.parse(localStorage.getItem(constants.FAVORITES_KEY) || '[]');
    const favorite = favorites[selectedIndex];
    
    if (!favorite?.beneficiary_name || !favorite?.beneficiary_iban) {
        console.error('Invalid favorite at index', selectedIndex);
        favorites.splice(selectedIndex, 1);
        localStorage.setItem(constants.FAVORITES_KEY, JSON.stringify(favorites));
        loadFavorites(favoritesSelect);
        return;
    }
    
    const deleteButton = document.getElementById('delete-favorite');
    const amountInput = document.getElementById('amount');
    const communicationInput = document.getElementById('communication');
    const form = document.getElementById('transfer-form');

    if (!deleteButton || !saveButton || !nameInput || !ibanInput || !form) {
        console.error('Missing required elements for loading favorite');
        return;
    }

    // Save selected favorite
    if (selectedIndex) {
        localStorage.setItem(SELECTED_FAVORITE_KEY, selectedIndex);
    } else {
        localStorage.removeItem(SELECTED_FAVORITE_KEY);
    }

    // Load all favorite data first
    nameInput.value = favorite.beneficiary_name;
    ibanInput.value = favorite.beneficiary_iban;
    amountInput.value = favorite.amount || '';
    communicationInput.value = favorite.communication || '';

    // Disable inputs
    nameInput.disabled = true;
    ibanInput.disabled = true;
    deleteButton.disabled = false;
    saveButton.textContent = translations.translate('update_favorite');

    // Trigger validation once after all fields are set
    setTimeout(() => {
        const generateButton = document.getElementById('generate-qr-button');
        if (generateButton && !generateButton.disabled) {
            generateButton.click();
        }
    }, 100);
}

/**
 * Deletes the currently selected favorite
 */
function deleteFavorite(formOperations) {
    return function() {
        const favoritesSelect = document.getElementById('favorites');
        if (!favoritesSelect) {
            console.error('Favorites select element not found');
            return;
        }
        
        const selectedIndex = favoritesSelect.selectedIndex - 1; // Account for default option
        
        if (selectedIndex < 0) {
            console.error('Please select a favorite to delete');
            return;
        }
        
        const favorites = JSON.parse(localStorage.getItem(constants.FAVORITES_KEY) || '[]');
        
        if (selectedIndex >= 0 && selectedIndex < favorites.length) {
            favorites.splice(selectedIndex, 1);
            localStorage.setItem(constants.FAVORITES_KEY, JSON.stringify(favorites));
            
            // Clear form
            const form = document.getElementById('transfer-form');
            if (formOperations?.default?.clearForm && form) {
                formOperations.default.clearForm(form);
            }
            
            // Reset UI
            loadFavorites(favoritesSelect);
        }
    };
}

/**
 * Initializes favorites functionality
 */
function initializeFavorites() {
    const favoritesSelect = document.getElementById('favorites');
    const saveButton = document.getElementById('save-favorite');
    const nameInput = document.getElementById('beneficiary_name');
    const ibanInput = document.getElementById('beneficiary_iban');
    const form = document.getElementById('transfer-form');

    if (!favoritesSelect || !saveButton || !nameInput || !ibanInput || !form) {
        console.error('Missing required elements for initializing favorites');
        return;
    }

    // Load initial favorites
    loadFavorites(favoritesSelect);

    // Add event listeners
    favoritesSelect.addEventListener('change', () => {
        const selectedIndex = favoritesSelect.value;
        if (selectedIndex === '') {
            // Enable inputs when no favorite is selected
            nameInput.disabled = false;
            ibanInput.disabled = false;
            // Update button text for current values
            updateSaveButtonText(saveButton, nameInput.value.trim(), ibanInput.value.trim());
        }
        loadFavorite();
    });

    // Set initial button text
    updateSaveButtonText(saveButton, nameInput.value.trim(), ibanInput.value.trim());
}

// Expose saveFavorite immediately
window.saveFavorite = function() {
    const inputs = {
        beneficiary_name: document.getElementById('beneficiary_name'),
        beneficiary_iban: document.getElementById('beneficiary_iban'),
        amount: document.getElementById('amount'),
        communication: document.getElementById('communication')
    };
    const favoritesSelect = document.getElementById('favorites');
    const saveButton = document.getElementById('save-favorite');
    const deleteButton = document.getElementById('delete-favorite');
    
    if (!inputs.beneficiary_name || !inputs.beneficiary_iban) {
        alert(translations.translate('fill_required_fields'));
        return;
    }
    
    saveFavorite(inputs, favoritesSelect, saveButton, deleteButton);
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeFavorites();
});

// Export functions
export default {
    findFavoriteIndex,
    updateSaveButtonText,
    saveFavorite,
    loadFavorites,
    loadFavorite,
    deleteFavorite: (formOperations) => deleteFavorite(formOperations),
    initializeFavorites
};
