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
