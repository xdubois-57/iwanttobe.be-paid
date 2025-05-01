<?php
/*
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
 * Main routing configuration file
 * 
 * This file sets up all the routes for the application, including:
 * - Page routes (GET requests for viewing pages)
 * - Language change routes (both GET and POST for changing the interface language)
 * - API routes (POST requests for AJAX operations)
 */

// Include necessary controller classes
require_once 'controllers/Router.php';
require_once 'controllers/LanguageController.php';

// Core global controllers remain at root-level controllers
require_once 'controllers/GDPRController.php';
require_once 'controllers/SupportController.php';

// Landing controller remains at root
require_once 'controllers/LandingController.php';

// Paid! app controllers
require_once 'apps/paid/controllers/HomeController.php';
require_once 'apps/paid/controllers/WhyUsController.php';
require_once 'apps/paid/controllers/QRController.php';

// Placeholder apps (involved, drive)
require_once 'apps/involved/controllers/InvolvedHomeController.php';
require_once 'apps/drive/controllers/DriveHomeController.php';

// Initialize the router
$router = new Router();

// Landing page (select app)
$router->get('/{lang}', 'LandingController@index');

// Paid! app routes
$router->get('/{lang}/paid', 'HomeController@index'); // QR generator home
$router->get('/{lang}/paid/why-us', 'WhyUsController@index');
$router->post('/{lang}/paid/api/generate-qr', 'QRController@generate');

// Involved! app routes (placeholder)
$router->get('/{lang}/involved', 'InvolvedHomeController@index');

// Drive app routes (placeholder)
$router->get('/{lang}/drive', 'DriveHomeController@index');

// Global pages accessible from any app
$router->get('/{lang}/gdpr', 'GDPRController@index');
$router->get('/{lang}/support', 'SupportController@index');

// Redirect root to detected language based on browser preferences
$router->get('/', function() {
    // Get available languages from config
    $config = require __DIR__ . '/config/languages.php';
    $availableLanguages = array_keys($config['available_languages']);
    
    // Get browser language preference
    $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2);
    
    // Use browser language if supported, otherwise default to English
    $lang = in_array($browserLang, $availableLanguages) ? $browserLang : 'en';
    
    header('Location: /' . $lang);
    exit;
});
