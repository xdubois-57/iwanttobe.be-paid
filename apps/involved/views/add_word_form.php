<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1><?php echo htmlspecialchars($wordCloudData['question']); ?></h1>
        <p style="margin-top: 0.5rem;">
            <a href="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/<?php echo $wordCloudData['id']; ?>">
                &larr; Back to word cloud
            </a>
        </p>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'true'): ?>
        <div style="background:#d4edda; color:#155724; padding:1rem; margin:1rem 0; border-radius:0.3rem;">
            Your word has been added successfully!
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'missing_word'): ?>
        <div style="background:#f8d7da; color:#721c24; padding:1rem; margin:1rem 0; border-radius:0.3rem;">
            Please enter a word.
        </div>
        <?php endif; ?>
        
        <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/<?php echo $wordCloudData['id']; ?>/add">
            <input 
                type="text" 
                id="word" 
                name="word" 
                placeholder="Enter your answer (max 30 characters)"
                maxlength="30"
                required
                style="width:100%; font-size: 1.2rem; margin: 1rem 0;"
                autofocus
            >
            <button type="submit" style="width:100%;">Add Word</button>
        </form>
    </article>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
