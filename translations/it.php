<?php
// Global Italian language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/it/menu.php',
    require __DIR__.'/it/gdpr.php',
    require __DIR__.'/it/support.php',
    require __DIR__.'/it/landing.php',
    [
        'cookie_notice' => 'Questo sito utilizza cookie essenziali',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Applicazioni per la vita quotidiana',
        'meta_description' => 'Un insieme di applicazioni che ti aiutano con le tue attività quotidiane',
        'meta_keywords' => 'pagamenti, codice QR, comunità, archiviazione',
        'loading' => 'Caricamento...',
        'generating' => 'Generazione...',
        'share_text' => 'Condividi',
        'disclaimer_text' => 'iwantto.be non è responsabile per eventuali problemi legati all\'uso di questo servizio. L\'uso di questo strumento è a proprio rischio. Questo sito è concesso in licenza secondo la <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
