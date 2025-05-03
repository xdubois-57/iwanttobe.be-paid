<?php
// Global Irish language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/ga/menu.php',
    require __DIR__.'/ga/gdpr.php',
    require __DIR__.'/ga/support.php',
    require __DIR__.'/ga/landing.php',
    [
        'cookie_notice' => 'Úsáideann an suíomh seo fianáin riachtanacha',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Feidhmchláir don saol laethúil',
        'meta_description' => 'Sraith feidhmchlár chun cabhrú le tascanna laethúla',
        'meta_keywords' => 'íocaíochtaí, cód QR, pobal, stóráil',
        'loading' => 'Ag lódáil...',
        'generating' => 'Ag giniúint...',
        'share_text' => 'Roinn',
        'disclaimer_text' => 'Níl iwantto.be freagrach as aon fhadhbanna a bhaineann le húsáid na seirbhíse seo. Is ar do phriacal féin atá úsáid an uirlise seo. Tá an suíomh gréasáin seo ceadúnaithe faoin <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
