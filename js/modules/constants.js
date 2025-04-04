// Storage keys
const FAVORITES_KEY = 'qrtransfer_favorites';
const STORAGE_KEY = 'qrtransfer_form_data';
const FORM_DATA_KEY = 'qr_transfer_form_data';

// Field validation patterns
const VALIDATION_PATTERNS = {
    beneficiary_name: /^[a-zA-Z0-9\s\-']{2,50}$/,
    beneficiary_iban: /^[A-Z]{2}[0-9]{2}[A-Z0-9]{4}[0-9]{7}([A-Z0-9]?){0,16}$/,
    amount: /^\d+(\.\d{0,2})?$/,
    communication: /^[a-zA-Z0-9\s\-']{0,50}$/
};

export default {
    FAVORITES_KEY,
    STORAGE_KEY,
    FORM_DATA_KEY,
    VALIDATION_PATTERNS
};
