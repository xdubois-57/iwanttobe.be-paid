<?php
// Global Estonian language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/et/menu.php',
    require __DIR__.'/et/gdpr.php',
    require __DIR__.'/et/support.php',
    require __DIR__.'/et/landing.php',
    [
        'cookie_notice' => 'See sait kasutab olulisi küpsiseid',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Igapäevaelu rakendused',
        'meta_description' => 'Rakenduste komplekt igapäevaste ülesannete jaoks',
        'meta_keywords' => 'maksed, QR-kood, kogukond, salvestamine',
        'loading' => 'Laadimine...',
        'generating' => 'Genereerimine...',
        'share_text' => 'Jaga',
        'disclaimer_text' => 'iwantto.be ei vastuta selle teenuse kasutamisega seotud probleemide eest. Selle tööriista kasutamine toimub omal vastutusel. See veebisait on litsentseeritud <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a> all.'
    ]
);
