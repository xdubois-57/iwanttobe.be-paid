document.addEventListener('DOMContentLoaded', function() {
    const STORAGE_KEY = 'qrtransfer_form_data';
    const form = document.querySelector('#transfer-form');
    const submitButton = form.querySelector('button[type="submit"]');
    const submitButtonOriginalText = submitButton.textContent;
    const inputs = {
        beneficiary_name: document.getElementById('beneficiary_name'),
        beneficiary_iban: document.getElementById('beneficiary_iban'),
        amount: document.getElementById('amount'),
        communication: document.getElementById('communication')
    };

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

    // Save form data to local storage
    function saveFormData() {
        try {
            const formData = {};
            for (let inputId in inputs) {
                formData[inputId] = inputs[inputId].value;
            }
            localStorage.setItem(STORAGE_KEY, JSON.stringify(formData));
        } catch (e) {
            console.error('Error saving form data:', e);
        }
    }

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
                input.setCustomValidity('Invalid format');
            }
        }

        return isValid;
    }

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

    // Add input event listeners for real-time validation
    for (let inputId in inputs) {
        const input = inputs[inputId];
        input.addEventListener('input', function(e) {
            validateField(inputId, e.target.value);
            saveFormData();
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

    // Handle form submission
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        
        // Validate all fields
        let isValid = true;
        for (let inputId in inputs) {
            if (!validateField(inputId, inputs[inputId].value)) {
                isValid = false;
                if (inputId !== 'communication') { // Don't break for optional field
                    break;
                }
            }
        }

        if (!isValid) {
            return;
        }

        // Show loading state
        submitButton.disabled = true;
        submitButton.textContent = 'Generating...';

        try {
            // Prepare form data
            const formData = new FormData(form);
            
            // Send request
            const response = await fetch('/generate-qr', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.error || 'Failed to generate QR code');
            }

            // Hide support QR and show user QR
            document.getElementById('support-qr').style.display = 'none';
            document.getElementById('user-qr').style.display = 'block';
            document.getElementById('qr-image').src = result.image;

        } catch (error) {
            console.error('Error:', error);
            alert('Error: ' + (error.message || 'Failed to generate QR code. Please try again.'));
            
            // Show support QR and hide user QR on error
            document.getElementById('support-qr').style.display = 'block';
            document.getElementById('user-qr').style.display = 'none';
        } finally {
            // Restore button state
            submitButton.disabled = false;
            submitButton.textContent = submitButtonOriginalText;
        }
    });
});
