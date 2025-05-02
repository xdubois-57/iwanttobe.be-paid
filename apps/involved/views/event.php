<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';

// Include the chillerlan QR code library
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../lib/QrHelper.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
?>
<main class="container">
    <article>
        <h1>Event <?php echo htmlspecialchars($eventData['key']); ?></h1>
        <p>Description: <?php echo htmlspecialchars($eventData['description'] ?? ''); ?></p>
        <p>Created at: <?php echo htmlspecialchars($eventData['created_at']); ?></p>
    </article>

    <!-- Two-column layout -->
    <div class="grid" style="margin-top: 2rem; gap: 2rem;">
        <!-- Left column (3/4 width) -->
        <article style="grid-column: span 3;">
            <h2>Event Details</h2>
            <p>Event code: <?php echo htmlspecialchars($eventData['key']); ?></p>
            <p>Created at: <?php echo htmlspecialchars($eventData['created_at']); ?></p>
            <p>Description: <?php echo htmlspecialchars($eventData['description'] ?? ''); ?></p>
            <h3 style="margin-top:1.5rem;">Word Clouds</h3>
            <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/create" style="margin-bottom:1rem;">
                <input type="text" name="question" placeholder="Enter question" required style="width:100%;margin-bottom:0.5rem;">
                <button class="secondary" type="submit" style="width:100%;">Create Word Cloud</button>
            </form>

            <?php if (!empty($wordClouds)): ?>
            <ul style="list-style:none;padding:0;">
                <?php foreach ($wordClouds as $wc): ?>
                <li style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd;padding:0.5rem 0;">
                    <a href="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/<?php echo $wc['id']; ?>" style="flex:1;">
                        <?php echo htmlspecialchars($wc['question']); ?>
                    </a>
                    <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/<?php echo $wc['id']; ?>/delete" style="margin:0;">
                        <button type="submit" style="background:none;border:none;color:red;font-size:1rem;">&times;</button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p>No word clouds yet.</p>
            <?php endif; ?>
        </article>

        <!-- Right column (1/4 width) -->
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
                <p style="margin-top: 0.5rem; font-size: 0.8rem;">
                    Scan this QR code to access the event
                </p>
                <?php if (!empty($eventData['password'])): ?>
                <p style="margin-top: 0.5rem; font-size: 0.8rem; color: #666;">
                    Event password: <?php echo htmlspecialchars($eventData['password']); ?>
                </p>
                <?php endif; ?>
            </div>
        </article>
    </div>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
