document.addEventListener('DOMContentLoaded', function() {
    // Initialize modules
    Promise.all([
        import('./modules/validation.js'),
        import('./modules/qr-generator.js'),
        import('./modules/favorites.js'),
        import('./modules/form-storage.js'),
        import('./modules/form-operations.js')
    ]).then(([validation, qrGenerator, favorites, formStorage, formOperations]) => {
        // Get form elements
        const form = document.querySelector('#transfer-form');
        const submitButton = document.getElementById('generate-qr-button');
        const submitButtonOriginalText = submitButton.textContent;
        const favoritesSelect = document.getElementById('favorites');
        const saveButton = document.getElementById('save-favorite');
        const deleteButton = document.getElementById('delete-favorite');
        const saveButtonOriginalText = saveButton.textContent;
        const updateButtonText = translations.translate('update_favorite');
        
        // Store original save text for later use
        saveButton.dataset.saveText = saveButtonOriginalText;
        
        const amountField = document.getElementById('amount');

        const inputs = {
            beneficiary_name: document.getElementById('beneficiary_name'),
            beneficiary_iban: document.getElementById('beneficiary_iban'),
            amount: amountField,
            communication: document.getElementById('communication')
        };

        // Initialize form
        formStorage.default.loadFormData(form);
        favorites.default.loadFavorites(favoritesSelect);

        // Set up event handlers
        form.addEventListener('submit', function(e) {
            e.preventDefault();
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
            });

            // Initial validation state
            if (inputId !== 'communication') {
                validation.default.validateField(inputId, input.value);
            }
        }

        // Save form data when modified
        form.addEventListener('change', () => {
            formStorage.default.saveFormData(form);
            qrGenerator.default.updateButtonState(inputs, submitButton);
        });

        // Listen for favorites changes
        favoritesSelect.addEventListener('change', () => {
            console.log('Favorite selection changed');
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
            });
        }

        // Save favorite handler
        saveButton.addEventListener('click', () => {
            const name = inputs.beneficiary_name.value.trim();
            const iban = inputs.beneficiary_iban.value.trim();

            if (!name || !iban) {
                alert(translations.translate('fill_required_fields'));
                return;
            }

            let favorites = JSON.parse(localStorage.getItem('qr_transfer_favorites') || '[]');
            const selectedIndex = favoritesSelect.value;

            // Check for duplicates
            const existingIndex = favorites.findIndex(f => 
                f.beneficiary_name === name && f.beneficiary_iban === iban
            );

            if (existingIndex !== -1 && existingIndex !== parseInt(selectedIndex)) {
                alert(translations.translate('favorite_exists'));
                return;
            }

            const favorite = {
                beneficiary_name: name,
                beneficiary_iban: iban
            };

            if (selectedIndex !== '') {
                // Update existing favorite
                favorites[selectedIndex] = favorite;
            } else {
                // Add new favorite
                favorites.push(favorite);
            }

            localStorage.setItem('qr_transfer_favorites', JSON.stringify(favorites));
            favorites.default.loadFavorites(favoritesSelect);

            // Select the saved favorite
            const newIndex = selectedIndex !== '' ? selectedIndex : (favorites.length - 1).toString();
            favoritesSelect.value = newIndex;

            // Update UI state
            inputs.beneficiary_name.disabled = true;
            inputs.beneficiary_iban.disabled = true;
            deleteButton.disabled = false;
            saveButton.textContent = translations.translate('update_favorite');
        });

        // Delete favorite handler
        deleteButton.addEventListener('click', () => {
            favorites.default.deleteFavorite();
        });
    });
});
