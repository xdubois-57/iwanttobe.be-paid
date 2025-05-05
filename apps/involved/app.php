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
require_once __DIR__ . '/controllers/AjaxController.php';

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
        $router->get('/{lang}/involved', 'InvolvedHomeController@index');
        $router->post('/{lang}/involved/create', 'InvolvedHomeController@create');
        $router->post('/{lang}/involved/join', 'InvolvedHomeController@join');
        $router->get('/{lang}/involved/{code}/wordcloud/{wcid}', 'InvolvedHomeController@showWordCloud');
        $router->get('/{lang}/involved/{code}/wordcloud/{wcid}/words', 'InvolvedHomeController@getWordCloudWords');
        $router->post('/{lang}/involved/{code}/wordcloud/create', 'InvolvedHomeController@createWordCloud');
        $router->post('/{lang}/involved/{code}/wordcloud/{wcid}/delete', 'InvolvedHomeController@deleteWordCloud');
        $router->get('/{lang}/involved/{code}/wordcloud/{wcid}/add', 'InvolvedHomeController@showAddWordForm');
        $router->post('/{lang}/involved/{code}/wordcloud/{wcid}/add', 'InvolvedHomeController@addWord');
        $router->post('/{lang}/involved/{code}/wordcloud/{wcid}/word/delete', 'InvolvedHomeController@deleteWord');
        $router->get('/{lang}/involved/{code}', 'InvolvedHomeController@show');
        $router->post('/{lang}/involved/verify-password', 'InvolvedHomeController@verifyPassword');
        
        // AJAX endpoints
        $router->post('/{lang}/involved/ajax/like', 'InvolvedAjaxController@incrementLikes');
        $router->get('/{lang}/involved/ajax/likes', 'InvolvedAjaxController@getLikes');
        $router->post('/{lang}/involved/ajax/presence', 'InvolvedAjaxController@updatePresence');
        $router->get('/{lang}/involved/ajax/presence', 'InvolvedAjaxController@getPresence');
        $router->post('/{lang}/involved/ajax/set_active_url', 'InvolvedAjaxController@setActiveUrl');
        $router->post('/{lang}/involved/ajax/emoji', 'InvolvedAjaxController@appendEmoji');
        $router->get('/{lang}/involved/ajax/emoji', 'InvolvedAjaxController@getEmojis');
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
            'js/OverlayClientHelper.js',
            'js/eventQrBlock.js',
            '/vendor/timdream/wordcloud2.js',
            '/js/wordcloud-wrapper.js', // Add wrapper to fix wordcloud2.js errors
            '/js/error-catcher.js',     // Error catcher for debugging
            '/js/wordcloud.js'
        ];
    }
}

// Auto-register this app when the file is included
AppRegistry::getInstance()->registerApp(new InvolvedApp());
