<?php
// Latvian error messages for QR Transfer application
// Contains all validation and error messages
// Last updated: 2025-04-13

return [
    // Form validation errors
    'error_required_fields' => 'Lūdzu, pareizi aizpildiet visus obligātos laukus',
    'error_min_length' => 'Lauks ir pārāk īss',
    'error_max_length' => 'Lauks ir pārāk gars',
    'error_invalid_iban' => 'IBAN ir nederīgs',
    'error_invalid_amount' => 'Summa ir nederīga',
    'error_invalid_communication' => 'Komunikācija ir nederīga',
    'error_favorite_exists' => 'Favorīts ar šādu nosaukumu jau eksistē',
    'error_favorite_not_found' => 'Favorīts nav atrasts',
    'error_saving_favorite' => 'Kļūda, saglabājot favorītā',
    'favorite_duplicate' => 'Šis saņēmējs jau ir saglabāts jūsu favorītos',
    
    // Technical errors
    'error_server' => 'Servera kļūda',
    'error_network' => 'Tīkla kļūda',
    'error_storage' => 'Krātuves kļūda',
    'error_invalid_data' => 'Nederīgi dati',
    'error_processing' => 'Apstrādes kļūda',
    'error' => 'Kļūda',
    
    // Success messages
    'success_favorite_saved' => 'Favorīts veiksmīgi saglabāts',
    'success_favorite_updated' => 'Favorīts veiksmīgi atjaunināts',
    'success_favorite_deleted' => 'Favorīts veiksmīgi dzēsts',
    'success_qr_generated' => 'QR kods veiksmīgi izveidots',
    'favorite_updated' => 'Favorīts atjaunināts',
    'favorite_saved' => 'Saglabāts favorītos',
    'share_text' => 'Lasiet ar jūsu bankas lietotni!',
    'generating' => 'Izveido...',
    'failed_to_generate_qr' => 'Neizdevās izveidot QR kodu. Lūdzu, mēģiniet vēlreiz.',
    'invalid_format' => 'Nederīgs formāts',
    'confirm_delete_favorite' => 'Vai tiešām vēlaties dzēst šo favorītu?',
    'error_deleting_favorite' => 'Kļūda, dzēšot favorītu',
];
