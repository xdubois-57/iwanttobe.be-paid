<?php
// Global Croatian language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/hr/menu.php',
    require __DIR__.'/hr/gdpr.php',
    require __DIR__.'/hr/support.php',
    require __DIR__.'/hr/landing.php',
    [
        'cookie_notice' => 'Ova stranica koristi nužne kolačiće',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Aplikacije za svakodnevni život',
        'meta_description' => 'Skup aplikacija koje vam pomažu u svakodnevnim zadacima',
        'meta_keywords' => 'plaćanja, QR kod, zajednica, disk',
        'loading' => 'Učitavanje...',
        'generating' => 'Generiranje...',
        'share_text' => 'Podijeli',
        'disclaimer_text' => 'iwantto.be nije odgovoran za bilo kakve probleme povezane s korištenjem ove usluge. Korištenje ovog alata je na vlastitu odgovornost. Ova web stranica je licencirana pod <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
