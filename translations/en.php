<?php
// Global English language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/en/menu.php',
    require __DIR__.'/en/gdpr.php',
    require __DIR__.'/en/support.php',
    require __DIR__.'/en/landing.php',
    [
        'cookie_notice' => 'This site uses essential cookies',
        'cookie_accept' => 'OK',
        'meta_title' => 'iwantto.be - Apps for everyday life',
        'meta_description' => 'A suite of applications to help with everyday tasks',
        'meta_keywords' => 'payments, qr code, community, drive',
        'loading' => 'Loading...',
        'generating' => 'Generating...',
        'share_text' => 'Share',
        'disclaimer_text' => 'iwantto.be is not liable for any issues related to the use of this service. Use of this tool is at your own risk. This website is licensed under the <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
