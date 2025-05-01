<?php
require_once __DIR__ . '/../controllers/LanguageController.php';
require_once __DIR__ . '/../controllers/WordCloudController.php';
require_once __DIR__ . '/../core/AppRegistry.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/header.php';

// Get word cloud data
$wordCloudData = WordCloudController::getWordCloudDataJson();

// Get app instances
$apps = AppRegistry::getInstance()->getAppInterfaces();
?>
<main class="container">
    <article class="content-box" style="text-align: center; margin-bottom: 2rem;">
        <h2 style="margin-top: 0;"><?php echo $lang->translate('welcome_title'); ?></h2>
        <p style="font-size:1.15em;margin:0 auto 0.5em auto;">
            <?php echo $lang->translate('welcome_text'); ?>
        </p>
    </article>
    <div id="word-cloud-container" data-words='<?php echo $wordCloudData; ?>' class="word-cloud-wrapper"></div>

    <div class="grid">
        <?php foreach ($apps as $app): ?>
        <a href="/<?php echo $lang->getCurrentLanguage(); ?>/<?php echo htmlspecialchars($app->getSlug()); ?>" class="app-card">
            <?php if ($app->getSlug() === 'drive'): ?>
                <svg class="app-logo" style="margin-bottom:10px;" viewBox="8 14 8 5" width="50" height="50" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <rect x="8" y="16" width="8" height="2"/>
                    <polygon points="9,16 11,14 13,14 15,16"/>
                    <circle cx="10" cy="18" r="1"/>
                    <circle cx="14" cy="18" r="1"/>
                </svg>
            <?php elseif ($app->getSlug() === 'involved'): ?>
                <svg class="app-logo" style="margin-bottom:10px;" viewBox="4 5 13 12" width="50" height="50" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="6" cy="8" r="2"/>
                    <circle cx="10" cy="6" r="1.5"/>
                    <circle cx="14" cy="9" r="2.5"/>
                    <circle cx="8" cy="13" r="1"/>
                    <circle cx="15" cy="15" r="1.8"/>
                </svg>
            <?php elseif ($app->getSlug() === 'paid'): ?>
                <svg class="app-logo" style="margin-bottom:10px;" viewBox="2 2 20 20" width="50" height="50" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="2" width="5" height="5"/>
                    <rect x="17" y="2" width="5" height="5"/>
                    <rect x="2" y="17" width="5" height="5"/>
                    <rect x="9" y="9" width="3" height="3"/>
                    <rect x="14" y="9" width="2" height="2"/>
                    <rect x="9" y="14" width="2" height="2"/>
                    <rect x="14" y="14" width="2" height="2"/>
                </svg>
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($app->getDisplayName()); ?></h3>
            <p><?php echo htmlspecialchars($app->getDescription()); ?></p>
        </a>
        <?php endforeach; ?>
    </div>
</main>

<!-- Include WordCloud library -->
<script src="/vendor/timdream/wordcloud2.js"></script>
<script src="/js/wordcloud.js"></script>

<?php require_once __DIR__ . '/footer.php'; ?>
