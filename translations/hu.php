<?php
// Global Hungarian language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/hu/menu.php',
    require __DIR__.'/hu/gdpr.php',
    require __DIR__.'/hu/support.php',
    [
        'cookie_notice' => 'Ez a weboldal szükséges sütiket használ',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Mindennapi alkalmazások',
        'meta_description' => 'Alkalmazáskészlet, amely segít a mindennapi feladatokban',
        'meta_keywords' => 'fizetések, QR-kód, közösség, tárhely',
        'loading' => 'Betöltés...',
        'generating' => 'Generálás...',
        'share_text' => 'Megosztás',
        'disclaimer_text' => 'Az iwantto.be nem vállal felelősséget semmilyen problémáért, amely a szolgáltatás használatából ered. Az eszköz használata saját felelősségre történik. Ez a weboldal a <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a> alatt van licencelve.',
        'language_code' => 'hu',
        'language_direction' => 'ltr',
        'language_flag' => 'hu'
    ]
);
