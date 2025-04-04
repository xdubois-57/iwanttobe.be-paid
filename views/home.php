<?php
require_once __DIR__ . '/../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
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
                <form id="transfer-form" autocomplete="off" onsubmit="return false">
                    <div class="favorites-container">
                        <select id="favorites" onchange="loadFavorite()">
                            <option value=""><?php echo $lang->translate('select_favorite'); ?></option>
                        </select>
                    </div>

                    <div class="input-container">
                        <input type="text" 
                               id="beneficiary_name" 
                               name="beneficiary_name" 
                               placeholder="<?php echo $lang->translate('beneficiary_name'); ?>"
                               autocomplete="off"
                               required>
                        <span class="validation-indicator"></span>
                    </div>

                    <div class="input-container">
                        <input type="text" 
                               id="beneficiary_iban" 
                               name="beneficiary_iban" 
                               placeholder="<?php echo $lang->translate('beneficiary_iban'); ?>"
                               autocomplete="off"
                               required>
                        <span class="validation-indicator"></span>
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
                        <span class="validation-indicator"></span>
                    </div>

                    <div class="input-container">
                        <input type="text" 
                               id="communication" 
                               name="communication" 
                               placeholder="<?php echo $lang->translate('communication'); ?>"
                               autocomplete="off">
                        <span class="validation-indicator"></span>
                    </div>

                    <div class="button-container">
                        <div class="primary-button-row">
                            <button type="button" id="generate-qr-button" onclick="generateQRCode()"><?php echo $lang->translate('generate_qr'); ?></button>
                        </div>
                        <div class="secondary-button-row">
                            <button type="button" onclick="clearForm()" class="secondary outline" id="clear-form">
                                <?php echo $lang->translate('clear_form'); ?>
                            </button>
                            <button type="button" onclick="saveFavorite()" class="secondary outline" id="save-favorite" data-update-text="<?php echo $lang->translate('update_favorite'); ?>">
                                <?php echo $lang->translate('save_favorite'); ?>
                            </button>
                            <button type="button" onclick="deleteFavorite()" class="secondary outline" id="delete-favorite" disabled>
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
                    require_once __DIR__ . '/../controllers/HomeController.php';
                    $controller = new HomeController();
                    
                    // Support QR code data
                    $name = 'QR Transfer';
                    $iban = 'BE42377116042854';
                    $bic = $controller->lookupBIC($iban) ?: '';
                    $amount = 5;
                    $communication = $lang->translate('support_thanks');
                    
                    // Generate EPC QR code
                    $epcData = $controller->generateEPCData($name, $iban, $bic, $amount, $communication);
                    $supportQrImage = $controller->generateQRCode($epcData);
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
                    <p class="support-message clickable" onclick="resetRightPanel()"><?php echo $lang->translate('support_text_alt'); ?></p>
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

<style>
    .form-container {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .form-container form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        height: 100%;
    }

    .favorites-container {
        margin-bottom: 1rem;
    }

    .favorites-container select {
        width: 100%;
        margin: 0;
    }

    .button-container {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
        margin-top: 1rem;
    }

    .primary-button-row {
        display: flex;
        justify-content: center;
        margin-bottom: -0.2rem;
    }

    .primary-button-row button {
        width: 100%;
        max-width: 600px;
    }

    .secondary-button-row {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
    }

    .secondary-button-row button {
        flex: 1;
    }

    .input-container {
        display: grid;
        grid-template-columns: 1fr 24px;
        gap: 0.5rem;
        align-items: center;
    }

    .input-container input {
        margin: 0;
    }

    .qr-display {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        height: 100%;
    }

    #support-qr, #user-qr {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
    }

    .support-message {
        font-size: 1.1em;
        margin-bottom: 1rem;
        max-width: 400px;
        line-height: 1.4;
    }

    .support-message.clickable {
        cursor: pointer;
        color: var(--primary);
        text-decoration: underline;
    }

    .support-message.clickable:hover {
        opacity: 0.8;
    }

    .qr-wrapper {
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
    }

    .support-qr, #qr-image {
        width: 100%;
        height: auto;
        border-radius: var(--border-radius);
    }

    .button-wrapper {
        margin-top: 1rem;
    }

    .text-center {
        text-align: center;
    }

    [data-share] {
        margin: 0 auto;
    }

    /* Validation indicators */
    .validation-indicator {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: none;
    }

    .validation-indicator.valid {
        display: block;
        background-color: var(--form-valid-color, #2ecc71);
    }

    .validation-indicator.invalid {
        display: block;
        background-color: var(--form-invalid-color, #e74c3c);
    }

    .qr-caption {
        margin-top: 1rem;
        color: var(--muted-color);
        font-size: 0.9em;
    }

    @media (max-width: 768px) {
        #qr-placeholder img, #qr-code img {
            max-height: 300px;
        }
        .secondary-button-row {
            flex-direction: column;
        }
        .secondary-button-row button {
            width: 100%;
        }
    }
</style>

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
