<?php
require_once __DIR__ . '/../controllers/LanguageController.php';
require_once __DIR__ . '/../controllers/QRController.php';
$lang = LanguageController::getInstance();
$qrController = new QRController();
require_once __DIR__ . '/header.php';
?>

<main class="container">
    <article>
        <h2><?php echo $lang->translate('home_intro_title'); ?></h2>
        <p><?php echo $lang->translate('home_intro_text'); ?></p>
    </article>

    <div class="grid">
        <!-- Left column with form -->
        <div>
            <article class="form-container">
                <form id="transfer-form" autocomplete="off">
                    <div class="favorites-container">
                        <select id="favorites">
                            <option value=""><?php echo $lang->translate('select_favorite'); ?></option>
                        </select>
                    </div>

                    <div class="input-container">
                        <input type="text" 
                               id="beneficiary_name" 
                               name="beneficiary_name" 
                               placeholder="<?php echo $lang->translate('beneficiary_name'); ?>"
                               autocomplete="off"
                               readonly
                               required>
                    </div>

                    <div class="input-container">
                        <input type="text" 
                               id="beneficiary_iban" 
                               name="beneficiary_iban" 
                               placeholder="<?php echo $lang->translate('beneficiary_iban'); ?>"
                               autocomplete="off"
                               readonly
                               required>
                    </div>

                    <div class="input-container">
                        <input type="number" 
                               id="amount" 
                               name="amount" 
                               step="0.01" 
                               min="0.01" 
                               max="999999999.99"
                               placeholder="<?php echo $lang->translate('amount'); ?>"
                               autocomplete="off"
                               required>
                    </div>

                    <div class="input-container">
                        <input type="text" 
                               id="communication" 
                               name="communication" 
                               placeholder="<?php echo $lang->translate('communication'); ?>"
                               autocomplete="off">
                    </div>

                    <div class="button-container">
                        <div class="primary-button-row">
                            <button type="submit" id="generate-qr-button"><?php echo $lang->translate('generate_qr'); ?></button>
                        </div>
                        <div class="secondary-button-row">
                            <button type="button" class="secondary outline" id="clear-form">
                                <?php echo $lang->translate('clear_form'); ?>
                            </button>
                            <button type="button" class="secondary outline" id="save-favorite" data-update-text="<?php echo $lang->translate('update_favorite'); ?>">
                                <?php echo $lang->translate('save_favorite'); ?>
                            </button>
                            <button type="button" class="secondary outline" id="delete-favorite" disabled>
                                <?php echo $lang->translate('delete_favorite'); ?>
                            </button>
                        </div>
                    </div>
                </form>
            </article>
        </div>

        <!-- Right column with QR code -->
        <div>
            <!-- QR code display area -->
            <article class="qr-display">
                <!-- Support QR code -->
                <div id="support-qr" class="text-center">
                    <p class="support-message"><?php echo $lang->translate('support_text'); ?></p>
                    <?php
                    // Support QR code data
                    $bic = $qrController->lookupBIC('BE42377116042854');
                    $epcData = $qrController->generateEPCData('QR Transfer', 'BE42377116042854', $bic, 5, $lang->translate('support_thanks'));
                    $supportQrImage = $qrController->generateQRCode($epcData);
                    ?>
                    <div class="qr-wrapper">
                        <img src="<?php echo $supportQrImage; ?>" alt="Support QR Transfer" class="support-qr">
                    </div>
                    <div class="button-wrapper">
                        <button type="submit" data-share data-image="<?php echo $supportQrImage; ?>" data-title="QR Transfer Support">
                            <?php echo $lang->translate('share_qr'); ?>
                        </button>
                    </div>
                </div>

                <!-- User generated QR -->
                <div id="user-qr" class="text-center" style="display: none;">
                    <p class="support-message clickable"><?php echo $lang->translate('support_text_alt'); ?></p>
                    <div class="qr-wrapper">
                        <img id="qr-image" src="" alt="Generated QR Code">
                    </div>
                    <div class="button-wrapper">
                        <button type="submit" data-share data-title="QR Transfer Payment" id="share-qr">
                            <?php echo $lang->translate('share_qr'); ?>
                        </button>
                    </div>
                </div>
            </article>
        </div>
    </div>
</main>

<script src="js/form-validation.js"></script>

<script>
function resetRightPanel() {
    document.getElementById('user-qr').style.display = 'none';
    document.getElementById('support-qr').style.display = 'block';
}
</script>

<?php 
require_once __DIR__ . '/footer.php';
?>
