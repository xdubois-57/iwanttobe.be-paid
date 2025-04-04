<?php
require_once __DIR__ . '/header.php';
$lang = LanguageController::getInstance();
?>

<main class="container">
    <h1><?php echo $lang->translate('why_us_title'); ?></h1>

    <div class="grid">
        <article>
            <header>
                <h3><?php echo $lang->translate('why_us_secure_title'); ?></h3>
            </header>
            <p><?php echo $lang->translate('why_us_secure_text'); ?></p>
        </article>
        <article>
            <header>
                <h3><?php echo $lang->translate('why_us_easy_title'); ?></h3>
            </header>
            <p><?php echo $lang->translate('why_us_easy_text'); ?></p>
        </article>
        <article>
            <header>
                <h3><?php echo $lang->translate('why_us_free_title'); ?></h3>
            </header>
            <p><?php echo $lang->translate('why_us_free_text'); ?></p>
        </article>
    </div>

    <article>
        <header>
            <h2><?php echo $lang->translate('why_us_features_title'); ?></h2>
        </header>
        <ul>
            <li><?php echo $lang->translate('why_us_feature_1'); ?></li>
            <li><?php echo $lang->translate('why_us_feature_2'); ?></li>
            <li><?php echo $lang->translate('why_us_feature_3'); ?></li>
            <li><?php echo $lang->translate('why_us_feature_4'); ?></li>
        </ul>
    </article>

    <section>
        <h2><?php echo $lang->translate('why_us_use_cases_title'); ?></h2>
        <div class="grid">
            <article>
                <header>
                    <h3><?php echo $lang->translate('why_us_use_case_1_title'); ?></h3>
                </header>
                <p><?php echo $lang->translate('why_us_use_case_1_text'); ?></p>
            </article>
            <article>
                <header>
                    <h3><?php echo $lang->translate('why_us_use_case_2_title'); ?></h3>
                </header>
                <p><?php echo $lang->translate('why_us_use_case_2_text'); ?></p>
            </article>
            <article>
                <header>
                    <h3><?php echo $lang->translate('why_us_use_case_3_title'); ?></h3>
                </header>
                <p><?php echo $lang->translate('why_us_use_case_3_text'); ?></p>
            </article>
            <article>
                <header>
                    <h3><?php echo $lang->translate('why_us_use_case_4_title'); ?></h3>
                </header>
                <p><?php echo $lang->translate('why_us_use_case_4_text'); ?></p>
            </article>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
