<?php
// Global Swedish language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/sv/menu.php',
    require __DIR__.'/sv/gdpr.php',
    require __DIR__.'/sv/support.php',
    [
        'cookie_notice' => 'Denna webbplats använder nödvändiga cookies',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Applikationer för vardagslivet',
        'meta_description' => 'En samling applikationer som hjälper dig med vardagliga uppgifter',
        'meta_keywords' => 'betalningar, QR-kod, gemenskap, lagring',
        'loading' => 'Laddar...',
        'generating' => 'Genererar...',
        'share_text' => 'Dela',
        'disclaimer_text' => 'iwantto.be är inte ansvarig för problem relaterade till användningen av denna tjänst. Användningen av detta verktyg är på egen risk. Denna webbplats är licensierad under <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
