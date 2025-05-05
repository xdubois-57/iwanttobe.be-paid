<?php
// Greek home page translations for Involved! application
// Last updated: 2025-05-03

return [
    'involved_intro_title' => 'Συμμετέχετε!',
    'involved_intro_text' => 'Το Involved! διευκολύνει ομάδες, μη κερδοσκοπικούς οργανισμούς και κοινοτικές ομάδες να οργανώνουν ζωντανές εκδηλώσεις, να διεξάγουν ενδιαφέρουσες αναδρομικές συζητήσεις και να ξεκινούν διαδραστικές δημοσκοπήσεις—μετατρέποντας κάθε συνάντηση σε μια αξέχαστη εμπειρία.',
    'involved_features' => 'Συνδεθείτε με τοπικές πρωτοβουλίες, παρακολουθήστε τις εθελοντικές σας ώρες και κάντε τη διαφορά.',
    'join_event_title' => 'Συμμετοχή σε Εκδήλωση',
    'join_event_description' => 'Εισαγάγετε έναν 6-ψήφιο κωδικό για να συμμετάσχετε σε μια υπάρχουσα εκδήλωση.',
    'event_code_placeholder' => 'Κωδικός εκδήλωσης (6 χαρακτήρες)',
    'join_event_button' => 'Συμμετοχή σε Εκδήλωση',
    'create_event_title' => 'Δημιουργία Εκδήλωσης',
    'create_event_description' => 'Δημιουργήστε μια νέα εκδήλωση με προαιρετικό κωδικό πρόσβασης για ασφαλή πρόσβαση.',
    'password_placeholder' => 'Κωδικός πρόσβασης (προαιρετικό)',
    'create_event_button' => 'Δημιουργία Νέας Εκδήλωσης',
    
    // Password prompt page
    'protected_event_title' => 'Προστατευμένη Εκδήλωση',
    'password_prompt_description' => 'Αυτή η εκδήλωση απαιτεί κωδικό πρόσβασης. Παρακαλώ εισάγετέ τον παρακάτω για να συνεχίσετε.',
    'password_label' => 'Κωδικός πρόσβασης',
    'password_prompt_placeholder' => 'Εισάγετε κωδικό πρόσβασης εκδήλωσης',
    'continue_button' => 'Συνέχεια',
    'error_heading' => 'Σφάλμα:',
    
    // Add word form
    'back_to_wordcloud' => '← Επιστροφή στο σύννεφο λέξεων',
    'word_added_success' => 'Η λέξη σας προστέθηκε!',
    'please_enter_word' => 'Παρακαλώ εισάγετε μια λέξη.',
    'word_input_placeholder' => 'Πληκτρολογήστε τη λέξη σας εδώ...',
    'add_word_button' => 'Προσθήκη λέξης',
    
    // Event page
    'event_title_prefix' => 'Εκδήλωση',
    'created_at' => 'Δημιουργήθηκε:',
    'word_clouds_title' => 'Σύννεφα Λέξεων',
    'no_word_clouds' => 'Δεν υπάρχουν ακόμα σύννεφα λέξεων.',
    'enter_question_placeholder' => 'Εισάγετε μια ερώτηση',
    'create_word_cloud_button' => 'Δημιουργία Σύννεφου Λέξεων',
    'confirm_delete_wordcloud' => 'Είστε βέβαιοι ότι θέλετε να διαγράψετε αυτό το σύννεφο λέξεων;',
    'delete_failed' => 'Η διαγραφή του σύννεφου λέξεων απέτυχε',
    'an_error_occurred' => 'Παρουσιάστηκε σφάλμα κατά τη διαγραφή του σύννεφου λέξεων',
    
    // Wordcloud page
    'add_your_word' => 'Προσθέστε τη λέξη σας',
    'scan_qr_access' => 'Σαρώστε αυτόν τον κωδικό QR για πρόσβαση στην εκδήλωση',
    'event_password' => 'PIN:',
    'wordcloud_failed_delete' => 'Αποτυχία διαγραφής λέξης.',
    'wordcloud_error_delete' => 'Παρουσιάστηκε σφάλμα κατά τη διαγραφή της λέξης.',
    'scan_qr_to_answer' => 'Σαρώστε το QR code για να απαντήσετε',
    'admin_link_text' => 'Μετάβαση στη διαχείριση εκδήλωσης',
    
    // EventQrBlock component
    'event_code' => 'Κωδικός:',
    'share_button' => 'Κοινοποίηση',
    'share_title' => 'Συμμετέχετε στην εκδήλωσή μου',
    'share_text' => 'Συμμετέχετε στην εκδήλωσή μου με τον κωδικό:',
    'copy_success' => 'Ο σύνδεσμος αντιγράφηκε στο πρόχειρο!',
    'share_error' => 'Δεν ήταν δυνατή η κοινοποίηση. Ο σύνδεσμος αντιγράφηκε στο πρόχειρο αντ\' αυτού.',
    'share_link_prompt' => 'Αντιγράψτε αυτόν τον σύνδεσμο:',
    'qrblock_scan_or_visit' => 'iwantto.be/involved',
    
    // Controller messages
    'event_create_failed' => 'Η δημιουργία εκδήλωσης απέτυχε. Σφάλμα:',
    'event_not_found' => 'Η εκδήλωση δεν βρέθηκε',
    'invalid_password' => 'Μη έγκυρος κωδικός πρόσβασης. Παρακαλώ προσπαθήστε ξανά.',
    'word_cloud_create_failed' => 'Η δημιουργία σύννεφου λέξεων απέτυχε',
    'wordcloud_not_found' => 'Το σύννεφο λέξεων δεν βρέθηκε',
    'missing_word' => 'Απαιτείται λέξη',
    'word_add_failed' => 'Η προσθήκη λέξης απέτυχε',
    'unauthorized_access' => 'Μη εξουσιοδοτημένη προσπάθεια πρόσβασης',
    'word_parameter_is_required' => 'Η παράμετρος λέξης είναι υποχρεωτική',
    'unauthorized' => 'Μη εξουσιοδοτημένο',
    
    // Short description for landing page
    'short_description' => 'Σύννεφα λέξεων, δημοσκοπήσεις και αναδρομές – κάντε τις συναντήσεις σας διαδραστικές και αξέχαστες.',
    'admin_password_label' => 'Κωδικός διαχειριστή:',
];
