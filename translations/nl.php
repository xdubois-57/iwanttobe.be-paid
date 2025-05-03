<?php
// Global Dutch language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/nl/menu.php',
    require __DIR__.'/nl/gdpr.php',
    require __DIR__.'/nl/support.php',
    require __DIR__.'/nl/landing.php',
    [
        'cookie_notice' => 'Deze site gebruikt essentiële cookies',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Applicaties voor het dagelijks leven',
        'meta_description' => 'Een set van applicaties die je helpen met dagelijkse taken',
        'meta_keywords' => 'betalingen, QR-code, gemeenschap, opslag',
        'loading' => 'Laden...',
        'generating' => 'Genereren...',
        'share_text' => 'Delen',
        'disclaimer_text' => 'iwantto.be is niet aansprakelijk voor problemen die gerelateerd zijn aan het gebruik van deze dienst. Het gebruik van dit hulpmiddel is op eigen risico. Deze website is gelicenseerd onder de <a href="https://www.gnu.org/licenses/gpl-3.0.nl.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
