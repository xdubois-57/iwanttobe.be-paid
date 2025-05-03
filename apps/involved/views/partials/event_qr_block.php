<?php
/**
 * Reusable QR/event info block for the involved app.
 * Usage:
 *   include __DIR__.'/../partials/event_qr_block.php';
 *   echo renderEventQrBlock($url, $eventCode, $eventPassword);
 *
 * @param string $url          The URL to encode in the QR code
 * @param string $eventCode    The event code to display
 * @param string|null $eventPassword The event password (optional)
 */
if (!function_exists('renderEventQrBlock')) {
    function renderEventQrBlock($url, $eventCode, $eventPassword = null) {
        require_once __DIR__ . '/../../../../lib/QrHelper.php';
        require_once __DIR__ . '/../../../../controllers/LanguageController.php';
        $lang = LanguageController::getInstance();
        ob_start();
        $qrSvg = QrHelper::renderSvg($url);
        ?>
        <div class="event-qr-block" style="text-align:center;margin:1.5em 0;">
            <div style="max-width:200px;margin:0 auto;">
                <?php echo $qrSvg; ?>
            </div>
            <div style="margin-top:0.5em;font-size:0.95em;color:#333;">
                <div><strong><?php echo htmlspecialchars($lang->translate('event_code')); ?></strong> <?php echo htmlspecialchars($eventCode); ?></div>
                <?php if (!empty($eventPassword)): ?>
                    <div style="margin-top:0.4em;color:#666;"><strong><?php echo htmlspecialchars($lang->translate('event_password')); ?></strong> <?php echo htmlspecialchars($eventPassword); ?></div>
                <?php endif; ?>
            </div>
            <button type="button" class="event-share-btn" style="margin-top:1em;font-size:0.95em;padding:0.5em 1.2em;cursor:pointer;" onclick="shareEvent('<?php echo htmlspecialchars(addslashes($url)); ?>')">
                <?php echo htmlspecialchars($lang->translate('share_button')); ?>
            </button>
        </div>
        <script>
        function shareEvent(url) {
            if (navigator.share) {
                navigator.share({ url })
                    .catch(() => {});
            } else {
                // Fallback: copy to clipboard
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(function() {
                        alert('<?php echo htmlspecialchars($lang->translate('copy_success')); ?>');
                    }, function() {
                        prompt('<?php echo htmlspecialchars($lang->translate('share_link_prompt')); ?>', url);
                    });
                } else {
                    prompt('<?php echo htmlspecialchars($lang->translate('share_link_prompt')); ?>', url);
                }
            }
        }
        </script>
        <?php
        return ob_get_clean();
    }
}
