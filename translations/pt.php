<?php
// Global Portuguese language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/pt/menu.php',
    require __DIR__.'/pt/gdpr.php',
    require __DIR__.'/pt/support.php',
    require __DIR__.'/pt/landing.php',
    [
        'cookie_notice' => 'Este site usa cookies essenciais',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Aplicações para o dia a dia',
        'meta_description' => 'Um conjunto de aplicações que ajudam nas suas tarefas diárias',
        'meta_keywords' => 'pagamentos, código QR, comunidade, armazenamento',
        'loading' => 'Carregando...',
        'generating' => 'Gerando...',
        'share_text' => 'Compartilhar',
        'disclaimer_text' => 'iwantto.be não se responsabiliza por quaisquer problemas relacionados ao uso deste serviço. O uso desta ferramenta é por sua conta e risco. Este site está licenciado sob a <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
