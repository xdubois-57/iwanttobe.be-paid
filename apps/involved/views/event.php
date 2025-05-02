<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
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
        </article>

        <!-- Right column (1/4 width) -->
        <article style="grid-column: span 1; text-align: center;">
            <h2>QR Code</h2>
            <div style="margin: 1rem 0;">
                <?php
                // Generate QR code URL
                $currentUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($currentUrl);
                ?>
                <img src="<?php echo htmlspecialchars($qrCodeUrl); ?>" alt="QR Code" style="max-width: 100%;">
                <p style="margin-top: 0.5rem; font-size: 0.8rem;">
                    Scan this QR code to access the event
                </p>
            </div>
        </article>
    </div>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
