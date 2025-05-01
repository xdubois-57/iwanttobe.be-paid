<?php
// Global Norwegian language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/no/menu.php',
    require __DIR__.'/no/gdpr.php',
    require __DIR__.'/no/support.php',
    [
        'cookie_notice' => 'Dette nettstedet bruker nødvendige informasjonskapsler',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Dagligdagse apper',
        'meta_description' => 'Et sett med applikasjoner som hjelper deg med daglige oppgaver',
        'meta_keywords' => 'betalinger, QR-kode, samfunn, lagring',
        'loading' => 'Laster...',
        'generating' => 'Genererer...',
        'share_text' => 'Del',
        'disclaimer_text' => 'iwantto.be er ikke ansvarlig for problemer knyttet til bruk av denne tjenesten. Bruk av dette verktøyet skjer på eget ansvar. Dette nettstedet er lisensiert under <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a> lisens.'
    ]
);
