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
            
            // Create the full wordcloud URL
            const scheme = window.location.protocol.replace(':', '');
            const host = window.location.host;
            const lang = '<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>';
            const eventKey = '<?php echo htmlspecialchars($eventData['key']); ?>';
            const wordCloudId = '<?php echo $wordCloudData['id']; ?>';
            
            const wordCloudUrl = `${scheme}://${host}/${lang}/involved/${eventKey}/wordcloud/${wordCloudId}`;
            
            function fetchLikes() {
                fetch('/ajax/likes?url=' + encodeURIComponent(wordCloudUrl))
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
                
                // Send like to server
                fetch('/ajax/like', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'url=' + encodeURIComponent(wordCloudUrl)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        likeCount.textContent = data.likes;
                    }
                })
                .catch(error => console.error('Error incrementing like:', error));
            }

            likeBtn.addEventListener('click', incrementLike);
            
            // Load initial likes count
            fetchLikes();
        });
        </script>
    </article>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
