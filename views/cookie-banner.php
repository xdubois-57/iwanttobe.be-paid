<?php if (!isset($_COOKIE['cookie_consent'])): ?>
<div id="cookie-banner" class="cookie-banner">
    <div class="cookie-content">
        <p><?php echo $lang->translate('cookie_notice'); ?></p>
        <button id="accept-cookies"><?php echo $lang->translate('cookie_accept'); ?></button>
    </div>
</div>

<script>
// Ensure the banner is properly positioned at the bottom of the viewport
document.addEventListener('DOMContentLoaded', function() {
    // The main functionality is now in cookies.js
});
</script>
<?php endif; ?>
