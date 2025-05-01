<?php
// Global French language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/fr/menu.php',
    require __DIR__.'/fr/gdpr.php',
    require __DIR__.'/fr/support.php',
    [
        'cookie_notice' => 'Ce site utilise des cookies essentiels',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Applications pour la vie quotidienne',
        'meta_description' => 'Une suite d\'applications pour vous aider dans les tâches quotidiennes',
        'meta_keywords' => 'paiements, code QR, communauté, drive',
        'loading' => 'Chargement...',
        'generating' => 'Génération...',
        'share_text' => 'Partager',
        'disclaimer_text' => 'iwantto.be n\'est pas responsable des problèmes liés à l\'utilisation de ce service. L\'utilisation de cet outil est à vos propres risques. Ce site web est soumis à la <a href="https://www.gnu.org/licenses/gpl-3.0.fr.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
