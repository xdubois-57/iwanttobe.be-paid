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
        const updateButtonText = saveButton.getAttribute('data-update-text');
        
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
            window.generateQRCode();
        });

        // Make functions globally available
        window.generateQRCode = () => qrGenerator.default.generateQRCode(form, submitButton, submitButtonOriginalText);
        window.clearForm = () => formOperations.default.clearForm(form);

        // Add input event listeners for real-time validation
        for (let inputId in inputs) {
            const input = inputs[inputId];
            input.addEventListener('input', function(e) {
                console.log(`Input event on ${inputId}:`, e.target.value);
                validation.default.validateField(inputId, e.target.value);
            });

            // Initial validation state
            if (inputId !== 'communication') {
                validation.default.validateField(inputId, input.value);
            }
        }

        // Save form data when modified
        form.addEventListener('change', () => formStorage.default.saveFormData(form));

        // Set up favorites handlers
        window.loadFavorite = () => favorites.default.loadFavorite({
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

        window.saveFavorite = () => favorites.default.saveFavorite({
            form,
            favoritesSelect,
            inputs,
            saveButton,
            saveButtonOriginalText,
            deleteButton
        });

        window.deleteFavorite = () => favorites.default.deleteFavorite({
            favoritesSelect,
            saveButton,
            saveButtonOriginalText,
            deleteButton,
            form
        });
    });
});
