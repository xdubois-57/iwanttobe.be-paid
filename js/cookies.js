document.addEventListener('DOMContentLoaded', function() {
    const banner = document.getElementById('cookie-banner');
    const acceptButton = document.getElementById('accept-cookies');

    if (banner) {
        // Force the banner to be a direct child of body
        if (banner.parentNode !== document.body) {
            document.body.appendChild(banner);
        }
        
        // Apply inline styles to ensure it's fixed at the bottom with opaque background
        const isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        Object.assign(banner.style, {
            position: 'fixed',
            bottom: '0',
            left: '0',
            right: '0',
            width: '100%',
            background: isDarkMode ? '#121212' : '#ffffff',
            color: isDarkMode ? '#f0f0f0' : '#333333',
            zIndex: '999999',
            padding: '0.5rem 1rem',
            fontSize: '0.85rem',
            borderTop: isDarkMode ? '1px solid #333333' : '1px solid #e0e0e0'
        });
        
        // Ensure content is on a single line
        const content = banner.querySelector('.cookie-content');
        if (content) {
            Object.assign(content.style, {
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                flexWrap: 'nowrap'
            });
        }
        
        // Ensure text is truncated if too long
        const text = banner.querySelector('.cookie-content p');
        if (text) {
            Object.assign(text.style, {
                whiteSpace: 'nowrap',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                margin: '0'
            });
        }
    }

    if (banner && acceptButton) {
        // Style the button
        Object.assign(acceptButton.style, {
            whiteSpace: 'nowrap',
            background: 'var(--primary)',
            color: 'var(--primary-inverse)',
            border: 'none',
            padding: '0.3rem 0.8rem',
            borderRadius: '3px',
            fontSize: '0.85rem',
            cursor: 'pointer',
            flexShrink: '0'
        });
        
        acceptButton.addEventListener('click', function() {
            // Set cookie for 1 year
            document.cookie = 'cookie_consent=1; max-age=31536000; path=/; SameSite=Strict';
            banner.style.display = 'none';
        });
    }
});
