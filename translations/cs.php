<?php
// Global Czech language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/cs/menu.php',
    require __DIR__.'/cs/gdpr.php',
    require __DIR__.'/cs/support.php',
    require __DIR__.'/cs/landing.php',
    [
        'cookie_notice' => 'Tento web používá nezbytné cookies',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Aplikace pro každodenní život',
        'meta_description' => 'Sada aplikací, které vám pomohou s každodenními úkoly',
        'meta_keywords' => 'platby, QR kód, komunita, úložiště',
        'loading' => 'Načítání...',
        'generating' => 'Generování...',
        'share_text' => 'Sdílet',
        'disclaimer_text' => 'iwantto.be není zodpovědný za jakékoli problémy spojené s používáním této služby. Použití tohoto nástroje je na vlastní riziko. Tato webová stránka je licencována pod <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
