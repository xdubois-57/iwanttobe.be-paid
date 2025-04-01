<?php if (!isset($_COOKIE['cookie_consent'])): ?>
<div id="cookie-banner" class="cookie-banner">
    <div class="cookie-content">
        <p><?php echo $lang->translate('cookie_notice'); ?></p>
        <button id="accept-cookies"><?php echo $lang->translate('cookie_accept'); ?></button>
    </div>
</div>
<?php endif; ?>
