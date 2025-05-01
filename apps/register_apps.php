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
 * App registration script
 * 
 * This file scans the apps directory and loads all app.php files,
 * allowing for automatic discovery and registration of apps.
 */

// Each app registers itself with the AppRegistry when included
$appsDir = __DIR__;

// Get all app directories (excluding . and .. and this file)
$appDirs = array_filter(scandir($appsDir), function($item) use ($appsDir) {
    return $item !== '.' && $item !== '..' && $item !== 'register_apps.php' && is_dir($appsDir . '/' . $item);
});

// Include each app's registration file
foreach ($appDirs as $appDir) {
    $appFile = $appsDir . '/' . $appDir . '/app.php';
    if (file_exists($appFile)) {
        require_once $appFile;
    }
}
