/**
 * Get a translation for a given key
 * @param {string} key - The translation key
 * @returns {string} - The translated text or the key if translation not found
 */
function translate(key) {
    return window.translations?.[key] || window.t?.(key) || key;
}

export default { translate };
