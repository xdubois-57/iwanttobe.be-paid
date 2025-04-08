<?php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../controllers/QRController.php';
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create form data for QR code generation
    const formData = new FormData();
    formData.append('beneficiary_name', 'QR Transfer Team');
    formData.append('beneficiary_iban', 'BE42377116042854');
    formData.append('amount', '5.00');
    formData.append('communication', '<?php echo $lang->translate('support_coffee_message'); ?>');

    // Make AJAX request to generate QR code
    fetch('/generate-qr', {
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
        document.getElementById('support-qr').innerHTML = '<p class="error"><?php echo $lang->translate('support_qr_error'); ?></p>';
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
