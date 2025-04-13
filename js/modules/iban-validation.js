/**
 * IBAN Validation Module
 * @module IBANValidation
 */

class IBANValidation {
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
        console.log('Reordered string:', reordered);
        
        // Convert letters to numbers (A=10, B=11, etc.)
        const numericString = reordered.replace(/[A-Z]/g, char => (char.charCodeAt(0) - 55).toString());
        console.log('Numeric string:', numericString);
        
        // Calculate the checksum using BigInt for large numbers
        const mod97 = BigInt(numericString) % 97n;
        console.log('Mod 97 result:', mod97);
        
        // The result must be 1 for a valid IBAN
        if (mod97 !== 1n) {
            console.log('Invalid IBAN: Checksum validation failed');
            return false;
        }

        console.log('ISO IBAN validation successful');
        return true;
    }

    /**
     * Validate an IBAN
     * @param {string} iban - The IBAN to validate
     * @returns {boolean} - True if valid, false otherwise
     */
    static validateIBAN(iban) {
        // Remove spaces and convert to uppercase
        iban = iban.replace(/\s/g, '').toUpperCase();

        // Validate using ISO standard
        return IBANValidation.validateISO(iban);
    }
}

export default IBANValidation;
