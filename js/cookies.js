document.addEventListener('DOMContentLoaded', function() {
    const banner = document.getElementById('cookie-banner');
    const acceptButton = document.getElementById('accept-cookies');

    if (banner && acceptButton) {
        acceptButton.addEventListener('click', function() {
            // Set cookie for 1 year
            document.cookie = 'cookie_consent=1; max-age=31536000; path=/; SameSite=Strict';
            banner.style.display = 'none';
        });
    }
});
