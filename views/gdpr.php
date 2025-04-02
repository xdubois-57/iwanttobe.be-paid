<?php
require_once __DIR__ . '/../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
include 'header.php';
?>

<main class="container">
    <article>
        <h1><?php echo $lang->translate('gdpr_title'); ?></h1>
        
        <section>
            <h2><?php echo $lang->translate('gdpr_title'); ?></h2>
            <p><?php echo sprintf($lang->translate('gdpr_last_updated'), date('F j, Y')); ?></p>
            <p><?php echo $lang->translate('gdpr_intro'); ?></p>
        </section>

        <section>
            <h2><?php echo $lang->translate('gdpr_info_collect_title'); ?></h2>
            <p><?php echo $lang->translate('gdpr_info_collect_intro'); ?></p>
            <ul>
                <li><strong><?php echo $lang->translate('gdpr_payment_info_title'); ?>:</strong> <?php echo $lang->translate('gdpr_payment_info_desc'); ?>
                    <ul>
                        <li><?php echo $lang->translate('beneficiary_name'); ?></li>
                        <li><?php echo $lang->translate('beneficiary_iban'); ?></li>
                        <li><?php echo $lang->translate('amount'); ?></li>
                        <li><?php echo $lang->translate('communication'); ?></li>
                    </ul>
                    <p><?php echo $lang->translate('gdpr_payment_storage_note'); ?></p>
                </li>
                <li><strong><?php echo $lang->translate('gdpr_technical_data'); ?>:</strong> <?php echo $lang->translate('gdpr_technical_data_desc'); ?></li>
            </ul>
        </section>

        <section>
            <h2><?php echo $lang->translate('gdpr_info_use_title'); ?></h2>
            <p><?php echo $lang->translate('gdpr_info_use_intro'); ?></p>
            <ul>
                <li><?php echo $lang->translate('gdpr_use_qr_generation'); ?></li>
                <li><?php echo $lang->translate('gdpr_use_local_storage'); ?></li>
                <li><?php echo $lang->translate('gdpr_use_technical'); ?></li>
                <li><?php echo $lang->translate('gdpr_use_improvement'); ?></li>
            </ul>
            <p><?php echo $lang->translate('gdpr_storage_note'); ?></p>
        </section>

        <section>
            <h2><?php echo $lang->translate('gdpr_security_title'); ?></h2>
            <p><?php echo $lang->translate('gdpr_security_intro'); ?></p>
            <ul>
                <li><?php echo $lang->translate('gdpr_security_processing'); ?></li>
                <li><?php echo $lang->translate('gdpr_security_no_storage'); ?></li>
                <li><?php echo $lang->translate('gdpr_security_local_storage'); ?></li>
                <li><?php echo $lang->translate('gdpr_security_encryption'); ?></li>
                <li><?php echo $lang->translate('gdpr_security_standard'); ?></li>
                <li><?php echo $lang->translate('gdpr_security_clear_data'); ?></li>
            </ul>
        </section>

        <section>
            <h2><?php echo $lang->translate('gdpr_cookies_title'); ?></h2>
            <p><?php echo $lang->translate('gdpr_cookies_intro'); ?></p>
            <ul>
                <li><strong><?php echo $lang->translate('gdpr_cookies_section'); ?></strong>
                    <ul>
                        <li><strong>cookie_consent:</strong> <?php echo $lang->translate('gdpr_cookie_consent_desc'); ?></li>
                        <li><strong>language:</strong> <?php echo $lang->translate('gdpr_cookie_language_desc'); ?></li>
                    </ul>
                </li>
                <li><strong><?php echo $lang->translate('gdpr_local_storage_section'); ?></strong>
                    <ul>
                        <li><strong>payment_details:</strong> <?php echo $lang->translate('gdpr_local_storage_payment'); ?></li>
                    </ul>
                </li>
            </ul>
            <p><?php echo $lang->translate('gdpr_cookies_note'); ?></p>
        </section>

        <section>
            <h2><?php echo $lang->translate('gdpr_rights_title'); ?></h2>
            <p><?php echo $lang->translate('gdpr_rights_intro'); ?></p>
            <ul>
                <li><?php echo $lang->translate('gdpr_right_access'); ?></li>
                <li><?php echo $lang->translate('gdpr_right_rectification'); ?></li>
                <li><?php echo $lang->translate('gdpr_right_erasure'); ?></li>
                <li><?php echo $lang->translate('gdpr_right_restrict'); ?></li>
                <li><?php echo $lang->translate('gdpr_right_portability'); ?></li>
                <li><?php echo $lang->translate('gdpr_right_object'); ?></li>
            </ul>
            <p><?php echo $lang->translate('gdpr_rights_note'); ?></p>
        </section>

        <section>
            <h2><?php echo $lang->translate('gdpr_third_party_title'); ?></h2>
            <p><?php echo $lang->translate('gdpr_third_party_desc'); ?></p>
        </section>

        <section>
            <h2><?php echo $lang->translate('gdpr_updates_title'); ?></h2>
            <p><?php echo $lang->translate('gdpr_updates_desc'); ?></p>
        </section>

        <section>
            <h2><?php echo $lang->translate('gdpr_contact_title'); ?></h2>
            <p><?php echo $lang->translate('gdpr_contact_intro'); ?></p>
            <ul>
                <li><a href="https://github.com/xdubois-57/qrtransfer"><?php echo $lang->translate('gdpr_contact_github'); ?></a></li>
            </ul>
        </section>
    </article>
</main>

<?php include 'footer.php'; ?>
