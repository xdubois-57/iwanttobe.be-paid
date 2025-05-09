<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container" style="text-align:center; padding:3rem 1rem;">
    <h1><?php echo htmlspecialchars($lang->translate('waiting_room_title', 'Please wait for the event to start')); ?></h1>
    <p style="margin-top:1.5rem;font-size:1.2em; color:#555;">
        <?php echo htmlspecialchars($lang->translate('waiting_room_message', 'The event organizer will start soon. Please keep this page open.')); ?>
    </p>
    <div style="margin-top:2.5rem; color:#aaa; font-size:0.95em;">
        <?php echo htmlspecialchars($lang->translate('waiting_room_tip', 'This page will update automatically when the event begins.')); ?>
    </div>
</main>

<!-- Initialize the overlay client helper just like in the add word form -->
<script src="/apps/involved/js/OverlayObjectHelper.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the overlay object helper for presence tracking
    if (window.OverlayObjectHelper) {
        window.OverlayObjectHelper.initialize();
        // Optionally set custom polling interval if needed
        window.OverlayObjectHelper.presenceIntervalTime = 3000; // Check every 3 seconds
        window.OverlayObjectHelper.startPresencePolling();
    }

});
</script>

<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
