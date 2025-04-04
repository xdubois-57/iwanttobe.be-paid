<?php
require_once __DIR__ . '/header.php';
$lang = LanguageController::getInstance();
?>

<div class="container">
    <h1><?php echo $lang->translate('why_us_title'); ?></h1>
    
    <section class="grid">
        <div>
            <article>
                <h3><?php echo $lang->translate('why_us_secure_title'); ?></h3>
                <p><?php echo $lang->translate('why_us_secure_text'); ?></p>
            </article>
        </div>
        <div>
            <article>
                <h3><?php echo $lang->translate('why_us_easy_title'); ?></h3>
                <p><?php echo $lang->translate('why_us_easy_text'); ?></p>
            </article>
        </div>
        <div>
            <article>
                <h3><?php echo $lang->translate('why_us_free_title'); ?></h3>
                <p><?php echo $lang->translate('why_us_free_text'); ?></p>
            </article>
        </div>
    </section>

    <section class="features">
        <h2><?php echo $lang->translate('why_us_features_title'); ?></h2>
        <ul>
            <li><?php echo $lang->translate('why_us_feature_1'); ?></li>
            <li><?php echo $lang->translate('why_us_feature_2'); ?></li>
            <li><?php echo $lang->translate('why_us_feature_3'); ?></li>
            <li><?php echo $lang->translate('why_us_feature_4'); ?></li>
        </ul>
    </section>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
