/**
 * Get a translation for a given key
 * @param {string} key - The translation key
 * @returns {string} - The translated text or the key if translation not found
 */
function translate(key) {
    // t() is defined globally in the PHP template
    return window.t ? window.t(key) : key;
}

export default { translate };
