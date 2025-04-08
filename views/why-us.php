<?php
require_once __DIR__ . '/header.php';
$lang = LanguageController::getInstance();
?>

<main class="container">
    <h1><?php echo $lang->translate('why_us_title'); ?></h1>

    <div class="grid">
        <article class="content-box">
            <h3><?php echo $lang->translate('why_us_secure_title'); ?></h3>
            <p><?php echo $lang->translate('why_us_secure_text'); ?></p>
        </article>
        
        <article class="content-box">
            <h3><?php echo $lang->translate('why_us_easy_title'); ?></h3>
            <p><?php echo $lang->translate('why_us_easy_text'); ?></p>
        </article>
        
        <article class="content-box">
            <h3><?php echo $lang->translate('why_us_free_title'); ?></h3>
            <p><?php echo $lang->translate('why_us_free_text'); ?></p>
        </article>
    </div>

    <section class="key-features">
        <h2><?php echo $lang->translate('why_us_features_title'); ?></h2>
        <div class="features-container">
            <div class="feature-item">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>Instant QR Generation</h3>
                    <p><?php echo $lang->translate('why_us_feature_1'); ?></p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>European Bank Support</h3>
                    <p><?php echo $lang->translate('why_us_feature_2'); ?></p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M12.87 15.07l-2.54-2.51.03-.03c1.74-1.94 2.98-4.17 3.71-6.53H17V4h-7V2H8v2H1v1.99h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12zm-2.62 7l1.62-4.33L19.12 17h-3.24z"/>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>Multilingual</h3>
                    <p><?php echo $lang->translate('why_us_feature_3'); ?></p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M4 6h18V4H4c-1.1 0-2 .9-2 2v11H0v3h14v-3H4V6zm19 2h-6c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h6c.55 0 1-.45 1-1V9c0-.55-.45-1-1-1zm-1 9h-4v-7h4v7z"/>
                    </svg>
                </div>
                <div class="feature-content">
                    <h3>Cross-Device Compatible</h3>
                    <p><?php echo $lang->translate('why_us_feature_4'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <style>
        .key-features {
            margin: 2rem 0;
        }
        .features-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background-color 0.2s;
        }
        .feature-item:hover {
            background-color: var(--secondary-hover);
        }
        .feature-icon {
            color: var(--primary);
            min-width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .feature-content {
            flex: 1;
        }
        .feature-content h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
        }
        .feature-content p {
            margin: 0;
        }
        
        /* Desktop grid layout */
        @media (min-width: 769px) {
            .features-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem;
            }
        }
    </style>

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
