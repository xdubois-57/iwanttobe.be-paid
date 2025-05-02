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
// Include the header first, so AppRegistry has the current app set
require_once __DIR__ . '/../controllers/QRController.php';
$qrController = new QRController();
require_once __DIR__ . '/../../../views/header.php';
// The header will have already initialized LanguageController with the proper app context
$lang = LanguageController::getInstance();
?>

<main class="container">
    <article class="content-box">
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
                               required>
                    </div>

                    <div class="input-container">
                        <input type="text" 
                               id="beneficiary_iban" 
                               name="beneficiary_iban" 
                               placeholder="<?php echo $lang->translate('beneficiary_iban'); ?>"
                               autocomplete="off"
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
                        <div class="secondary-button-row flex gap-1">
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
                <a class="support-message clickable" href="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/support">☕ <?php echo $lang->translate('support_text_alt'); ?></a>
                     
                </div>

                <!-- User generated QR -->
                <div id="user-qr" class="text-center" style="display: none;">
                    <a class="support-message clickable" href="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/support">☕ <?php echo $lang->translate('support_text_alt'); ?></a>
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

<script src="/js/form-validation.js"></script>

<script>
function resetRightPanel() {
    document.getElementById('user-qr').style.display = 'none';
    document.getElementById('support-qr').style.display = 'block';
}
</script>

<?php 
require_once __DIR__ . '/../../../views/footer.php';
?>
