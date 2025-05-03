<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1><?php echo htmlspecialchars($wordCloudData['question']); ?></h1>
        <p style="margin-top: 0.5rem;">
            <a href="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/<?php echo $wordCloudData['id']; ?>">
                <?php echo htmlspecialchars($lang->translate('back_to_wordcloud')); ?>
            </a>
        </p>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'true'): ?>
        <div style="background:#d4edda; color:#155724; padding:1rem; margin:1rem 0; border-radius:0.3rem;">
            <?php echo htmlspecialchars($lang->translate('word_added_success')); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'missing_word'): ?>
        <div style="background:#f8d7da; color:#721c24; padding:1rem; margin:1rem 0; border-radius:0.3rem;">
            <?php echo htmlspecialchars($lang->translate('please_enter_word')); ?>
        </div>
        <?php endif; ?>
        
        <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/<?php echo $wordCloudData['id']; ?>/add">
            <input 
                type="text" 
                id="word" 
                name="word" 
                placeholder="<?php echo htmlspecialchars($lang->translate('word_input_placeholder')); ?>"
                maxlength="30"
                required
                style="width:100%; font-size: 1.2rem; margin: 1rem 0;"
                autofocus
            >
            <button type="submit" style="width:100%;"><?php echo htmlspecialchars($lang->translate('add_word_button')); ?></button>
        </form>
    </article>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
