<?php
/**
 * QR Transfer
 * Copyright (C) 2025 Xavier Dubois
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
?>

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
