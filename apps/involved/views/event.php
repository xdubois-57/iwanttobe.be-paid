<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';

// Include the chillerlan QR code library
require_once __DIR__ . '/../../../vendor/autoload.php';
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
        </article>

        <!-- Right column (1/4 width) -->
        <article style="grid-column: span 1; text-align: center;">
            <h2>QR Code</h2>
            <div style="margin: 1rem 0;">
                <?php
                // Generate QR code using chillerlan
                $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
                $currentUrl = $scheme . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                
                // Configure QR code options
                $options = new QROptions([
                    'eccLevel' => QRCode::ECC_L,     // Lowest error correction level
                    'outputType' => QRCode::OUTPUT_MARKUP_SVG,
                    'scale' => 4,                    // Slightly smaller scale
                    'imageBase64' => false,
                    'addQuietzone' => true,
                    'quietzoneSize' => 1             // Smaller quiet zone
                ]);
                
                // Create QR code instance
                $qrcode = new QRCode($options);
                
                // Generate QR code as SVG
                $qrSvg = $qrcode->render($currentUrl);
                ?>
                <div style="max-width: 200px; margin: 0 auto;">
                    <?php echo $qrSvg; ?>
                </div>
                <p style="margin-top: 0.5rem; font-size: 0.8rem;">
                    Scan this QR code to access the event
                </p>
            </div>
        </article>
    </div>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
