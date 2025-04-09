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
