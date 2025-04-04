<?php

$translations = [
    'fr' => [
        'menu_why_us' => 'Pourquoi nous ?',
        'why_us_title' => 'Pourquoi choisir QR Transfer ?',
        'why_us_secure_title' => 'Sécurisé et privé',
        'why_us_secure_text' => 'Vos données sont traitées localement dans votre navigateur. Nous ne stockons jamais d\'informations de paiement sensibles sur nos serveurs.',
        'why_us_easy_title' => 'Facile à utiliser',
        'why_us_easy_text' => 'Générez des codes QR pour vos paiements en quelques secondes. Pas d\'inscription requise, pas de configuration compliquée.',
        'why_us_free_title' => 'Totalement gratuit',
        'why_us_free_text' => 'Notre service est gratuit, sans coûts cachés ni fonctionnalités premium.',
        'why_us_features_title' => 'Fonctionnalités clés',
        'why_us_feature_1' => 'Génération instantanée de codes QR pour les paiements SEPA',
        'why_us_feature_2' => 'Compatible avec toutes les banques européennes',
        'why_us_feature_3' => 'Disponible en plusieurs langues',
        'why_us_feature_4' => 'Fonctionne sur tous les appareils - ordinateur, tablette et mobile'
    ],
    'nl' => [
        'menu_why_us' => 'Waarom wij?',
        'why_us_title' => 'Waarom QR Transfer kiezen?',
        'why_us_secure_title' => 'Veilig en privé',
        'why_us_secure_text' => 'Uw gegevens worden lokaal in uw browser verwerkt. We slaan nooit gevoelige betalingsinformatie op onze servers op.',
        'why_us_easy_title' => 'Gebruiksvriendelijk',
        'why_us_easy_text' => 'Genereer QR-codes voor betalingen in seconden. Geen registratie vereist, geen ingewikkelde setup.',
        'why_us_free_title' => 'Volledig gratis',
        'why_us_free_text' => 'Onze service is gratis te gebruiken, zonder verborgen kosten of premium functies.',
        'why_us_features_title' => 'Belangrijkste functies',
        'why_us_feature_1' => 'Directe QR-code generatie voor SEPA-betalingen',
        'why_us_feature_2' => 'Ondersteuning voor alle Europese banken',
        'why_us_feature_3' => 'Beschikbaar in meerdere talen',
        'why_us_feature_4' => 'Werkt op alle apparaten - desktop, tablet en mobiel'
    ],
    'de' => [
        'menu_why_us' => 'Warum wir?',
        'why_us_title' => 'Warum QR Transfer wählen?',
        'why_us_secure_title' => 'Sicher und privat',
        'why_us_secure_text' => 'Ihre Daten werden lokal in Ihrem Browser verarbeitet. Wir speichern niemals sensible Zahlungsinformationen auf unseren Servern.',
        'why_us_easy_title' => 'Einfach zu bedienen',
        'why_us_easy_text' => 'Generieren Sie QR-Codes für Zahlungen in Sekunden. Keine Registrierung erforderlich, keine komplizierte Einrichtung.',
        'why_us_free_title' => 'Komplett kostenlos',
        'why_us_free_text' => 'Unser Service ist kostenlos nutzbar, ohne versteckte Kosten oder Premium-Funktionen.',
        'why_us_features_title' => 'Hauptfunktionen',
        'why_us_feature_1' => 'Sofortige QR-Code-Generierung für SEPA-Zahlungen',
        'why_us_feature_2' => 'Unterstützung für alle europäischen Banken',
        'why_us_feature_3' => 'In mehreren Sprachen verfügbar',
        'why_us_feature_4' => 'Funktioniert auf allen Geräten - Desktop, Tablet und Mobil'
    ]
];

// Default translations for other languages (in English)
$default = [
    'menu_why_us' => 'Why Us?',
    'why_us_title' => 'Why Choose QR Transfer?',
    'why_us_secure_title' => 'Secure & Private',
    'why_us_secure_text' => 'Your data is processed locally in your browser. We never store sensitive payment information on our servers.',
    'why_us_easy_title' => 'Easy to Use',
    'why_us_easy_text' => 'Generate QR codes for payments in seconds. No registration required, no complicated setup.',
    'why_us_free_title' => 'Completely Free',
    'why_us_free_text' => 'Our service is free to use, with no hidden costs or premium features.',
    'why_us_features_title' => 'Key Features',
    'why_us_feature_1' => 'Instant QR code generation for SEPA payments',
    'why_us_feature_2' => 'Support for all European banks',
    'why_us_feature_3' => 'Available in multiple languages',
    'why_us_feature_4' => 'Works on all devices - desktop, tablet, and mobile'
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
