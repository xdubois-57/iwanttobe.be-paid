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
            <button id="like-button" type="button" style="background: none; border: none; cursor: pointer; font-size: 2rem; padding: 0.5rem 1rem; transition: transform 0.2s;">
                <span id="like-heart" style="display: inline-block;">❤️</span>
                <span id="like-count" style="font-size: 1.5rem; margin-left: 0.5rem;">0</span>
            </button>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const likeBtn = document.getElementById('like-button');
            const likeCount = document.getElementById('like-count');
            const heart = document.getElementById('like-heart');
            
            // Ensure window.OverlayClientHelper exists
            if (typeof window.OverlayClientHelper !== 'undefined') {
                // Get current URL
                if (!window.OverlayClientHelper.currentUrl) {
                    // Set URL manually if calculateCurrentUrl isn't available
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
                
                function fetchLikes() {
                    fetch('/ajax/likes?url=' + encodeURIComponent(window.OverlayClientHelper.currentUrl))
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                likeCount.textContent = data.likes;
                            }
                        })
                        .catch(error => console.error('Error fetching likes:', error));
                }

                function incrementLike() {
                    // Show animation regardless of server response
                    heart.style.transform = 'scale(1.3)';
                    setTimeout(() => heart.style.transform = '', 200);
                    
                    // Send like using the OverlayClientHelper
                    if (typeof window.OverlayClientHelper.like === 'function') {
                        window.OverlayClientHelper.like()
                            .then(likes => {
                                likeCount.textContent = likes;
                            })
                            .catch(error => console.error('Error incrementing like:', error));
                    } else {
                        // Fallback if like method doesn't exist
                        const formData = new FormData();
                        formData.append('url', window.OverlayClientHelper.currentUrl);
                        
                        fetch('/ajax/like', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                likeCount.textContent = data.likes;
                            }
                        })
                        .catch(error => console.error('Error incrementing like:', error));
                    }
                }

                likeBtn.addEventListener('click', incrementLike);
                
                // Load initial likes count
                fetchLikes();
            } else {
                console.error("OverlayClientHelper not available");
                // Basic like functionality without the helper
                likeBtn.addEventListener('click', function() {
                    const formData = new FormData();
                    const currentUrl = window.location.href.split('?')[0].split('#')[0];
                    formData.append('url', currentUrl);
                    
                    fetch('/ajax/like', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            likeCount.textContent = data.likes;
                        }
                    });
                });
                
                // Initial load
                fetch('/ajax/likes?url=' + encodeURIComponent(window.location.href.split('?')[0].split('#')[0]))
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            likeCount.textContent = data.likes;
                        }
                    });
            }
        });
        </script>
    </article>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
