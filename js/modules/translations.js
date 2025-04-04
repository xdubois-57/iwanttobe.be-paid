/**
 * Get a translation for a given key
 * @param {string} key - The translation key
 * @returns {string} - The translated text or the key if translation not found
 */
export function translate(key) {
    // Use window.t (PHP translations) if available, otherwise return key
    if (typeof window.t === 'function') {
        return window.t(key) || key;
    }
    return key;
}

export default { translate };
