<?php
// Global Latvian language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/lv/menu.php',
    require __DIR__.'/lv/gdpr.php',
    require __DIR__.'/lv/support.php',
    [
        'cookie_notice' => 'Šī vietne izmanto nepieciešamos sīkfailus',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Ikdienas dzīves lietotnes',
        'meta_description' => 'Lietotņu komplekts, kas palīdz ikdienas uzdevumos',
        'meta_keywords' => 'maksājumi, QR kods, kopiena, disks',
        'loading' => 'Ielāde...',
        'generating' => 'Ģenerēšana...',
        'share_text' => 'Dalīties',
        'disclaimer_text' => 'iwantto.be nav atbildīgs par jebkādām problēmām, kas saistītas ar šī pakalpojuma izmantošanu. Šī rīka izmantošana ir uz jūsu pašu risku. Šī vietne ir licencēta saskaņā ar <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
