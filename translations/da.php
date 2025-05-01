<?php
// Global Danish language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/da/menu.php',
    require __DIR__.'/da/gdpr.php',
    require __DIR__.'/da/support.php',
    require __DIR__.'/da/landing.php',
    [
        'cookie_notice' => 'Denne hjemmeside bruger nødvendige cookies',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Hverdagsapps',
        'meta_description' => 'Et sæt applikationer, der hjælper med daglige opgaver',
        'meta_keywords' => 'betalinger, QR-kode, fællesskab, opbevaring',
        'loading' => 'Indlæser...',
        'generating' => 'Genererer...',
        'share_text' => 'Del',
        'disclaimer_text' => 'iwantto.be er ikke ansvarlig for problemer i forbindelse med brugen af denne tjeneste. Brug af dette værktøj sker på eget ansvar. Denne hjemmeside er licenseret under <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
