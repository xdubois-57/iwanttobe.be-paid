<?php
// Global English GDPR translations
// Contains all privacy policy and GDPR-related text for all iwantto.be applications
// Last updated: 2025-05-03

return [
    // Page title and metadata
    'gdpr_title' => 'Privacy Policy & GDPR',
    'gdpr_last_updated' => 'Last updated: %s',
    
    // Introduction section
    'gdpr_intro' => 'This Privacy Policy describes how <strong>iwantto.be</strong> ("we", "us", or "our") collects, uses, and protects your information when you use our suite of web applications including Paid!, Involved!, and Driven!.',
    
    // Information collection section
    'gdpr_info_collect_title' => 'Information We Collect',
    'gdpr_info_collect_intro' => 'We collect and process the minimum amount of information necessary to provide our services:',
    
    // Application-specific information collection
    'gdpr_apps_title' => 'Application-Specific Data Processing',
    'gdpr_apps_intro' => 'Each of our applications processes different types of information:',
    
    'gdpr_paid_title' => 'Paid! Application',
    'gdpr_paid_desc' => 'Our payment QR code generator processes payment information to create standardized QR codes:',
    'gdpr_paid_data' => 'Payment information (beneficiary name, IBAN, amount, communication) is processed in your browser to generate payment QR codes but is never stored on our servers.',
    
    'gdpr_involved_title' => 'Involved! Application',
    'gdpr_involved_desc' => 'Our interactive event application collects only the information required to power features such as polls, word clouds, retrospectives, and similar collaborative tools:',
    'gdpr_involved_words' => 'Words and phrases submitted to word clouds',
    'gdpr_involved_polls' => 'Poll questions and responses',
    'gdpr_involved_retros' => 'Feedback items submitted to retrospectives',
    'gdpr_involved_retention' => 'Event-related information is stored on our servers and is automatically deleted after one month of inactivity to comply with GDPR.',
    'gdpr_involved_anonymous' => 'No personally identifiable information is collected, and no login is required to use the service.',
    
    'gdpr_driven_title' => 'Driven! Application',
    'gdpr_driven_desc' => 'Our driving application processes:',
    'gdpr_driven_data' => 'Minimal anonymous data required for the service functionality.',
    
    // Payment form field labels (for GDPR description list)
    'beneficiary_name' => 'Beneficiary Name',
    'beneficiary_iban' => 'IBAN',
    'amount' => 'Amount',
    'communication' => 'Communication',
    
    // Payment information section
    'gdpr_payment_info_title' => 'Payment Information',
    'gdpr_payment_info_desc' => 'When you generate a payment QR code with Paid!, we process the following information:',
    'gdpr_payment_storage_note' => 'This data is processed in your browser to generate the QR code and is never stored on our servers.',
    
    // Technical data section
    'gdpr_technical_data' => 'Technical Data',
    'gdpr_technical_data_desc' => 'We use essential cookies and local storage to ensure the proper functioning of our websites.',
    
    // Information usage section
    'gdpr_info_use_title' => 'How We Use Your Information',
    'gdpr_info_use_intro' => 'The information you provide is used solely for:',
    'gdpr_use_qr_generation' => 'Processing payment information to generate QR codes (Paid!)',
    'gdpr_use_events' => 'Managing event participation and word clouds (Involved!)',
    'gdpr_use_driven' => 'Providing driving-related functionalities (Driven!)',
    'gdpr_use_local_storage' => 'Storing your preferences locally in your browser (when you choose this option)',
    'gdpr_use_language' => 'Remembering your language preference',
    'gdpr_use_theme' => 'Remembering your theme preference (light/dark mode)',
    'gdpr_use_technical' => 'Ensuring the technical functionality of our services',
    'gdpr_use_improvement' => 'Improving our services based on anonymous usage patterns',
    'gdpr_storage_note' => 'For the Involved! application, event data is stored on our servers for a maximum of one month, after which it is automatically deleted for GDPR compliance.',
    
    // Information sharing section
    'gdpr_info_sharing_title' => 'Information Sharing and Disclosure',
    'gdpr_info_sharing_intro' => 'We do not sell, trade, or otherwise transfer your information to outside parties. This does not include trusted third parties who assist us in operating our website, conducting our business, or servicing you, so long as those parties agree to keep this information confidential.',
    
    // Third-party services (generic statement)
    'gdpr_third_party_title' => 'Third-Party Services',
    'gdpr_third_party_desc' => 'We do not use external analytics or advertising services. The only third-party integration is the OpenIBAN service, which is used solely to validate the IBAN you enter when generating a payment QR code. Only the IBAN is transmitted, and no other personal data is shared.',
    
    // Data security section
    'gdpr_security_title' => 'Data Security',
    'gdpr_security_intro' => 'We implement a variety of security measures to maintain the safety of your information:',
    'gdpr_security_processing' => 'Information is processed securely on our servers',
    'gdpr_security_no_pii' => 'We do not collect or store personally identifiable information',
    'gdpr_security_no_storage' => 'Payment information is never stored on our servers',
    'gdpr_security_event_retention' => 'Event data is automatically deleted after one month of inactivity',
    'gdpr_security_local_storage' => 'Your saved preferences are stored only in your browser\'s local storage if you choose to save them',
    'gdpr_security_encryption' => 'We use HTTPS encryption to protect data transfer',
    'gdpr_security_clear_data' => 'You can clear your locally stored data at any time through your browser settings',
    'gdpr_security_standard' => 'We adhere to industry-standard security practices to safeguard your information',
    
    // Your rights section
    'gdpr_rights_title' => 'Your Rights',
    'gdpr_rights_intro' => 'Under the General Data Protection Regulation (GDPR), you have certain rights regarding your personal data:',
    'gdpr_right_access' => 'The right to access your data',
    'gdpr_right_rectification' => 'The right to rectification',
    'gdpr_right_erasure' => 'The right to erasure',
    'gdpr_right_restrict' => 'The right to restrict processing',
    'gdpr_right_restriction' => 'The right to restrict processing',
    'gdpr_right_portability' => 'The right to data portability',
    'gdpr_right_object' => 'The right to object',
    'gdpr_rights_note' => 'Since we process minimal data and do not store personally identifiable information, many of these rights are automatically fulfilled. For Involved! application data, you can request immediate deletion of any event data before the automatic one-month deletion period.',
    
    // Cookies and local storage section
    'gdpr_cookies_title' => 'Cookies and Local Storage',
    'gdpr_cookies_intro' => 'We use the following cookies and local storage items:',
    'gdpr_cookies_section' => 'Cookies:',
    'gdpr_cookie_consent_desc' => 'Remembers your cookie preferences',
    'gdpr_cookie_language_desc' => 'Stores your preferred language setting',
    'gdpr_cookie_session' => 'Maintains your session state while using our applications',
    
    'gdpr_local_storage_section' => 'Local Storage:',
    'gdpr_local_storage_theme' => 'Stores your preferred theme (light/dark mode)',
    'gdpr_local_storage_payment' => 'Stores your saved payment details (Paid! app, only if you choose to save them)',
    'gdpr_local_storage_preferences' => 'Stores your application preferences',
    'gdpr_cookies_note' => 'These cookies and local storage data do not track you and do not share information with third parties. You can clear this data at any time through your browser settings.',
    
    // Policy updates section
    'gdpr_updates_title' => 'Changes to This Privacy Policy',
    'gdpr_updates_desc' => 'We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date.',
    
    // Contact information section
    'gdpr_contact_title' => 'Contact Us',
    'gdpr_contact_intro' => 'If you have any questions about this Privacy Policy or our practices, please contact us:',
    'gdpr_contact_email' => 'privacy@iwantto.be',
    'gdpr_contact_github' => 'By creating an issue on our GitHub repository'
];
