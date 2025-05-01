<?php
// Global Slovak language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/sk/menu.php',
    require __DIR__.'/sk/gdpr.php',
    require __DIR__.'/sk/support.php',
    [
        'cookie_notice' => 'Táto stránka používa nevyhnutné cookies',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Aplikácie pre každodenný život',
        'meta_description' => 'Sada aplikácií pre pomoc s každodennými úlohami',
        'meta_keywords' => 'platby, QR kód, komunita, úložisko',
        'loading' => 'Načítava sa...',
        'generating' => 'Generuje sa...',
        'share_text' => 'Zdieľať',
        'disclaimer_text' => 'iwantto.be nezodpovedá za žiadne problémy súvisiace s používaním tejto služby. Používanie tohto nástroja je na vaše vlastné riziko. Táto webová stránka je licencovaná pod <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
