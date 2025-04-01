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
                               placeholder="<?php echo $lang->translate('amount'); ?>"
                               step="0.01" 
                               min="0.01" 
                               required>
                        <span class="validation-indicator"></span>
                    </div>

                    <div class="input-container">
                        <input type="text" 
                               id="communication" 
                               name="communication" 
                               placeholder="<?php echo $lang->translate('communication'); ?>"
                               maxlength="100">
                        <span class="validation-indicator"></span>
                    </div>

                    <button type="submit" class="primary"><?php echo $lang->translate('generate_qr'); ?></button>
                </form>
            </article>
        </div>

        <!-- Right column with QR code -->
        <div>
            <article class="qr-container">
                <div id="qr-placeholder">
                    <img src="https://thumb.ac-illust.com/7b/7b60a6661240685f492f5e8cce934f27_w.jpeg" 
                         alt="QR Code Transfer Illustration">
                </div>
                <div id="qr-code" style="display: none;">
                    <img id="qr-image" src="" alt="Generated QR Code">
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
