<?php
// Global Slovenian language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/sl/menu.php',
    require __DIR__.'/sl/gdpr.php',
    require __DIR__.'/sl/support.php',
    require __DIR__.'/sl/landing.php',
    [
        'cookie_notice' => 'Ta spletna stran uporablja nujne piškotke',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Aplikacije za vsakdanje življenje',
        'meta_description' => 'Nabor aplikacij, ki pomagajo pri vsakodnevnih opravilih',
        'meta_keywords' => 'plačila, QR koda, skupnost, shramba',
        'loading' => 'Nalaganje...',
        'generating' => 'Generiranje...',
        'share_text' => 'Deli',
        'disclaimer_text' => 'iwantto.be ni odgovoren za težave, povezane z uporabo te storitve. Uporaba tega orodja je na lastno odgovornost. To spletno mesto je licencirano pod <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
