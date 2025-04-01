document.addEventListener('DOMContentLoaded', function() {
    const STORAGE_KEY = 'qrtransfer_form_data';
    const form = document.querySelector('#transfer-form');
    const inputs = {
        beneficiary_name: document.getElementById('beneficiary_name'),
        beneficiary_iban: document.getElementById('beneficiary_iban'),
        amount: document.getElementById('amount'),
        communication: document.getElementById('communication')
    };

    // Load saved values from local storage
    try {
        const savedData = localStorage.getItem(STORAGE_KEY);
        if (savedData) {
            const formData = JSON.parse(savedData);
            for (let inputId in inputs) {
                if (formData[inputId]) {
                    inputs[inputId].value = formData[inputId];
                }
            }
            // Validate all fields after loading
            for (let inputId in inputs) {
                validateField(inputId, inputs[inputId].value);
            }
        }
    } catch (e) {
        console.error('Error loading saved form data:', e);
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
            // Allow letters, spaces, hyphens, apostrophes, and accented characters
            const validNameRegex = /^[a-zA-ZÀ-ÿ\s\-']+$/;
            return value.trim().length >= 1 && 
                   value.trim().length <= 100 && 
                   validNameRegex.test(value.trim());
        },
        beneficiary_iban: (value) => {
            // Remove spaces and convert to uppercase
            value = value.replace(/\s/g, '').toUpperCase();
            
            // Basic format check for SEPA countries
            return /^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/.test(value);
        },
        amount: (value) => {
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

    // Real-time validation and storage
    for (let inputId in inputs) {
        const input = inputs[inputId];
        
        // Only prevent invalid characters in amount field
        if (inputId === 'amount') {
            input.addEventListener('keypress', function(e) {
                if (!/[\d.]/.test(e.key) || 
                    (e.key === '.' && this.value.includes('.'))) {
                    e.preventDefault();
                }
            });
        }

        input.addEventListener('input', function() {
            validateField(inputId, input.value);
            saveFormData(); // Save on every change
        });

        input.addEventListener('change', function() {
            validateField(inputId, input.value);
            saveFormData(); // Save on every change
        });

        // Initial validation
        validateField(inputId, input.value);
    }

    function validateField(fieldId, value) {
        const input = inputs[fieldId];
        const isValid = rules[fieldId](value);
        const indicator = input.parentNode.querySelector('.validation-indicator');
        
        if (value.trim() === '') {
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
            if (!rules[inputId](inputs[inputId].value)) {
                isValid = false;
                break;
            }
        }

        if (!isValid) {
            return;
        }

        // Prepare form data
        const formData = {
            beneficiary_name: inputs.beneficiary_name.value.trim(),
            beneficiary_iban: inputs.beneficiary_iban.value.replace(/\s/g, ''),
            amount: parseFloat(inputs.amount.value),
            communication: inputs.communication.value.trim()
        };

        try {
            // Show loading state
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.getAttribute('data-original-text') || submitButton.textContent;
            submitButton.setAttribute('data-original-text', originalText); // Store original text
            submitButton.disabled = true;
            submitButton.textContent = 'Generating...';

            // Make AJAX call
            const response = await fetch('/generate-qr', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            if (result.error) {
                console.error('Server Error:', result.details);
                alert('Error: ' + result.message);
                submitButton.disabled = false;
                submitButton.textContent = originalText;
                return;
            }

            // Update QR code display
            document.getElementById('qr-placeholder').style.display = 'none';
            const qrCode = document.getElementById('qr-code');
            qrCode.style.display = 'block';
            const qrImage = document.getElementById('qr-image');
            
            // Set the base64 image data
            qrImage.src = result.qr_url;
            qrImage.onerror = function() {
                console.error('Failed to load QR code');
                alert('Failed to load QR code. Please try again.');
                document.getElementById('qr-placeholder').style.display = 'block';
                qrCode.style.display = 'none';
            };

            // Log debug info
            if (result.debug) {
                console.log('Debug Info:', result.debug);
            }

            // Don't clear form data from local storage on success anymore
            // localStorage.removeItem(STORAGE_KEY);

            // Reset button state with original text
            submitButton.disabled = false;
            submitButton.textContent = originalText;

        } catch (error) {
            console.error('Error:', error);
            alert('Failed to generate QR code. Please try again.');
            // Reset button state with original text
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    });
});
