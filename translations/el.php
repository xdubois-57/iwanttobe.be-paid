<?php
// Global Greek language definition file
// Contains common translations shared across all apps
// Last updated: 2025-05-01

return array_merge(
    require __DIR__.'/el/menu.php',
    require __DIR__.'/el/gdpr.php',
    require __DIR__.'/el/support.php',
    require __DIR__.'/el/landing.php',
    [
        'cookie_notice' => 'Αυτός ο ιστότοπος χρησιμοποιεί απαραίτητα cookies',
        'cookie_accept' => 'ΟΚ',
        'meta_title' => 'iwantto.be - Εφαρμογές για την καθημερινή ζωή',
        'meta_description' => 'Ένα σύνολο εφαρμογών που σας βοηθούν με καθημερινές εργασίες',
        'meta_keywords' => 'πληρωμές, QR code, κοινότητα, αποθήκευση',
        'loading' => 'Φόρτωση...',
        'generating' => 'Δημιουργία...',
        'share_text' => 'Κοινοποίηση',
        'disclaimer_text' => 'Το iwantto.be δεν φέρει ευθύνη για οποιαδήποτε προβλήματα σχετικά με τη χρήση αυτής της υπηρεσίας. Η χρήση αυτού του εργαλείου γίνεται με δική σας ευθύνη. Αυτή η ιστοσελίδα είναι υπό την άδεια <a href="https://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License v3.0 (GPLv3)</a>.'
    ]
);
