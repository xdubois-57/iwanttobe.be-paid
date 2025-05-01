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
    <div id="word-cloud-container" data-words='<?php echo $wordCloudData; ?>' class="word-cloud-wrapper"></div>

    <div class="grid">
        <?php foreach ($apps as $app): ?>
        <a href="/<?php echo $lang->getCurrentLanguage(); ?>/<?php echo htmlspecialchars($app->getSlug()); ?>" class="app-card">
            <img src="/assets/images/<?php echo htmlspecialchars($app->getSlug()); ?>-logo.png" alt="<?php echo htmlspecialchars($app->getDisplayName()); ?> logo">
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
