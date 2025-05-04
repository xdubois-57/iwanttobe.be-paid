<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1><?php echo htmlspecialchars($wordCloudData['question']); ?></h1>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'true'): ?>
        <div style="background:#d4edda; color:#155724; padding:1rem; margin:1rem 0; border-radius:0.3rem;">
            <?php echo htmlspecialchars($lang->translate('word_added_success')); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'missing_word'): ?>
        <div style="background:#f8d7da; color:#721c24; padding:1rem; margin:1rem 0; border-radius:0.3rem;">
            <?php echo htmlspecialchars($lang->translate('please_enter_word')); ?>
        </div>
        <?php endif; ?>
        
        <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/<?php echo $wordCloudData['id']; ?>/add">
            <input 
                type="text" 
                id="word" 
                name="word" 
                placeholder="<?php echo htmlspecialchars($lang->translate('word_input_placeholder')); ?>"
                maxlength="30"
                required
                style="width:100%; font-size: 1.2rem; margin: 1rem 0;"
                autofocus
            >
            <button type="submit" style="width:100%;"><?php echo htmlspecialchars($lang->translate('add_word_button')); ?></button>
        </form>
        
        <div style="text-align: center; margin-top: 1.5rem;">
            <div id="emoji-buttons" style="display:inline-flex; gap:1rem;">
                <button class="emoji-btn" data-emoji="❤️" type="button" style="background:none; border:none; cursor:pointer; font-size:2.2rem; transition:transform 0.2s;">❤️</button>
                <button class="emoji-btn" data-emoji="😂" type="button" style="background:none; border:none; cursor:pointer; font-size:2.2rem; transition:transform 0.2s;">😂</button>
                <button class="emoji-btn" data-emoji="👍" type="button" style="background:none; border:none; cursor:pointer; font-size:2.2rem; transition:transform 0.2s;">👍</button>
                <button class="emoji-btn" data-emoji="🔥" type="button" style="background:none; border:none; cursor:pointer; font-size:2.2rem; transition:transform 0.2s;">🔥</button>
                <button class="emoji-btn" data-emoji="🎉" type="button" style="background:none; border:none; cursor:pointer; font-size:2.2rem; transition:transform 0.2s;">🎉</button>
                <button class="emoji-btn" data-emoji="😅" type="button" style="background:none; border:none; cursor:pointer; font-size:2.2rem; transition:transform 0.2s;">😅</button>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emojiButtonsDiv = document.getElementById('emoji-buttons');
            const buttons = emojiButtonsDiv.querySelectorAll('.emoji-btn');
            
            // Ensure window.OverlayClientHelper exists
            if (typeof window.OverlayClientHelper !== 'undefined') {
                // Normalize URL to match wordcloud format
                // Extract URL components: /lang/involved/eventCode/wordcloud/wordcloudId/add
                const pathSegments = window.location.pathname.split('/').filter(s => s);
                if (pathSegments.length >= 6 && pathSegments[1] === 'involved' && pathSegments[3] === 'wordcloud') {
                    const baseUrl = window.location.origin;
                    const lang = pathSegments[0];
                    const eventKey = pathSegments[2]; 
                    const wcid = pathSegments[4];
                    
                    // Set the normalized URL for consistent tracking
                    const normalizedUrl = `${baseUrl}/${lang}/involved/${eventKey}/wordcloud/${wcid}`;
                    window.OverlayClientHelper.currentUrl = normalizedUrl;
                    console.log('[Page] Set normalized URL for tracking:', normalizedUrl);
                } else {
                    // Fallback to current URL without query params
                    const urlObj = new URL(window.location.href);
                    urlObj.hash = '';
                    urlObj.search = '';
                    window.OverlayClientHelper.currentUrl = urlObj.toString();
                    console.log('[Page] Set URL manually:', window.OverlayClientHelper.currentUrl);
                }
                
                // Start presence tracking
                if (typeof window.OverlayClientHelper.startPresenceTracking === 'function') {
                    window.OverlayClientHelper.startPresenceTracking();
                }
                
                function sendEmoji(emojiChar, btnEl) {
                    // simple click animation
                    btnEl.style.transform = 'scale(1.3)';
                    setTimeout(() => btnEl.style.transform = '', 200);
                    
                    if (window.OverlayClientHelper && typeof window.OverlayClientHelper.sendEmoji === 'function') {
                        window.OverlayClientHelper.sendEmoji(emojiChar).catch(err => console.error('Error sending emoji:', err));
                    } else {
                        // Fallback direct POST
                        const formData = new FormData();
                        formData.append('url', window.OverlayClientHelper.currentUrl);
                        formData.append('emoji', emojiChar);
                        fetch('/ajax/emoji', { method:'POST', body: formData }).catch(err => console.error('Error sending emoji:', err));
                    }
                }
                
                buttons.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const emoji = btn.dataset.emoji;
                        sendEmoji(emoji, btn);
                    });
                });
            } else {
                console.error("OverlayClientHelper not available");
                // Still allow emoji POST directly
                buttons.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const emojiChar = btn.dataset.emoji;
                        btn.style.transform = 'scale(1.3)';
                        setTimeout(() => btn.style.transform = '', 200);
                        const formData = new FormData();
                        formData.append('url', window.location.href.split('?')[0].split('#')[0]);
                        formData.append('emoji', emojiChar);
                        fetch('/ajax/emoji', { method:'POST', body: formData }).catch(err => console.error('Error sending emoji:', err));
                    });
                });
            }
        });
        </script>
        
        <!-- Initialize OverlayClientHelper for admin link -->
        <script src="/apps/involved/js/OverlayClientHelper.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the OverlayClientHelper which will add the admin link
            const overlayClient = new OverlayClientHelper();
            overlayClient.initialize();
        });
        </script>
    </article>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
