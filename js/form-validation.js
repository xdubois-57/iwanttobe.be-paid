// Helper function to get translations with fallbacks
function t(key) {
    const fallbacks = {
        'generating': 'Generating...',
        'failed_to_generate_qr': 'Failed to generate QR code',
        'error': 'Error',
        'please_fix_errors': 'Please fix the errors in the form',
        'confirm_delete_favorite': 'Confirm delete favorite',
        'error_deleting_favorite': 'Error deleting favorite',
        'error_required_fields': 'Error: required fields',
        'favorite_updated': 'Favorite updated',
        'favorite_saved': 'Favorite saved',
        'favorite_duplicate': 'Favorite duplicate'
    };
    return (window.translations && window.translations[key]) || fallbacks[key] || key;
}

// QR code generation function
window.generateQRCode = function() {
    const form = document.getElementById('transfer-form');
    const submitButton = document.getElementById('generate-qr-button');
    const submitButtonOriginalText = submitButton.textContent;
    const inputs = {
        beneficiary_name: document.getElementById('beneficiary_name'),
        beneficiary_iban: document.getElementById('beneficiary_iban'),
        amount: document.getElementById('amount'),
        communication: document.getElementById('communication')
    };

    // Validate all fields
    let isValid = true;
    for (let inputId in inputs) {
        const value = inputs[inputId].value;
        const validationIndicator = inputs[inputId].parentNode.querySelector('.validation-indicator');
        
        // Skip validation for optional communication field if empty
        if (inputId === 'communication' && !value) {
            continue;
        }

        // Basic required field validation
        if (!value || value.trim() === '') {
            validationIndicator.style.backgroundColor = '#ff4444';
            isValid = false;
            continue;
        }

        // Field-specific validation
        switch (inputId) {
            case 'beneficiary_name':
                if (!/^[a-zA-ZÀ-ÿ\s\-']+$/.test(value.trim())) {
                    validationIndicator.style.backgroundColor = '#ff4444';
                    isValid = false;
                } else {
                    validationIndicator.style.backgroundColor = '#44ff44';
                }
                break;

            case 'beneficiary_iban':
                const cleanIban = value.replace(/\s/g, '').toUpperCase();
                if (!/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/.test(cleanIban)) {
                    validationIndicator.style.backgroundColor = '#ff4444';
                    isValid = false;
                } else {
                    validationIndicator.style.backgroundColor = '#44ff44';
                }
                break;

            case 'amount':
                const amount = parseFloat(value);
                if (isNaN(amount) || amount <= 0 || amount > 999999999.99) {
                    validationIndicator.style.backgroundColor = '#ff4444';
                    isValid = false;
                } else {
                    validationIndicator.style.backgroundColor = '#44ff44';
                }
                break;

            case 'communication':
                if (value && !/^[a-zA-Z0-9\s\-'.,]*$/.test(value)) {
                    validationIndicator.style.backgroundColor = '#ff4444';
                    isValid = false;
                } else {
                    validationIndicator.style.backgroundColor = '#44ff44';
                }
                break;
        }
    }

    if (!isValid) {
        alert(t('please_fix_errors'));
        return;
    }

    // Show loading state
    submitButton.textContent = t('generating');
    submitButton.disabled = true;

    const formData = new FormData(form);
    return fetch('/generate-qr', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const imageUrl = data.image;

            // Hide support QR and show user QR
            document.getElementById('support-qr').style.display = 'none';
            document.getElementById('user-qr').style.display = 'block';
            
            // Set the image source and ensure it's visible
            const qrImage = document.getElementById('qr-image');
            qrImage.src = imageUrl;
            qrImage.style.display = 'block';

            return true;
        } else {
            throw new Error(data.error || t('failed_to_generate_qr'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || t('failed_to_generate_qr'));
        throw error;
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.textContent = submitButtonOriginalText;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const FAVORITES_KEY = 'qrtransfer_favorites';
    const STORAGE_KEY = 'qrtransfer_form_data';
    const form = document.querySelector('#transfer-form');
    const submitButton = document.getElementById('generate-qr-button');
    const submitButtonOriginalText = submitButton.textContent;
    const favoritesSelect = document.getElementById('favorites');
    const saveButton = document.getElementById('save-favorite');
    const deleteButton = document.getElementById('delete-favorite');
    const saveButtonOriginalText = saveButton.textContent;
    const updateButtonText = saveButton.getAttribute('data-update-text');
    const amountField = document.getElementById('amount');

    const inputs = {
        beneficiary_name: document.getElementById('beneficiary_name'),
        beneficiary_iban: document.getElementById('beneficiary_iban'),
        amount: amountField,
        communication: document.getElementById('communication')
    };

    // Helper function to get translations with fallbacks
    function t(key) {
        const fallbacks = {
            'generating': 'Generating...',
            'failed_to_generate_qr': 'Failed to generate QR code',
            'error': 'Error',
            'please_fix_errors': 'Please fix the errors in the form',
            'confirm_delete_favorite': 'Confirm delete favorite',
            'error_deleting_favorite': 'Error deleting favorite',
            'error_required_fields': 'Error: required fields',
            'favorite_updated': 'Favorite updated',
            'favorite_saved': 'Favorite saved',
            'favorite_duplicate': 'Favorite duplicate'
        };
        return (window.translations && window.translations[key]) || fallbacks[key] || key;
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
                // Set text fields
                inputs.beneficiary_name.value = favorite.beneficiary_name || '';
                inputs.beneficiary_iban.value = favorite.beneficiary_iban || '';
                inputs.communication.value = favorite.communication || '';

                // Handle amount field specially (convert to number)
                const amount = parseFloat(favorite.amount);
                if (!isNaN(amount)) {
                    const formattedAmount = amount.toFixed(2);
                    amountField.value = formattedAmount;
                    
                    // Force a DOM update and trigger events
                    setTimeout(() => {
                        amountField.value = formattedAmount;
                        amountField.dispatchEvent(new Event('input', { bubbles: true }));
                        amountField.dispatchEvent(new Event('change', { bubbles: true }));
                    }, 0);
                } else {
                    amountField.value = '';
                }

                // Validate all fields
                let allValid = true;
                for (let inputId in inputs) {
                    const value = inputs[inputId].value;
                    if (!validateField(inputId, value)) {
                        if (inputId !== 'communication') { // Don't fail validation for optional field
                            allValid = false;
                            break;
                        }
                    }
                }

                if (allValid) {
                    saveButton.textContent = updateButtonText;
                    deleteButton.disabled = false;
                    // Automatically generate QR code
                    window.generateQRCode().catch(() => {
                        // Error is already handled in generateQRCode
                    });
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
            
            // Get current form values
            const name = inputs.beneficiary_name.value.trim();
            const iban = inputs.beneficiary_iban.value.trim();
            const amountField = document.getElementById('amount');
            const amountValue = parseFloat(amountField.value.trim());
            const amount = isNaN(amountValue) ? '' : amountValue.toFixed(2);
            const communication = inputs.communication.value.trim();

            // Create favorite object
            const formData = {
                beneficiary_name: name,
                beneficiary_iban: iban,
                amount: amount,
                communication: communication
            };

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

    // Load favorite and generate QR code on page load
    const selectedFavorite = localStorage.getItem('selectedFavorite');
    if (selectedFavorite) {
        try {
            const favorite = JSON.parse(localStorage.getItem('favorite_' + selectedFavorite));
            if (favorite) {
                // Fill the form with favorite data
                document.getElementById('beneficiary_name').value = favorite.name || '';
                document.getElementById('beneficiary_iban').value = favorite.iban || '';
                document.getElementById('amount').value = favorite.amount || '';
                document.getElementById('communication').value = favorite.communication || '';

                // Automatically generate QR code
                generateQRCode();
            }
        } catch (e) {
            console.error('Error loading favorite:', e);
        }
    }

    // Watch amount field changes to auto-generate QR code when favorite is selected
    inputs.amount.addEventListener('change', function() {
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
