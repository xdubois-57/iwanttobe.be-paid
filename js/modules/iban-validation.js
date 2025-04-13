/**
 * IBAN Validation Module
 * @module IBANValidation
 */

class IBANValidation {
    /**
     * Validate a Belgian IBAN
     * @param {string} iban - The IBAN to validate
     * @returns {boolean} - True if valid, false otherwise
     */
    static validateBE(iban) {
        // Remove spaces and convert to uppercase
        iban = iban.replace(/\s/g, '').toUpperCase();
        console.log('Validating Belgian IBAN:', iban);

        // Check if it starts with BE
        if (!iban.startsWith('BE')) {
            console.log('Invalid IBAN: Must start with BE');
            return false;
        }

        // Check length (BE IBANs are 16 characters)
        if (iban.length !== 16) {
            console.log('Invalid IBAN: Length must be exactly 16 characters (current length:', iban.length, ')');
            return false;
        }

        // Extract BBAN (last 12 digits)
        const bban = iban.substring(4);
        
        // Check if BBAN contains only digits
        if (!/^[0-9]+$/.test(bban)) {
            console.log('Invalid IBAN: BBAN must contain only digits (found:', bban, ')');
            return false;
        }

        // Split BBAN into 10-digit number and 2-digit checksum
        const numberPart = bban.substring(0, 10);
        const bbanChecksum = bban.substring(10);

        // Calculate checksum: numberPart % 97
        const calculatedChecksum = parseInt(numberPart, 10) % 97;
        
        // Convert to string and pad with zeros if needed
        const calculatedChecksumStr = calculatedChecksum.toString().padStart(2, '0');

        // Compare with BBAN checksum
        if (calculatedChecksumStr !== bbanChecksum) {
            console.log('Invalid IBAN: Checksum validation failed');
            console.log('  - Number part:', numberPart);
            console.log('  - Calculated checksum:', calculatedChecksumStr);
            console.log('  - BBAN checksum:', bbanChecksum);
            return false;
        }

        console.log('IBAN validation successful');
        return true;
    }

    /**
     * Validate an IBAN according to ISO 13616 standard
     * @param {string} iban - The IBAN to validate
     * @returns {boolean} - True if valid, false otherwise
     */
    static validateISO(iban) {
        // Remove spaces and convert to uppercase
        iban = iban.replace(/\s/g, '').toUpperCase();
        console.log('Validating IBAN according to ISO 13616:', iban);

        // Check length (must be between 15 and 34 characters)
        if (iban.length < 15 || iban.length > 34) {
            console.log('Invalid IBAN: Length must be between 15 and 34 characters (current length:', iban.length, ')');
            return false;
        }

        // Check if first two characters are letters (country code)
        if (!/^[A-Z]{2}/.test(iban)) {
            console.log('Invalid IBAN: Must start with two letters for country code');
            return false;
        }

        // Check if remaining characters are digits (checksum and BBAN)
        if (!/^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/.test(iban)) {
            console.log('Invalid IBAN: Invalid characters in IBAN');
            return false;
        }

        // Move the first 4 characters to the end
        const reordered = iban.substring(4) + iban.substring(0, 4);
        
        // Convert letters to numbers (A=10, B=11, etc.)
        const numericString = reordered.replace(/[A-Z]/g, char => char.charCodeAt(0) - 55);
        
        // Calculate the checksum
        const mod97 = parseInt(numericString, 10) % 97;
        
        // The result must be 1 for a valid IBAN
        if (mod97 !== 1) {
            console.log('Invalid IBAN: Checksum validation failed');
            console.log('  - Reordered string:', reordered);
            console.log('  - Numeric string:', numericString);
            console.log('  - Mod 97 result:', mod97);
            return false;
        }

        console.log('IBAN validation successful');
        return true;
    }

    /**
     * Validate an IBAN based on its country code
     * @param {string} iban - The IBAN to validate
     * @returns {boolean} - True if valid, false otherwise
     */
    static validateIBAN(iban) {
        // Remove spaces and convert to uppercase
        iban = iban.replace(/\s/g, '').toUpperCase();

        // Get country code
        const countryCode = iban.substring(0, 2);

        // Always execute the default validation.
        // It is not blocking though, I only use it to log for now.
        // If it works well, I will add it as default below.
        IBANValidation.validateISO(iban);

        // Validate based on country code
        switch (countryCode) {
            case 'BE':
                return IBANValidation.validateBE(iban);
            default:
                return true;
        }
    }
}

export default IBANValidation;
