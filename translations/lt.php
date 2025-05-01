<?php
// Global Lithuanian language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/lt/menu.php',
    require __DIR__.'/lt/gdpr.php',
    require __DIR__.'/lt/support.php',
    require __DIR__.'/lt/landing.php',
    [
        'cookie_notice' => 'Ši svetainė naudoja būtinus slapukus',
        'cookie_accept' => 'Gerai',
        'meta_title' => 'iwantto.be - Kasdienio gyvenimo programos',
        'meta_description' => 'Programų rinkinys, padedantis atlikti kasdienes užduotis',
        'meta_keywords' => 'mokėjimai, QR kodas, bendruomenė, saugykla',
        'loading' => 'Kraunama...',
        'generating' => 'Generuojama...',
        'share_text' => 'Dalintis',
        'disclaimer_text' => 'iwantto.be neatsako už jokias problemas, susijusias su šios paslaugos naudojimu. Šio įrankio naudojimas yra jūsų pačių rizika. Ši svetainė licencijuota pagal <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
