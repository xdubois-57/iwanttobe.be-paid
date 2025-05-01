<?php
// Global Polish language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/pl/menu.php',
    require __DIR__.'/pl/gdpr.php',
    require __DIR__.'/pl/support.php',
    require __DIR__.'/pl/landing.php',
    [
        'cookie_notice' => 'Ta strona używa niezbędnych plików cookie',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Aplikacje do codziennego życia',
        'meta_description' => 'Zestaw aplikacji, które pomagają w codziennych zadaniach',
        'meta_keywords' => 'płatności, kod QR, społeczność, przechowywanie',
        'loading' => 'Ładowanie...',
        'generating' => 'Generowanie...',
        'share_text' => 'Udostępnij',
        'disclaimer_text' => 'iwantto.be nie ponosi odpowiedzialności za jakiekolwiek problemy związane z korzystaniem z tej usługi. Korzystanie z tego narzędzia odbywa się na własne ryzyko. Ta strona jest licencjonowana na podstawie <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
