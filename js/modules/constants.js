// Storage keys
const FAVORITES_KEY = 'qrtransfer_favorites';
const SELECTED_FAVORITE_KEY = 'qrtransfer_selected_favorite';

// Field validation patterns
const VALIDATION_PATTERNS = {
    beneficiary_name: /^[a-zA-Z0-9\s\-']{2,50}$/,
    beneficiary_iban: /^[A-Z]{2}[0-9]{2}[A-Z0-9]{4}[0-9]{7}([A-Z0-9]?){0,16}$/,
    amount: /^\d+(\.\d{0,2})?$/,
    communication: /^[a-zA-Z0-9\s\-']{0,50}$/
};

export default {
    FAVORITES_KEY,
    SELECTED_FAVORITE_KEY,
    VALIDATION_PATTERNS
};
