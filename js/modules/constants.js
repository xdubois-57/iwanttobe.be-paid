// Storage keys
const FAVORITES_KEY = 'qrtransfer_favorites';
const STORAGE_KEY = 'qrtransfer_form_data';

// Field validation patterns
const VALIDATION_PATTERNS = {
    beneficiary_name: /^[a-zA-Z0-9\s\-']{1,70}$/,
    beneficiary_iban: /^[A-Z0-9]{14,34}$/,
    amount: /^\d+(\.\d{0,2})?$/,
    communication: /^[a-zA-Z0-9\s\-']{0,70}$/
};

export default {
    FAVORITES_KEY,
    STORAGE_KEY,
    VALIDATION_PATTERNS
};
