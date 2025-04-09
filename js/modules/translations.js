/**
 * QR Transfer
 * Copyright (C) 2025 Xavier Dubois
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

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
