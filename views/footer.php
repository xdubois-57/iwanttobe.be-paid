    <?php if(isset($useForm)): ?>
<script src="/js/form-validation.js"></script>
<?php endif; ?>
<script src="/js/share.js"></script>
<script src="/js/cookies.js"></script>
<script src="/js/theme-switcher.js"></script>

<footer class="disclaimer-footer">
    <div class="container">
        <p class="disclaimer-text"><?php echo $lang->translate('disclaimer_text'); ?></p>
        <p class="copyright">&copy; <?php echo date('Y'); ?> QR Transfer</p>
    </div>
</footer>

<?php require_once __DIR__ . '/cookie-banner.php'; ?>
</body>
</html>
