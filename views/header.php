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
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Paid!'; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : $lang->translate('meta_description'); ?>">
    <meta name="keywords" content="<?php echo $lang->translate('meta_keywords'); ?>">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="<?php echo isset($pageTitle) ? $pageTitle : 'Paid!'; ?>">
    <meta property="og:description" content="<?php echo isset($pageDescription) ? $pageDescription : $lang->translate('meta_description'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://iwantto.be/<?php echo $lang->getCurrentLanguage(); ?><?php echo $_SERVER['REQUEST_URI']; ?>">
    <meta name="google-site-verification" content="VlG6fhlOB4LhJf2uMGbByhfL2mJ3ilaltvhI7i0ChnA" />
    <link rel="canonical" href="https://iwantto.be/<?php echo $lang->getCurrentLanguage(); ?><?php echo $_SERVER['REQUEST_URI']; ?>">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="/css/styles.css">
    <?php
    $config = require __DIR__ . '/../config/languages.php';
    $languages = $config['available_languages'];
    asort($languages); // Sort languages by name
    // Add hreflang alternate links for all supported languages
    foreach ($languages as $code => $name) {
        echo '<link rel="alternate" hreflang="' . $code . '" href="https://iwantto.be/' . $code . '/" />';
    }
    ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo isset($pageTitle) ? $pageTitle : 'Paid!'; ?>">
    <meta name="twitter:description" content="<?php echo isset($pageDescription) ? $pageDescription : $lang->translate('meta_description'); ?>">
    <!-- If you want to add a twitter:image, add it here -->
    <?php if (in_array($lang->getCurrentLanguage(), ['ar', 'he', 'fa', 'ur'])): ?>
        <script>document.documentElement.setAttribute('dir', 'rtl');</script>
    <?php endif; ?>
    <script>
    // Expose PHP translations to JavaScript
    window.t = function(key) {
        const translations = {
            'save_favorite': '<?php echo $lang->translate('save_favorite'); ?>',
            'update_favorite': '<?php echo $lang->translate('update_favorite'); ?>',
            // Add other frequently used translations
            'app_name': 'iwantto.be Paid',
            'generating': '<?php echo $lang->translate('generating'); ?>',
            'share_text': '<?php echo $lang->translate('share_text'); ?>'
        };
        return translations[key] || key;
    };
    </script>
</head>
<body>
    <nav>
        <div class="container-fluid">
            <div class="nav-header">
                <button class="hamburger" onclick="toggleMenu()">
                    <svg viewBox="0 0 100 80" width="20" height="20">
                        <rect width="100" height="10"></rect>
                        <rect y="30" width="100" height="10"></rect>
                        <rect y="60" width="100" height="10"></rect>
                    </svg>
                </button>
                <a href="/<?php echo $lang->getCurrentLanguage(); ?>" class="app-name"><em style="font-size: 0.6em;">iwantto.be</em> <span style="font-size: 1.1em; font-weight: bold;">Paid!</span></a>
            </div>

            <div class="nav-links">
                <ul>
                    <li><a href="/<?php echo $lang->getCurrentLanguage(); ?>"><?php echo $lang->translate('menu_home'); ?></a></li>
                    <li><a href="/<?php echo $lang->getCurrentLanguage(); ?>/why-us"><?php echo $lang->translate('menu_why_us'); ?></a></li>
                    <li><a href="/<?php echo $lang->getCurrentLanguage(); ?>/support"><?php echo $lang->translate('menu_support'); ?></a></li>
                    <li><a href="/<?php echo $lang->getCurrentLanguage(); ?>/gdpr"><?php echo $lang->translate('menu_gdpr'); ?></a></li>
                    <li class="language-selector">
                        <select onchange="changeLanguage(this.value)" aria-label="<?php echo $lang->translate('language'); ?>">
                            <?php
                            asort($languages); // Sort languages by name
                            foreach ($languages as $code => $name) {
                                $selected = $lang->getCurrentLanguage() === $code ? 'selected' : '';
                                echo "<option value=\"$code\" $selected>$name</option>";
                            }
                            ?>
                        </select>
                    </li>
                    <li class="theme-selector">
                        <select id="theme-selector" aria-label="<?php echo $lang->translate('theme'); ?>">
                            <option value="light"><?php echo $lang->translate('theme_light'); ?></option>
                            <option value="dark"><?php echo $lang->translate('theme_dark'); ?></option>
                            <option value="auto"><?php echo $lang->translate('theme_auto'); ?></option>
                        </select>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <script src="/js/form-validation.js" type="module"></script>
    <script>
        function toggleMenu() {
            document.querySelector('.nav-links').classList.toggle('active');
        }

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const navLinks = document.querySelector('.nav-links');
            const hamburger = document.querySelector('.hamburger');
            
            // Only process if we're on mobile (menu toggle is visible) and the menu is open
            if (window.getComputedStyle(hamburger).display !== 'none' && navLinks.classList.contains('active')) {
                // Check if the click was outside the menu and not on the hamburger button
                if (!navLinks.contains(event.target) && !hamburger.contains(event.target)) {
                    navLinks.classList.remove('active');
                }
            }
        });

        function changeLanguage(lang) {
            // Change URL to the selected language, preserving the current path after the language code
            const pathParts = window.location.pathname.split('/').filter(Boolean);
            if (pathParts.length === 0) {
                window.location.href = '/' + lang;
                return;
            }
            // Replace first segment if it's a supported language, otherwise add it
            const supported = <?php echo json_encode(array_keys($languages)); ?>;
            if (supported.includes(pathParts[0])) {
                pathParts[0] = lang;
            } else {
                pathParts.unshift(lang);
            }
            window.location.href = '/' + pathParts.join('/');
        }
    </script>
