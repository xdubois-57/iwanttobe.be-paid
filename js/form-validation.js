document.addEventListener('DOMContentLoaded', function() {
    // Initialize modules
    Promise.all([
        import('./modules/validation.js'),
        import('./modules/qr-generator.js'),
        import('./modules/favorites.js'),
        import('./modules/form-operations.js')
    ]).then(([validation, qrGenerator, favorites, formOperations]) => {
        // Get form elements
        const form = document.querySelector('#transfer-form');
        const submitButton = document.getElementById('generate-qr-button');

        if (!form || !submitButton) {
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
                validation.default.validateField(inputId, e.target.value);
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
                saveButton.textContent = window.t('save_favorite');
            });
        }

        // Save favorite handler
        saveButton.addEventListener('click', () => {
            favorites.default.saveFavorite(inputs, favoritesSelect, saveButton, deleteButton);
        });

        // Delete favorite handler
        deleteButton.addEventListener('click', () => {
            favorites.default.deleteFavorite();
        });
    });
});
