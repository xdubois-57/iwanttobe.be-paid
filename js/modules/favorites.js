/**
 * QR Transfer
 * Copyright (C) 2025 Xavier Dubois
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

import formOperations from './form-operations.js';
import translations from './translations.js';
import constants from './constants.js';
import validation from './validation.js'; // Import validation module

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
    const existingIndex = findFavoriteIndex(name, iban);
    const currentText = saveButton.textContent;
    
    // Only update if necessary to prevent flickering
    if (existingIndex !== -1 && !currentText.includes('Update')) {
        saveButton.textContent = translations.translate('update_favorite');
    } else if (existingIndex === -1 && !currentText.includes('Save')) {
        saveButton.textContent = translations.translate('save_favorite');
    }
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
        // Just update without confirmation
        favorites[existingIndex] = favorite;
    } else {
        favorites.push(favorite);
    }
    
    localStorage.setItem(constants.FAVORITES_KEY, JSON.stringify(favorites));
    
    // Reload and select the new favorite
    loadFavorites(favoritesSelect);
    const newIndex = findFavoriteIndex(name, iban);
    if (newIndex !== -1) {
        favoritesSelect.value = newIndex;
        loadFavorite();
    }
}

/**
 * Loads favorites from storage and populates the select
 * @param {HTMLSelectElement} [favoritesSelect] - Optional select element, will query if not provided
 */
function loadFavorites(favoritesSelect) {
    favoritesSelect = favoritesSelect || document.getElementById('favorites');
    if (!favoritesSelect) return;
    
    // Store current state before clearing
    const currentValue = favoritesSelect.value;
    const defaultText = favoritesSelect.options[0]?.textContent || translations.translate('select_favorite');
    
    const favorites = JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]').filter(f => 
        f?.beneficiary_name && f?.beneficiary_iban
    );
    
    // Rebuild dropdown while preserving translations
    favoritesSelect.innerHTML = '';
    
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = defaultText;
    favoritesSelect.appendChild(defaultOption);
    
    favorites.forEach((favorite, index) => {
        const option = document.createElement('option');
        option.value = index;
        option.textContent = `${favorite.beneficiary_name} (${favorite.beneficiary_iban})`;
        favoritesSelect.appendChild(option);
    });
    
    // Restore selection if still valid
    if (currentValue && favoritesSelect.options[currentValue]) {
        favoritesSelect.value = currentValue;
    }
    
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
    const submitButton = document.getElementById('generate-qr-button');
    const submitButtonOriginalText = submitButton?.textContent || '';

    if (!deleteButton || !saveButton || !nameInput || !ibanInput || !form || !submitButton) {
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

    // Create inputs object for validation
    const inputs = {
        beneficiary_name: nameInput,
        beneficiary_iban: ibanInput,
        amount: amountInput,
        communication: communicationInput
    };

    // Validate all fields
    const isValid = validation.validateAllFields(inputs);
    
    // Reset aria-invalid attributes based on validation results
    for (const [fieldId, input] of Object.entries(inputs)) {
        const isFieldValid = validation.validateField(fieldId, input.value);
        input.setAttribute('aria-invalid', !isFieldValid);
    }
    
    // If all fields are valid, automatically generate QR code
    if (isValid) {
        // Import QR generator module dynamically
        import('./qr-generator.js').then(qrGenerator => {
            // Generate QR code without scrolling
            qrGenerator.default.generateQRCode(form, submitButton, submitButtonOriginalText, false);
        }).catch(error => {
            console.error('Error importing QR generator module:', error);
        });
    }
}

/**
 * Deletes the currently selected favorite
 */
function deleteFavorite(formOperations) {
    return function() {
        const favoritesSelect = document.getElementById('favorites');
        if (!favoritesSelect) return;
        
        const selectedIndex = favoritesSelect.selectedIndex - 1;
        if (selectedIndex < 0) return;
        
        const favorites = JSON.parse(localStorage.getItem(constants.FAVORITES_KEY) || '[]');
        if (selectedIndex >= favorites.length) return;
        
        // Delete the favorite
        favorites.splice(selectedIndex, 1);
        localStorage.setItem(constants.FAVORITES_KEY, JSON.stringify(favorites));
        
        // Explicitly call clear form function
        const clearFormBtn = document.getElementById('clear-form');
        if (clearFormBtn) {
            clearFormBtn.click();
        } else if (formOperations?.default?.clearForm) {
            const form = document.getElementById('transfer-form');
            if (form) formOperations.default.clearForm(form);
        }
        
        // Reset favorites list
        loadFavorites(favoritesSelect);
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

    const clearButton = document.getElementById('clear-form');
    if (clearButton) {
        clearButton.addEventListener('click', () => {
            formOperations.default.clearForm(form);
            inputs.beneficiary_name.disabled = false;
            inputs.beneficiary_iban.disabled = false;
            favoritesSelect.value = '';
            deleteButton.disabled = true;
            
            // Reset button text to 'Save' translation while preserving existing translation
            if (!saveButton.textContent.includes('Save')) {
                saveButton.textContent = translations.translate('save_favorite');
            }
            
            // Reset validation states
            validation.validateField('beneficiary_name', '');
            validation.validateField('beneficiary_iban', '');
            validation.validateField('amount', '');
            validation.validateField('communication', '');
        });
    }
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
        console.warn(translations.translate('fill_required_fields'));
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
