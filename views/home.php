<?php
require_once __DIR__ . '/../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/header.php';
?>

<main class="container">
    <div class="grid">
        <!-- Left column with form -->
        <div>
            <article class="form-container">
                <form id="transfer-form">
                    <div class="input-container">
                        <input type="text" 
                               id="beneficiary_name" 
                               name="beneficiary_name" 
                               placeholder="<?php echo $lang->translate('beneficiary_name'); ?>"
                               required>
                        <span class="validation-indicator"></span>
                    </div>

                    <div class="input-container">
                        <input type="text" 
                               id="beneficiary_iban" 
                               name="beneficiary_iban" 
                               placeholder="<?php echo $lang->translate('beneficiary_iban'); ?>"
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
                               required>
                        <span class="validation-indicator"></span>
                    </div>

                    <div class="input-container">
                        <input type="text" 
                               id="communication" 
                               name="communication" 
                               placeholder="<?php echo $lang->translate('communication'); ?>">
                        <span class="validation-indicator"></span>
                    </div>

                    <button type="submit"><?php echo $lang->translate('generate_qr'); ?></button>
                </form>
            </article>
        </div>

        <!-- Right column with QR code -->
        <div>
            <!-- QR code display area -->
            <article class="qr-display">
                <!-- Default support QR -->
                <div id="support-qr">
                    <p><?php echo $lang->translate('support_text'); ?></p>
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
                    <img src="<?php echo $supportQrImage; ?>" alt="Support QR Transfer" class="support-qr">
                    <div>
                        <button type="submit" data-share data-image="<?php echo $supportQrImage; ?>" data-title="QR Transfer Support">
                            <?php echo $lang->translate('share_qr'); ?>
                        </button>
                    </div>
                </div>

                <!-- User generated QR -->
                <div id="user-qr" style="display: none;">
                    <img id="qr-image" src="" alt="Generated QR Code">
                    <div>
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
    .form-container, .qr-container {
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

    .input-container {
        display: grid;
        grid-template-columns: 1fr 24px;
        gap: 0.5rem;
        align-items: center;
    }

    .input-container input {
        margin: 0;
    }

    .qr-container {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #qr-placeholder img, #qr-code img {
        width: 100%;
        height: auto;
        max-height: 400px;
        object-fit: contain;
        border-radius: var(--border-radius);
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

    .text-center {
        text-align: center;
    }

    [data-share] {
        background-color: var(--button-color);
        color: var(--button-text-color);
        border: none;
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        cursor: pointer;
    }

    @media (max-width: 768px) {
        #qr-placeholder img, #qr-code img {
            max-height: 300px;
        }
    }
</style>

<script src="js/form-validation.js"></script>

<?php 
require_once __DIR__ . '/footer.php';
?>
