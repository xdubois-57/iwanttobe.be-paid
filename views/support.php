<?php
/**
 * QR Transfer
 * Copyright (C) 2025 Xavier Dubois
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
?>

<?php
require_once __DIR__ . '/../controllers/LanguageController.php';
require_once __DIR__ . '/../apps/paid/controllers/QRController.php';
require_once __DIR__ . '/../views/header.php';
$lang = LanguageController::getInstance();
$qrController = new QRController();
?>

<main class="container">
    <h1><?php echo $lang->translate('support_title'); ?></h1>
    
    <div class="grid">
        <article class="content-box">
            <h3><?php echo $lang->translate('support_hosting_title'); ?></h3>
            <p><?php echo $lang->translate('support_hosting_text'); ?></p>
        </article>
        
        <article class="content-box">
            <h3><?php echo $lang->translate('support_development_title'); ?></h3>
            <p><?php echo $lang->translate('support_development_text'); ?></p>
        </article>
        
        <article class="content-box">
            <h3><?php echo $lang->translate('support_future_title'); ?></h3>
            <p><?php echo $lang->translate('support_future_text'); ?></p>
        </article>
    </div>

    <section class="coffee-qr">
        <h2><?php echo $lang->translate('support_coffee_title'); ?></h2>
        <div class="qr-container">
            <div id="support-qr" class="text-center">
                <p class="support-message"><?php echo $lang->translate('support_text'); ?></p>
                <div class="qr-wrapper">
                    <img id="coffee-qr-image" src="" alt="<?php echo $lang->translate('support_qr_alt'); ?>" class="support-qr-image">
                </div>
                <p class="thank-you-message"><?php echo $lang->translate('support_thanks'); ?></p>
            </div>
        </div>
    </section>
</main>

<!-- Structured data for search engines -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "DonateAction",
  "name": "<?php echo $lang->translate('support_coffee_title'); ?>",
  "description": "<?php echo $lang->translate('support_text'); ?>",
  "agent": {
    "@type": "Organization",
    "name": "QR Transfer",
    "url": "https://qrtransfer.eu"
  },
  "potentialAction": {
    "@type": "PayAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "https://qrtransfer.eu/support"
    },
    "instrument": {
      "@type": "MonetaryAmount",
      "currency": "EUR",
      "value": "5.00"
    }
  }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create form data for QR code generation
    const formData = new FormData();
    formData.append('beneficiary_name', 'iwantto.be');
    formData.append('beneficiary_iban', 'LT103250087680814808');
    formData.append('amount', '5.00');
    formData.append('communication', 'Get a coffee');

    // Store error message for later use
    const errorMessage = "<?php echo $lang->translate('support_qr_error'); ?>";

    // Make AJAX request to generate QR code
    fetch('/<?php echo $lang->getCurrentLanguage(); ?>/paid/api/generate-qr', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            document.getElementById('coffee-qr-image').src = data.image;
        } else {
            throw new Error(data.message || 'Failed to generate QR code');
        }
    })
    .catch(error => {
        console.error('Error generating QR code:', error);
        document.getElementById('support-qr').innerHTML = '<p class="error">' + errorMessage + '</p>';
    });
});
</script>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
