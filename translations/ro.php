<?php
// Global Romanian language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/ro/menu.php',
    require __DIR__.'/ro/gdpr.php',
    require __DIR__.'/ro/support.php',
    [
        'cookie_notice' => 'Acest site utilizează cookie-uri esențiale',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Aplicații pentru viața de zi cu zi',
        'meta_description' => 'Un set de aplicații care vă ajută cu sarcinile zilnice',
        'meta_keywords' => 'plăți, cod QR, comunitate, stocare',
        'loading' => 'Încărcare...',
        'generating' => 'Generare...',
        'share_text' => 'Partajare',
        'disclaimer_text' => 'iwantto.be nu este responsabil pentru nicio problemă legată de utilizarea acestui serviciu. Utilizarea acestui instrument se face pe propriul risc. Acest site este licențiat sub <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
