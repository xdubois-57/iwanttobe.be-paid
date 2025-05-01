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
 * Contract that every app registration file must implement.
 */
interface AppInterface
{
    /**
     * Returns the app's URL slug (e.g. 'paid', 'drive')
     * @return string
     */
    public function getSlug(): string;

    /**
     * Returns the human-readable name (e.g. 'Paid!')
     * @return string
     */
    public function getDisplayName(): string;

    /**
     * Returns an array of menu items for this app
     * Each item must be an associative array with keys:
     *   - text: String label to display
     *   - url: URL pattern, may contain {lang} placeholder
     * 
     * @return array
     */
    public function getMenuItems(): array;

    /**
     * Registers all routes for this app
     * @param Router $router The router instance
     */
    public function registerRoutes(Router $router): void;
}
