<?php
// Global Finnish language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/fi/menu.php',
    require __DIR__.'/fi/gdpr.php',
    require __DIR__.'/fi/support.php',
    require __DIR__.'/fi/landing.php',
    [
        'cookie_notice' => 'Tämä sivusto käyttää välttämättömiä evästeitä',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Arjen sovellukset',
        'meta_description' => 'Sovelluspaketti, joka auttaa päivittäisissä tehtävissä',
        'meta_keywords' => 'maksut, QR-koodi, yhteisö, tallennustila',
        'loading' => 'Ladataan...',
        'generating' => 'Luodaan...',
        'share_text' => 'Jaa',
        'disclaimer_text' => 'iwantto.be ei ole vastuussa palvelun käytöstä aiheutuvista ongelmista. Tämän työkalun käyttö on omalla vastuulla. Tämä verkkosivusto on lisensoitu <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a> lisenssin alaisuudessa.'
    ]
);
