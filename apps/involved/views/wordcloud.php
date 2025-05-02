<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
require_once __DIR__ . '/../../../lib/QrHelper.php';
$lang = LanguageController::getInstance();
?>
<style>
    .word-cloud-wrapper {
        position: relative;
        min-height: 400px;
        margin-bottom: 2rem;
    }
    
    @media (max-width: 768px) {
        .word-cloud-wrapper {
            margin-bottom: 1.5rem;
        }
        
        .word-cloud-wrapper canvas {
            max-width: 100%;
            height: auto;
        }
    }
</style>
<?php
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1>Word Cloud <?php echo htmlspecialchars($wordCloudData['id']); ?></h1>
        <p>Question: <?php echo htmlspecialchars($wordCloudData['question']); ?></p>
        <p>Event: <?php echo htmlspecialchars($eventData['key']); ?></p>
    </article>
    <div class="grid" style="margin-top: 2rem; gap: 2rem;">
        <article style="grid-column: span 3;">
            <h3>Word Cloud Visualization</h3>
            <div id="word-cloud-container" class="word-cloud-wrapper" data-wordcloud-url="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/<?php echo $wordCloudData['id']; ?>/words"></div>
            
            <?php 
            // Fetch words for this cloud
            $wcModel = new WordCloudModel();
            $words = $wcModel->getWords($wordCloudData['id']);
            
            if (!empty($words)): 
            ?>
            <div style="margin-top: 1.5rem;">
                <h3>Words</h3>
                <ul style="list-style:none; padding:0;">
                <?php foreach ($words as $word): ?>
                    <li style="display:inline-block; margin:0.3rem; padding:0.5rem 1rem; background:#f4f4f4; border-radius:1rem;">
                        <?php echo htmlspecialchars($word['word']); ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </article>
        <article style="grid-column: span 1; text-align: center;">
            <div style="margin: 1rem 0;">
                <?php
                $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
                $currentUrl = $scheme . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                // Change URL to point to the add word form
                $addWordUrl = $scheme . "://" . $_SERVER['HTTP_HOST'] . '/' . 
                    htmlspecialchars($lang->getCurrentLanguage()) . '/involved/' . 
                    urlencode($eventData['key']) . '/' . 
                    $wordCloudData['id'] . '/add';
                $qrSvg = QrHelper::renderSvg($addWordUrl);
                ?>
                <div style="max-width: 200px; margin: 0 auto;">
                    <a href="<?php echo $addWordUrl; ?>">
                        <?php echo $qrSvg; ?>
                    </a>
                </div>
                <div style="margin-top: 1rem; display: flex; justify-content: center; align-items: center; height: 50px;">
                    <a href="<?php echo $addWordUrl; ?>" role="button">
                        Add Your Word
                    </a>
                </div>
                <?php if (!empty($eventData['password'])): ?>
                <div style="margin-top: 1rem; text-align: center;">
                    <h3>Event Password</h3>
                    <p style="word-break: break-all;">
                        <?php echo htmlspecialchars($eventData['password']); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </article>
    </div>
</main>
<!-- Include WordCloud library -->
<script src="/vendor/timdream/wordcloud2.js"></script>
<script src="/js/wordcloud.js"></script>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
