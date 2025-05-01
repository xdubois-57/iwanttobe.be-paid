<?php
// Global Icelandic language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/is/menu.php',
    require __DIR__.'/is/gdpr.php',
    require __DIR__.'/is/support.php',
    [
        'cookie_notice' => 'Þessi vefsíða notar nauðsynlegar kökur',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Dagleg tól',
        'meta_description' => 'Sett af forritum sem hjálpa í daglegum verkefnum',
        'meta_keywords' => 'greiðslur, QR kóði, samfélag, diskur',
        'loading' => 'Hleður...',
        'generating' => 'Bý til...',
        'share_text' => 'Deila',
        'disclaimer_text' => 'iwantto.be ber enga ábyrgð á neinum vandamálum sem tengjast notkun þessarar þjónustu. Notkun þessa tóls er á þína eigin ábyrgð. Þessi vefsíða er með leyfi samkvæmt <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
