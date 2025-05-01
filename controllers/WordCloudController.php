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

require_once __DIR__ . '/../core/AppRegistry.php';
require_once __DIR__ . '/../controllers/LanguageController.php';

/**
 * WordCloudController
 * Generates data for the word cloud on the landing page
 */
class WordCloudController
{
    /**
     * Get all keywords from all apps and languages
     * 
     * @return array Word cloud data in format [[word, weight], [word, weight], ...]
     */
    public static function getWordCloudData()
    {
        $wordFrequencies = [];
        $registry = AppRegistry::getInstance();
        $apps = $registry->getApps();
        
        // Save original state before we start changing things
        $languageController = LanguageController::getInstance();
        $originalLang = $languageController->getCurrentLanguage();
        $originalApp = $registry->getCurrent() ? $registry->getCurrent()->getSlug() : 'landing';
        
        // Add app names to the word cloud (with high weight)
        foreach ($apps as $app) {
            $appName = $app->getDisplayName();
            if (!isset($wordFrequencies[$appName])) {
                $wordFrequencies[$appName] = 0;
            }
            $wordFrequencies[$appName] += 10; // Give app names higher weight
        }
        
        // Get all meta_keywords from all apps and languages
        $config = require __DIR__ . '/../config/languages.php';
        $languages = array_keys($config['available_languages']);
        
        // First get global keywords
        foreach ($languages as $lang) {
            // Temporarily switch language to get translations
            $languageController->setLanguage($lang);
            
            // Get meta_keywords
            $keywords = $languageController->translate('meta_keywords');
            if ($keywords) {
                self::processKeywords($keywords, $wordFrequencies);
            }
        }
        
        // Then get app-specific keywords
        foreach ($apps as $app) {
            $slug = $app->getSlug();
            
            foreach ($languages as $lang) {
                // Set current app context
                $registry->setCurrent($slug);
                
                // Temporarily switch language to get translations
                $languageController->setLanguage($lang);
                $languageController->loadTranslations();
                
                // Get app-specific meta_keywords if available
                $keywords = $languageController->translate('meta_keywords');
                if ($keywords) {
                    self::processKeywords($keywords, $wordFrequencies);
                }
            }
        }
        
        // Convert to format required by wordcloud2.js
        $result = [];
        foreach ($wordFrequencies as $word => $frequency) {
            $result[] = [$word, $frequency];
        }
        
        // IMPORTANT: Restore original app and language state before returning
        $registry->setCurrent($originalApp);
        $languageController->setLanguage($originalLang);
        $languageController->loadTranslations();
        
        return $result;
    }
    
    /**
     * Process keywords string and update frequency array
     * 
     * @param string $keywords Comma-separated keywords
     * @param array &$frequencies Reference to word frequency array
     */
    private static function processKeywords($keywords, &$frequencies)
    {
        $keywordArray = explode(',', $keywords);
        foreach ($keywordArray as $keyword) {
            $keyword = trim($keyword);
            if (!empty($keyword)) {
                if (!isset($frequencies[$keyword])) {
                    $frequencies[$keyword] = 0;
                }
                $frequencies[$keyword] += 1;
            }
        }
    }
    
    /**
     * Get word cloud data as JSON
     * 
     * @return string JSON encoded word cloud data
     */
    public static function getWordCloudDataJson()
    {
        return json_encode(self::getWordCloudData());
    }
}
