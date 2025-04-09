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
require_once 'controllers/HomeController.php';
require_once 'controllers/GDPRController.php';
require_once 'controllers/WhyUsController.php';
require_once 'controllers/Router.php';
require_once 'controllers/LanguageController.php';
require_once 'controllers/QRController.php';
require_once 'controllers/SupportController.php';

// Initialize the router
$router = new Router();

// Page routes
// These routes handle the main navigation of the site
$router->get('/', 'HomeController@index');          // Homepage with QR code generation form
$router->get('/gdpr', 'GDPRController@index');     // GDPR/Privacy policy page
$router->get('/why-us', 'WhyUsController@index');     // Why Us page
$router->get('/support', 'SupportController@index');     // Support/Buy me a coffee page

// Language routes
// These routes handle language changes via both GET and POST requests
$router->post('/language/{lang}', 'LanguageController@change');  // For direct language links
$router->post('/language', 'LanguageController@change');        // For form submissions

// API routes
// These routes handle AJAX requests and return JSON responses
$router->post('/generate-qr', 'QRController@generate');     // Handles QR code generation requests
