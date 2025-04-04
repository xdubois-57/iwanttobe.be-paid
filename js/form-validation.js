document.addEventListener('DOMContentLoaded', function() {
    const FAVORITES_KEY = 'qrtransfer_favorites';
    const STORAGE_KEY = 'qrtransfer_form_data';
    const form = document.querySelector('#transfer-form');
    const submitButton = form.querySelector('button[type="submit"]');
    const submitButtonOriginalText = submitButton.textContent;
    const favoritesSelect = document.getElementById('favorites');
    const saveButton = document.getElementById('save-favorite');
    const deleteButton = document.getElementById('delete-favorite');
    const saveButtonOriginalText = saveButton.textContent;
    const updateButtonText = saveButton.getAttribute('data-update-text');
    const inputs = {
        beneficiary_name: document.getElementById('beneficiary_name'),
        beneficiary_iban: document.getElementById('beneficiary_iban'),
        amount: document.getElementById('amount'),
        communication: document.getElementById('communication')
    };

    // Helper function to get translations
    function t(key) {
        return window.translations[key] || key;
    }

    // Clear any stored form data
    sessionStorage.clear();
    localStorage.removeItem(STORAGE_KEY);
    
    // Reset all form fields
    form.reset();
    
    // Clear any browser-stored values
    for (let inputId in inputs) {
        inputs[inputId].value = '';
    }

    // Add validation indicators if they don't exist
    for (let inputId in inputs) {
        const input = inputs[inputId];
        if (!input.parentNode.querySelector('.validation-indicator')) {
            const indicator = document.createElement('span');
            indicator.className = 'validation-indicator';
            input.parentNode.appendChild(indicator);
        }
    }

    // Validation rules
    const rules = {
        beneficiary_name: (value) => {
            if (!value.trim()) return false;
            // Allow letters, spaces, hyphens, apostrophes, and accented characters
            const validNameRegex = /^[a-zA-ZÀ-ÿ\s\-']+$/;
            return value.trim().length >= 1 && 
                   value.trim().length <= 100 && 
                   validNameRegex.test(value.trim());
        },
        beneficiary_iban: (value) => {
            if (!value.trim()) return false;
            // Remove spaces and convert to uppercase
            value = value.replace(/\s/g, '').toUpperCase();
            
            // Basic format check for SEPA countries
            return /^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/.test(value);
        },
        amount: (value) => {
            if (!value) return false;
            // Only allow numbers and one decimal point
            const validAmountRegex = /^\d+(\.\d{0,2})?$/;
            const amount = parseFloat(value);
            return validAmountRegex.test(value) && 
                   !isNaN(amount) && 
                   amount > 0 && 
                   amount <= 999999999.99;
        },
        communication: (value) => {
            if (!value) return true; // Optional field
            // Allow letters, numbers, spaces, and basic punctuation
            const validCommRegex = /^[a-zA-Z0-9\s\-'.,]*$/;
            return value.length <= 100 && validCommRegex.test(value);
        }
    };

    // Load favorites from local storage
    function loadFavorites() {
        try {
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
        } catch (e) {
            console.error('Error loading favorites:', e);
        }
    }

    // Load a favorite into the form
    window.loadFavorite = function() {
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
                let allValid = true;
                for (let inputId in inputs) {
                    const value = favorite[inputId] || '';
                    inputs[inputId].value = value;
                    // Validate each field
                    if (!validateField(inputId, value)) {
                        allValid = false;
                    }
                }
                if (allValid) {
                    saveButton.textContent = updateButtonText;
                }
                deleteButton.disabled = false;

                // Automatically generate QR code when a favorite is selected
                let isValid = true;
                for (let inputId in inputs) {
                    const value = inputs[inputId].value;
                    if (!validateField(inputId, value)) {
                        if (inputId !== 'communication') { // Don't break for optional field
                            isValid = false;
                            break;
                        }
                    }
                }

                if (isValid) {
                    generateQRCode();
                }
            }
        } catch (e) {
            console.error('Error loading favorite:', e);
        }
    };

    // Delete the selected favorite
    window.deleteFavorite = function() {
        const selectedIndex = favoritesSelect.value;
        if (!selectedIndex) return;

        try {
            const favorites = JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]');
            const favorite = favorites[selectedIndex];
            
            if (confirm(t('confirm_delete_favorite'))) {
                favorites.splice(selectedIndex, 1);
                localStorage.setItem(FAVORITES_KEY, JSON.stringify(favorites));
                loadFavorites();
                clearForm();
            }
        } catch (e) {
            console.error('Error deleting favorite:', e);
            alert(t('error_deleting_favorite'));
        }
    };

    // Save or update favorite
    window.saveFavorite = function() {
        // Validate all required fields
        let isValid = true;
        for (let inputId in inputs) {
            const value = inputs[inputId].value;
            if (!validateField(inputId, value)) {
                if (inputId !== 'communication') { // Don't fail validation for optional field
                    isValid = false;
                    break;
                }
            }
        }

        if (!isValid) {
            alert(t('error_required_fields'));
            return;
        }

        try {
            const favorites = JSON.parse(localStorage.getItem(FAVORITES_KEY) || '[]');
            const formData = {};
            for (let inputId in inputs) {
                formData[inputId] = inputs[inputId].value;
            }

            const selectedIndex = favoritesSelect.value;
            if (selectedIndex) {
                // Update existing favorite
                favorites[selectedIndex] = formData;
                localStorage.setItem(FAVORITES_KEY, JSON.stringify(favorites));
                loadFavorites();
                favoritesSelect.value = selectedIndex; // Maintain selection
                deleteButton.disabled = false;
                alert(t('favorite_updated'));
            } else {
                // Check for duplicates when adding new favorite
                const isDuplicate = favorites.some(fav => 
                    fav.beneficiary_iban === formData.beneficiary_iban && 
                    fav.beneficiary_name === formData.beneficiary_name
                );

                if (isDuplicate) {
                    alert(t('favorite_duplicate'));
                    return;
                }

                favorites.push(formData);
                localStorage.setItem(FAVORITES_KEY, JSON.stringify(favorites));
                loadFavorites();
                favoritesSelect.value = favorites.length - 1; // Select the new favorite
                saveButton.textContent = updateButtonText;
                deleteButton.disabled = false;
                alert(t('favorite_saved'));
            }
        } catch (e) {
            console.error('Error saving favorite:', e);
            alert(t('error_saving_favorite'));
        }
    };

    // Clear form fields
    window.clearForm = function() {
        // Reset favorites dropdown
        favoritesSelect.value = '';
        saveButton.textContent = saveButtonOriginalText;
        deleteButton.disabled = true;
        
        // Clear all input fields
        for (let inputId in inputs) {
            inputs[inputId].value = '';
            // Hide validation indicators
            const indicator = inputs[inputId].parentNode.querySelector('.validation-indicator');
            indicator.style.display = 'none';
        }

        // Reset right panel to default state
        document.getElementById('user-qr').style.display = 'none';
        document.getElementById('support-qr').style.display = 'block';
    };

    function validateField(fieldId, value) {
        const input = inputs[fieldId];
        const isValid = rules[fieldId](value);
        const indicator = input.parentNode.querySelector('.validation-indicator');
        
        // Always show indicator except for empty optional fields
        if (fieldId === 'communication' && value.trim() === '') {
            indicator.style.display = 'none';
        } else {
            indicator.style.display = 'block';
            if (isValid) {
                indicator.className = 'validation-indicator valid';
                input.setCustomValidity('');
            } else {
                indicator.className = 'validation-indicator invalid';
                input.setCustomValidity(t('invalid_format'));
            }
        }

        return isValid;
    }

    // Add input event listeners for real-time validation
    for (let inputId in inputs) {
        const input = inputs[inputId];
        input.addEventListener('input', function(e) {
            validateField(inputId, e.target.value);
        });

        // Initial validation state
        if (inputId !== 'communication') {
            validateField(inputId, input.value);
        }
    }

    // Format IBAN as user types
    inputs.beneficiary_iban.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '').toUpperCase();
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) {
                formatted += ' ';
            }
            formatted += value[i];
        }
        e.target.value = formatted;
    });

    // Load favorites on page load
    loadFavorites();

    // Load saved values from local storage
    try {
        const savedData = localStorage.getItem(STORAGE_KEY);
        if (savedData) {
            const formData = JSON.parse(savedData);
            for (let inputId in inputs) {
                if (formData[inputId]) {
                    inputs[inputId].value = formData[inputId];
                    validateField(inputId, inputs[inputId].value);
                }
            }
        }
    } catch (e) {
        console.error('Error loading saved form data:', e);
    }

    function generateQRCode() {
        // Validate all fields
        let isValid = true;
        for (let inputId in inputs) {
            if (!validateField(inputId, inputs[inputId].value)) {
                if (inputId !== 'communication') { // Don't break for optional field
                    isValid = false;
                    break;
                }
            }
        }

        if (!isValid) {
            return;
        }

        // Show loading state
        submitButton.disabled = true;
        submitButton.textContent = t('generating');

        const formData = new FormData(form);
        fetch('/generate-qr', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || t('failed_to_generate_qr'));
            }

            // Hide support QR and show user QR
            document.getElementById('support-qr').style.display = 'none';
            document.getElementById('user-qr').style.display = 'block';
            document.getElementById('qr-image').src = data.image;

            submitButton.disabled = false;
            submitButton.textContent = submitButtonOriginalText;
        })
        .catch(error => {
            console.error('Error:', error);
            alert(t('error') + ': ' + (error.message || t('failed_to_generate_qr')));
            
            // Show support QR and hide user QR on error
            document.getElementById('support-qr').style.display = 'block';
            document.getElementById('user-qr').style.display = 'none';

            submitButton.disabled = false;
            submitButton.textContent = submitButtonOriginalText;
        });
    }

    // Handle form submission
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        generateQRCode();
    });

    // Watch amount field changes to auto-generate QR code when favorite is selected
    inputs.amount.addEventListener('input', function() {
        if (favoritesSelect.value !== '') {
            generateQRCode();
        }
    });

    // Clear amount field when selecting a favorite
    favoritesSelect.addEventListener('change', function() {
        if (favoritesSelect.value !== '') {
            inputs.amount.value = '';
        }
    });
});
