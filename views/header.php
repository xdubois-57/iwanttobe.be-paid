<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../controllers/LanguageController.php';
$lang = LanguageController::getInstance();

// Prepare translations for JavaScript
$jsTranslations = [
    'error_required_fields' => $lang->translate('error_required_fields'),
    'error_saving_favorite' => $lang->translate('error_saving_favorite'),
    'favorite_updated' => $lang->translate('favorite_updated'),
    'favorite_duplicate' => $lang->translate('favorite_duplicate'),
    'favorite_saved' => $lang->translate('favorite_saved'),
    'share_text' => $lang->translate('share_text')
];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang->translate('app_name'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <script>
        // Make translations available to JavaScript
        window.translations = <?php echo json_encode($jsTranslations); ?>;
    </script>
    <style>
        /* Custom styles */
        nav {
            background: var(--secondary-background);
            border-bottom: 1px solid var(--secondary-border);
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        nav a {
            color: var(--primary);
            text-decoration: none;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .language-switcher {
            margin-left: 1rem;
        }

        .language-switcher .lang-link {
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
        }

        .language-switcher .lang-link.active {
            background: var(--primary);
            color: var(--primary-inverse);
            text-decoration: none;
        }

        .language-switcher .lang-link:hover:not(.active) {
            background: var(--secondary);
        }

        nav select {
            background-color: var(--secondary-background);
            border: 1px solid var(--secondary-border);
            margin-bottom: 0;
        }
        
        .container-fluid {
            margin: 0 auto;
            padding: 0 1rem;
            max-width: 1200px;
        }

        .nav-header {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            width: 100%;
            gap: 1rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
        }

        .nav-links ul {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .hamburger {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
            margin: 0;
            display: none;
        }

        .hamburger:hover {
            opacity: 0.8;
        }

        /* Mobile styles */
        @media (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .nav-links {
                display: none;
                width: 100%;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid var(--secondary-border);
            }

            .nav-links.active {
                display: block;
            }

            .nav-links ul {
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
            }

            .nav-links li {
                width: 100%;
            }

            .nav-links a {
                display: block;
                padding: 0.5rem 0;
            }

            .nav-links select {
                width: 100%;
            }

            .container-fluid {
                flex-direction: column;
                align-items: stretch;
            }
        }

        /* Desktop styles */
        @media (min-width: 769px) {
            .container-fluid {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .nav-header {
                width: auto;
            }

            .nav-links {
                display: flex !important;
                margin-left: auto;
            }

            .hamburger {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="container-fluid">
            <div class="nav-header">
                <button class="hamburger" onclick="toggleMenu()">
                    <svg width="24" height="24" viewBox="0 0 24 24">
                        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                    </svg>
                </button>
                <strong><?php echo $lang->translate('app_name'); ?></strong>
            </div>

            <div class="nav-links">
                <ul style="display: flex; gap: 1rem; list-style: none; margin: 0;">
                    <li><a href="/"><?php echo $lang->translate('menu_home'); ?></a></li>
                    <li><a href="/why-us"><?php echo $lang->translate('menu_why_us'); ?></a></li>
                    <li><a href="/about"><?php echo $lang->translate('menu_about'); ?></a></li>
                    <li><a href="/gdpr"><?php echo $lang->translate('menu_gdpr'); ?></a></li>
                    <li>
                        <select onchange="changeLanguage(this.value)" aria-label="<?php echo $lang->translate('language'); ?>">
                            <?php
                            $config = require __DIR__ . '/../config/languages.php';
                            $languages = $config['available_languages'];
                            asort($languages); // Sort languages by name
                            foreach ($languages as $code => $name) {
                                $selected = $lang->getCurrentLanguage() === $code ? 'selected' : '';
                                echo "<option value=\"$code\" $selected>$name</option>";
                            }
                            ?>
                        </select>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <script src="/js/form-validation.js"></script>
    <script>
        function toggleMenu() {
            document.querySelector('.nav-links').classList.toggle('active');
        }

        async function changeLanguage(lang) {
            // Create a form and submit it to properly handle the language change
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/language/' + lang;
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'lang';
            input.value = lang;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
