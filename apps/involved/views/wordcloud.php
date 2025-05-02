<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
require_once __DIR__ . '/../../../lib/QrHelper.php';
$lang = LanguageController::getInstance();
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
            <!-- Placeholder for word cloud features (responses, visualization, etc.) -->
            <p style="color:#888;">(Word cloud content will appear here.)</p>
            
            <div style="margin-top: 1.5rem;">
                <a href="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/<?php echo $wordCloudData['id']; ?>/add" class="button">
                    Add Your Word
                </a>
            </div>
            
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
            <h2>QR Code</h2>
            <div style="margin: 1rem 0;">
                <?php
                $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
                $currentUrl = $scheme . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                // Change URL to point to the add word form
                $addWordUrl = $scheme . "://" . $_SERVER['HTTP_HOST'] . '/' . 
                    htmlspecialchars($lang->getCurrentLanguage()) . '/involved/' . 
                    urlencode($eventData['key']) . '/' . $wordCloudData['id'] . '/add';
                $qrSvg = QrHelper::renderSvg($addWordUrl);
                ?>
                <div style="max-width: 200px; margin: 0 auto;">
                   <a href="<?php echo $addWordUrl; ?>" target="_blank" title="Open add word form in new window">
                    <?php echo $qrSvg; ?>
                   </a>
                </div>
                <p style="margin-top: 0.5rem; font-size: 0.8rem;">Scan this QR code to add your word</p>
                <p style="margin-top: 0.5rem; font-size: 0.8rem;">Click to open the add word form</p>
            </div>
        </article>
    </div>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
