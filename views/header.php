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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang->translate('app_name'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>
        /* Custom styles */
        nav {
            background: var(--secondary-background);
            border-bottom: 1px solid var(--secondary-border);
            padding: 0.25rem 0.4rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        nav a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .language-switcher {
            margin-left: 0.5rem;
        }

        .language-switcher .lang-link {
            padding: 0.2rem 0.3rem;
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
            font-size: 0.8rem;
            padding: 0.15rem;
            height: auto;
            min-height: unset;
        }
        
        .container-fluid {
            margin: 0 auto;
            padding: 0 0.5rem;
            max-width: 1200px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-header strong {
            font-size: 0.9rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
        }

        .nav-links ul {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .hamburger {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            margin: 0;
            display: none;
            line-height: 1;
        }

        .hamburger svg {
            width: 20px;
            height: 20px;
            display: block;
            fill: var(--primary);
        }

        .hamburger:hover {
            opacity: 0.8;
        }

        /* Mobile styles */
        @media (max-width: 768px) {
            .nav-header {
                width: 100%;
                display: flex;
                justify-content: space-between;
            }
            
            .hamburger {
                display: block;
                order: -1;
            }

            .nav-links {
                display: none;
                width: 100%;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background-color: var(--card-background-color, #ffffff);
                padding: 0.3rem 0.5rem;
                border-bottom: 1px solid var(--secondary-border);
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                z-index: 100;
            }

            .nav-links.active {
                display: block;
            }

            .nav-links ul {
                flex-direction: column;
                align-items: stretch;
                gap: 0;
            }

            .nav-links li {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .nav-links a {
                display: block;
                padding: 0.2rem 0;
                margin: 0;
            }

            .nav-links select {
                width: 100%;
                margin: 0.2rem 0;
            }
        }

        /* Desktop styles */
        @media (min-width: 769px) {
            .nav-header {
                width: auto;
            }

            .nav-links {
                display: flex !important;
                margin-left: auto;
            }
            
            .nav-links li {
                margin: 0;
                padding: 0;
                line-height: 1;
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
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                    </svg>
                </button>
                <a href="/" style="text-decoration: none; color: inherit;"><strong><?php echo $lang->translate('app_name'); ?></strong></a>
            </div>

            <div class="nav-links">
                <ul>
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

    <script src="/js/form-validation.js" type="module"></script>
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
