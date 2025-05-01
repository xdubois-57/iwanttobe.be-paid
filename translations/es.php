<?php
// Global Spanish language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/es/menu.php',
    require __DIR__.'/es/gdpr.php',
    require __DIR__.'/es/support.php',
    [
        'cookie_notice' => 'Este sitio utiliza cookies esenciales',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Aplicaciones para la vida diaria',
        'meta_description' => 'Un conjunto de aplicaciones que te ayudan con tus tareas diarias',
        'meta_keywords' => 'pagos, código QR, comunidad, almacenamiento',
        'loading' => 'Cargando...',
        'generating' => 'Generando...',
        'share_text' => 'Compartir',
        'disclaimer_text' => 'iwantto.be no se hace responsable de problemas relacionados con el uso de este servicio. El uso de esta herramienta es bajo su propio riesgo. Este sitio web está licenciado bajo la <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
