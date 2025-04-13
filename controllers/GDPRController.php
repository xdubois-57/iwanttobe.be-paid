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
 * GDPRController handles the GDPR (General Data Protection Regulation) page
 * Displays the privacy policy and data protection information
 * Ensures compliance with EU data protection laws
 */
class GDPRController {
    /**
     * Renders the GDPR policy page view
     * Shows the privacy policy and data handling information
     */
    public function index() {
        include __DIR__ . '/../views/gdpr.php';
    }
}
