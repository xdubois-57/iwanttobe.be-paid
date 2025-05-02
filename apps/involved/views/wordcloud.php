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
        </article>
        <article style="grid-column: span 1; text-align: center;">
            <h2>QR Code</h2>
            <div style="margin: 1rem 0;">
                <?php
                $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
                $currentUrl = $scheme . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $qrSvg = QrHelper::renderSvg($currentUrl);
                ?>
                <div style="max-width: 200px; margin: 0 auto;">
                    <?php echo $qrSvg; ?>
                </div>
                <p style="margin-top: 0.5rem; font-size: 0.8rem;">Scan this QR code to access this word cloud</p>
            </div>
        </article>
    </div>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
