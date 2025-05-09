<?php
/**
 * iwanttobe.be
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

require_once __DIR__ . '/controllers/InvolvedHomeController.php';
require_once __DIR__ . '/controllers/InvolvedApiController.php';

class InvolvedApp implements AppInterface {
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'involved';
    }

    /**
     * @inheritDoc
     */
    public function getIcon(): ?string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>';
    }

    /**
     * @inheritDoc
     */
    public function getSlug(): string
    {
        return 'involved';
    }
    
    /**
     * @inheritDoc
     */
    public function getDisplayName(): string
    {
        return 'Involved!';
    }
    
    /**
     * @inheritDoc
     */
    public function getMenuItems(): array
    {
        return [
            [
                'text' => 'menu_word_cloud',
                'url' => '/{lang}/involved'
            ]
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function registerRoutes(Router $router): void
    {
        // Redirect /involved to /{lang}/involved
        $router->get('/involved', function() {
            $lang = \LanguageController::detectBrowserLanguage();
            header('Location: /' . $lang . '/involved');
            exit;
        });
        
        $router->get('/{lang}/involved', 'InvolvedHomeController@index');
        $router->post('/{lang}/involved/create', 'InvolvedHomeController@create');
        $router->post('/{lang}/involved/join', 'InvolvedHomeController@join');
        
        $router->get('/{lang}/involved/{code}', 'InvolvedHomeController@show');
        $router->post('/{lang}/involved/verify-password', 'InvolvedHomeController@verifyPassword');
        
        // Event item routes
        $router->get('/{lang}/involved/{code}/eventitem/{itemid}', 'InvolvedHomeController@showEventItem');
        $router->get('/{lang}/involved/{code}/eventitem/{itemid}/answers', 'InvolvedHomeController@getEventItemAnswers');
        $router->post('/{lang}/involved/{code}/eventitem/create', 'InvolvedHomeController@createEventItem');
        $router->post('/{lang}/involved/{code}/eventitem/{itemid}/answer/add', 'InvolvedHomeController@addEventItemAnswer');
        $router->get('/{lang}/involved/{code}/eventitem/{itemid}/answer', 'InvolvedHomeController@showEventItemAnswerForm');
        $router->post('/{lang}/involved/{code}/eventitem/{itemid}/answer/{answerid}/delete', 'InvolvedHomeController@deleteEventItemAnswer');
        $router->post('/{lang}/involved/{code}/eventitem/{itemid}/delete', 'InvolvedHomeController@deleteEventItem');
        $router->post('/{lang}/involved/{code}/eventitem/reorder', 'InvolvedHomeController@reorderEventItems');
        $router->get('/{lang}/involved/{code}/eventitem/{itemid}/add', 'InvolvedHomeController@showAddItemForm');
        $router->post('/{lang}/involved/{code}/eventitem/{itemid}/add', 'InvolvedHomeController@addItem');
        
        // AJAX endpoints
        
        // Event-specific AJAX endpoints
        $router->post('/{lang}/involved/{code}/presence', 'InvolvedApiController@updatePresence');
        $router->get('/{lang}/involved/{code}/presence', 'InvolvedApiController@getPresence');
        $router->post('/{lang}/involved/{code}/active-url', 'InvolvedApiController@setActiveUrl');
        $router->post('/{lang}/involved/{code}/emoji', 'InvolvedApiController@appendEmoji');
        $router->get('/{lang}/involved/{code}/emoji', 'InvolvedApiController@getEmojis');
        
        // Legacy routes for backward compatibility - to be removed in future
        $router->post('/{lang}/involved/ajax/presence', 'InvolvedApiController@updatePresence');
        $router->get('/{lang}/involved/ajax/presence', 'InvolvedApiController@getPresence');
        $router->post('/{lang}/involved/ajax/set_active_url', 'InvolvedApiController@setActiveUrl');
        $router->post('/{lang}/involved/ajax/emoji', 'InvolvedApiController@appendEmoji');
        $router->get('/{lang}/involved/ajax/emoji', 'InvolvedApiController@getEmojis');
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
     * Get a shorter description for landing page
     */
    public function getShortDescription(): string
    {
        $lang = \LanguageController::getInstance();
        $lang->loadAppTranslationsForPath($this->getTranslationsPath());
        return $lang->translate('short_description');
    }
    
    /**
     * Order index for menu and landing page
     */
    public function getOrder(): int
    {
        return 2;
    }
    
    /**
     * @inheritDoc
     */
    public function getJavaScriptFiles(): array
    {
        return [
            'js/OverlayObjectHelper.js',
            'js/eventQrBlock.js',
            // Wordcloud scripts removed
            '/js/error-catcher.js'     // Error catcher for debugging
        ];
    }
}

// Auto-register this app when the file is included
AppRegistry::getInstance()->registerApp(new InvolvedApp());
