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

document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a page that has the transfer form
    const form = document.querySelector('#transfer-form');
    if (!form) {
        // Not on a page with the transfer form, exit early
        return;
    }

    // Initialize modules
    Promise.all([
        import('./modules/validation.js'),
        import('./modules/qr-generator.js'),
        import('./modules/favorites.js'),
        import('./modules/form-operations.js'),
        import('./modules/translations.js')
    ]).then(([validation, qrGenerator, favorites, formOperations, translations]) => {
        // Get form elements
        const submitButton = document.getElementById('generate-qr-button');

        if (!submitButton) {
            console.error('Required form elements not found');
            return;
        }

        const submitButtonOriginalText = submitButton.textContent;
        const favoritesSelect = document.getElementById('favorites');
        const saveButton = document.getElementById('save-favorite');
        const deleteButton = document.getElementById('delete-favorite');
        const saveButtonOriginalText = saveButton.textContent;
        
        // Store original save text for later use
        saveButton.dataset.saveText = saveButtonOriginalText;
        
        const inputs = {
            beneficiary_name: document.getElementById('beneficiary_name'),
            beneficiary_iban: document.getElementById('beneficiary_iban'),
            amount: document.getElementById('amount'),
            communication: document.getElementById('communication')
        };

        if (!favoritesSelect || !saveButton || !deleteButton || !inputs.beneficiary_name || !inputs.beneficiary_iban || !inputs.amount || !inputs.communication) {
            console.error('Required form elements not found');
            return;
        }

        // Initialize favorites
        favorites.default.loadFavorites(favoritesSelect);

        // Function to format IBAN with spaces
        function formatIBAN(iban) {
            // Remove existing spaces
            iban = iban.replace(/\s/g, '').toUpperCase();
            // Add a space every 4 characters
            return iban.replace(/(.{4})/g, '$1 ').trim();
        }
        
        // Add blur event listener to IBAN field for auto-formatting
        inputs.beneficiary_iban.addEventListener('blur', function() {
            if (this.value) {
                const formattedIBAN = formatIBAN(this.value);
                this.value = formattedIBAN;
                // Validate after formatting
                validation.default.validateField('beneficiary_iban', this.value);
            }
        });

        // Initialize validation indicators
        ['beneficiary_name', 'beneficiary_iban', 'amount', 'communication'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                const indicator = document.createElement('span');
                indicator.className = 'validation-indicator';
                field.insertAdjacentElement('afterend', indicator);
                validation.default.validateField(fieldId, field.value);
            }
        });

        // Set up event handlers
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!submitButton) return;
            
            if (validation.default.validateAllFields(inputs)) {
                qrGenerator.default.generateQRCode(form, submitButton, submitButtonOriginalText);
            }
        });

        // Add input event listeners for real-time validation
        for (let inputId in inputs) {
            const input = inputs[inputId];
            input.addEventListener('input', function(e) {
                console.log(`Input event on ${inputId}:`, e.target.value);
                
                // For IBAN, validate without formatting while typing
                if (inputId === 'beneficiary_iban') {
                    // Remove spaces for validation but don't update the field value
                    const rawValue = e.target.value;
                    validation.default.validateField(inputId, rawValue);
                } else {
                    validation.default.validateField(inputId, e.target.value);
                }
                
                qrGenerator.default.updateButtonState(inputs, submitButton);
                
                // Update save button text when name or IBAN changes
                if (inputId === 'beneficiary_name' || inputId === 'beneficiary_iban') {
                    favorites.default.updateSaveButtonText(saveButton, inputs.beneficiary_name.value.trim(), inputs.beneficiary_iban.value.trim());
                }
            });

            // Initial validation state
            if (inputId !== 'communication') {
                validation.default.validateField(inputId, input.value);
            }
        }

        // Listen for favorites changes
        favoritesSelect.addEventListener('change', () => {
            console.log('Favorite selection changed');
            favorites.default.loadFavorite();
            // Small delay to ensure inputs are updated
            setTimeout(() => {
                qrGenerator.default.updateButtonState(inputs, submitButton);
                console.log('Updated button state after favorite change');
            }, 0);
        });

        // Reset when form is cleared
        const clearButton = document.getElementById('clear-form');
        if (clearButton) {
            clearButton.addEventListener('click', () => {
                formOperations.default.clearForm(form);
                inputs.beneficiary_name.disabled = false;
                inputs.beneficiary_iban.disabled = false;
                favoritesSelect.value = '';
                deleteButton.disabled = true;
                saveButton.textContent = translations.default.translate('save_favorite');
                
                // Reset validation states
                validation.default.validateField('beneficiary_name', '');
                validation.default.validateField('beneficiary_iban', '');
                validation.default.validateField('amount', '');
                validation.default.validateField('communication', '');
            });
        }

        // Save favorite handler
        saveButton.addEventListener('click', () => {
            favorites.default.saveFavorite(inputs, favoritesSelect, saveButton, deleteButton);
        });

        // Delete favorite handler
        deleteButton.addEventListener('click', () => {
            if (favoritesSelect.selectedIndex > 0) {
                favorites.default.deleteFavorite()(formOperations);
            }
        });

        function validateForm() {
            const nameValid = validation.default.validateField('beneficiary_name', inputs.beneficiary_name.value);
            const ibanValid = validation.default.validateField('beneficiary_iban', inputs.beneficiary_iban.value);
            const saveButton = document.getElementById('save-favorite');
            
            if (saveButton) {
                saveButton.disabled = !(nameValid && ibanValid);
            }
        }

        // Add input event listeners for save button validation
        for (let inputId in inputs) {
            const input = inputs[inputId];
            input.addEventListener('input', validateForm);
        }
    });
});
