<?php

$translations = [
    'fr' => [
        'error_required_fields' => 'Veuillez remplir correctement tous les champs obligatoires',
        'error_saving_favorite' => 'Erreur lors de l\'enregistrement du favori',
        'favorite_updated' => 'Favori mis à jour',
        'favorite_duplicate' => 'Ce bénéficiaire est déjà enregistré dans vos favoris',
        'favorite_saved' => 'Enregistré dans les favoris',
        'share_text' => 'Scannez avec votre application bancaire !',
        'generating' => 'Génération en cours...',
        'failed_to_generate_qr' => 'Échec de la génération du code QR. Veuillez réessayer.',
        'invalid_format' => 'Format invalide',
        'confirm_delete_favorite' => 'Êtes-vous sûr de vouloir supprimer ce favori ?',
        'error_deleting_favorite' => 'Erreur lors de la suppression du favori',
        'error' => 'Erreur'
    ],
    'nl' => [
        'error_required_fields' => 'Vul alle verplichte velden correct in',
        'error_saving_favorite' => 'Fout bij het opslaan van favoriet',
        'favorite_updated' => 'Favoriet bijgewerkt',
        'favorite_duplicate' => 'Deze begunstigde is al opgeslagen in uw favorieten',
        'favorite_saved' => 'Opgeslagen in favorieten',
        'share_text' => 'Scan met uw bankapp!',
        'generating' => 'Genereren...',
        'failed_to_generate_qr' => 'QR-code genereren mislukt. Probeer het opnieuw.',
        'invalid_format' => 'Ongeldig formaat',
        'confirm_delete_favorite' => 'Weet u zeker dat u deze favoriet wilt verwijderen?',
        'error_deleting_favorite' => 'Fout bij het verwijderen van favoriet',
        'error' => 'Fout'
    ],
    'de' => [
        'error_required_fields' => 'Bitte füllen Sie alle Pflichtfelder korrekt aus',
        'error_saving_favorite' => 'Fehler beim Speichern des Favoriten',
        'favorite_updated' => 'Favorit aktualisiert',
        'favorite_duplicate' => 'Dieser Begünstigte ist bereits in Ihren Favoriten gespeichert',
        'favorite_saved' => 'In Favoriten gespeichert',
        'share_text' => 'Scannen Sie mit Ihrer Banking-App!',
        'generating' => 'Wird generiert...',
        'failed_to_generate_qr' => 'QR-Code konnte nicht generiert werden. Bitte versuchen Sie es erneut.',
        'invalid_format' => 'Ungültiges Format',
        'confirm_delete_favorite' => 'Sind Sie sicher, dass Sie diesen Favoriten löschen möchten?',
        'error_deleting_favorite' => 'Fehler beim Löschen des Favoriten',
        'error' => 'Fehler'
    ]
];

// Default translations (in English)
$default = [
    'error_required_fields' => 'Please fill all required fields correctly',
    'error_saving_favorite' => 'Error saving to favorites',
    'favorite_updated' => 'Favorite updated',
    'favorite_duplicate' => 'This beneficiary is already saved in your favorites',
    'favorite_saved' => 'Saved to favorites',
    'share_text' => 'Scan with your banking app!',
    'generating' => 'Generating...',
    'failed_to_generate_qr' => 'Failed to generate QR code. Please try again.',
    'invalid_format' => 'Invalid format',
    'confirm_delete_favorite' => 'Are you sure you want to delete this favorite?',
    'error_deleting_favorite' => 'Error deleting favorite',
    'error' => 'Error'
];

// Update all language files
$files = glob(__DIR__ . '/translations/*.php');
foreach ($files as $file) {
    $lang = basename($file, '.php');
    
    // Skip English as it's already updated
    if ($lang === 'en') continue;
    
    // Read existing translations
    $current = require($file);
    
    // Add new translations
    $new = isset($translations[$lang]) ? $translations[$lang] : $default;
    $merged = array_merge($current, $new);
    
    // Write back to file
    file_put_contents($file, "<?php\nreturn " . var_export($merged, true) . ";\n");
}

echo "All translation files have been updated.\n";
