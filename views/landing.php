<?php
require_once __DIR__ . '/../controllers/LanguageController.php';
require_once __DIR__ . '/../controllers/WordCloudController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/header.php';

// Get word cloud data
$wordCloudData = WordCloudController::getWordCloudDataJson();
?>
<main class="container">
    <h1><?php echo $lang->translate('landing_choose_app') ?? 'Choose an application'; ?></h1>
    <ul class="app-list">
        <li><a class="button" href="/<?php echo $lang->getCurrentLanguage(); ?>/paid">Paid!</a></li>
        <li><a class="button" href="/<?php echo $lang->getCurrentLanguage(); ?>/involved">Involved!</a></li>
        <li><a class="button" href="/<?php echo $lang->getCurrentLanguage(); ?>/drive">Drive</a></li>
    </ul>
    
    <div id="word-cloud-container" data-words='<?php echo $wordCloudData; ?>' class="word-cloud-wrapper"></div>
</main>

<!-- Include WordCloud library -->
<script src="/vendor/timdream/wordcloud2.js"></script>
<script src="/js/wordcloud.js"></script>

<?php require_once __DIR__ . '/footer.php'; ?>
