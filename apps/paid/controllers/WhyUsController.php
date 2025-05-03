<?php
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
 * WhyUsController handles the "Why Us" page
 * Displays the key features and advantages of using QR Transfer
 * Features a comparison table with other payment solutions
 */
class WhyUsController {
    /**
     * Renders the "Why Us" page view
     * Shows the comparison table and feature highlights
     */
    public function index() {
        $currentApp = 'paid';
        require_once __DIR__ . '/../views/why-us.php';
    }
}
