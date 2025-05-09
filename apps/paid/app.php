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

require_once __DIR__ . '/../../core/AppInterface.php';
require_once __DIR__ . '/../../core/AppRegistry.php';
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/WhyUsController.php';
require_once __DIR__ . '/controllers/QRController.php';

/**
 * Paid! app registration
 */
class PaidApp implements AppInterface
{
    /**
     * @inheritDoc
     */
    public function getSlug(): string
    {
        return 'paid';
    }
    
    /**
     * @inheritDoc
     */
    public function getDisplayName(): string
    {
        return 'Paid!';
    }
    
    /**
     * @inheritDoc
     */
    public function getMenuItems(): array
    {
        return [
            [
                'text' => 'menu_generate_qr',
                'url' => '/{lang}/paid'
            ],
            [
                'text' => 'menu_why_us',
                'url' => '/{lang}/paid/why-us'
            ]
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function registerRoutes(Router $router): void
    {
        // Redirect /paid to /{lang}/paid
        $router->get('/paid', function() {
            $lang = LanguageController::detectBrowserLanguage();
            header('Location: /' . $lang . '/paid');
            exit;
        });
        
        $router->get('/{lang}/paid', 'HomeController@index');
        $router->get('/{lang}/paid/why-us', 'WhyUsController@index');
        $router->post('/{lang}/paid/api/generate-qr', 'QRController@generate');
    }
    
    /**
     * @inheritDoc
     */
    public function getTranslationsPath(): string
    {
        return __DIR__ . '/translations';
    }
    
    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        $lang = \LanguageController::getInstance();
        $lang->loadAppTranslationsForPath($this->getTranslationsPath());
        return $lang->translate('description');
    }
    
    /**
     * Order index for menu and landing page
     */
    public function getOrder(): int
    {
        return 1;
    }
    
    /**
     * Get a shorter description for landing page
     */
    public function getShortDescription(): string
    {
        $lang = \LanguageController::getInstance();
        $lang->loadAppTranslationsForPath($this->getTranslationsPath());
        return $lang->translate('short_description');
    }
    
    /**
     * @inheritDoc
     */
    public function getJavaScriptFiles(): array
    {
        return [];
    }
}

// Auto-register this app when the file is included
AppRegistry::getInstance()->registerApp(new PaidApp());
