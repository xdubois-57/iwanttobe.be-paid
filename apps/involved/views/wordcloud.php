<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
require_once __DIR__ . '/../../../lib/QrHelper.php';
$lang = LanguageController::getInstance();
?>
<style>
    .word-cloud-wrapper {
        position: relative;
        min-height: 400px;
        margin-bottom: 2rem;
    }
    
    .wordcloud-list-item {
        display: inline-block;
        margin: 0.3rem;
        position: relative;
    }
    
    .wordcloud-list-content {
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background: var(--background-secondary, #f4f4f4);
        color: var(--text-primary, #121212);
        border-radius: 1rem;
        transition: background 0.2s;
        cursor: pointer;
    }
    
    .wordcloud-list-content:hover {
        background: var(--background-secondary-hover, #e0e0e0);
    }
    
    .wordcloud-list-question {
        font-size: 1rem;
    }
    
    .word-cloud-delete {
        margin-left: 0.5rem;
        padding: 0px;
        background: none;
        border: medium;
        cursor: pointer;
        color: var(--text-secondary, #666);
        font-size: 1.2rem;
    }
    
    .word-cloud-delete:hover {
        color: #dc3545;
    }
    
    @media (max-width: 768px) {
        .word-cloud-wrapper {
            margin-bottom: 1.5rem;
        }
        
        .word-cloud-wrapper canvas {
            max-width: 100%;
            height: auto;
        }
    }
</style>
<?php
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1 data-likes="<?php echo isset($wordCloudData['likes']) ? (int)$wordCloudData['likes'] : 0; ?>">
            <?php echo htmlspecialchars($wordCloudData['question']); ?>
        </h1>
        <p>
            <a href="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>">
                ← Back to event <?php echo htmlspecialchars($eventData['key']); ?>
            </a>
        </p>
    </article>
    <div class="grid" style="margin-top: 2rem; gap: 2rem;">
        <article style="grid-column: span 3;">
            <div id="word-cloud-container" class="word-cloud-wrapper" data-wordcloud-url="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/<?php echo $wordCloudData['id']; ?>/words"></div>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/<?php echo $wordCloudData['id']; ?>/add" class="primary" role="button" target="_blank" style="padding: 0.8rem 2rem; font-size: 1.1rem;">
                    <?php echo htmlspecialchars($lang->translate('add_your_word')); ?>
                </a>
            </div>
        </article>
        <article style="grid-column: span 1; text-align: center;">
            <div id="wordcloud-qr-block" style="margin: 1rem 0;"></div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Compute the add word URL
                const scheme = window.location.protocol.replace(':', '');
                const host = window.location.host;
                const lang = '<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>';
                const eventKey = '<?php echo htmlspecialchars($eventData["key"]); ?>';
                const wordCloudId = '<?php echo $wordCloudData["id"]; ?>';
                const eventPassword = <?php echo json_encode($eventData['password'] ?? null); ?>;
                
                // Construct the add word URL
                const addWordUrl = `${scheme}://${host}/${lang}/involved/${eventKey}/wordcloud/${wordCloudId}/add`;
                
                // Render the QR/event info block with no question text
                new EventQrBlock(
                    '#wordcloud-qr-block',
                    addWordUrl,
                    eventKey,
                    eventPassword,
                    '', // No additional text 
                    true // Show share button
                );
            });
            </script>
        </article>
    </div>
    
    <article style="margin-top: 2rem;">
        <?php 
        // Fetch words for this cloud
        $wcModel = new WordCloudModel();
        $words = $wcModel->getWords($wordCloudData['id']);
        
        if (!empty($words)): 
        ?>
        <div>
            <ul id="word-list" style="list-style:none; padding:0;">
            <?php foreach ($words as $word): ?>
                <?php
                    $wordText = '';
                    if (is_array($word) && isset($word[0]) && $word[0] !== null) {
                        $wordText = (string)$word[0];
                    } elseif (is_string($word) && $word !== '') {
                        $wordText = $word;
                    }
                    if ($wordText === '') continue;
                ?>
                <li class="wordcloud-list-item">
                    <div class="wordcloud-list-content">
                        <span class="wordcloud-list-question">
                            <?php echo htmlspecialchars($wordText, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <button onclick="deleteWord(<?php echo $wordCloudData['id']; ?>, '<?php echo addslashes($wordText); ?>')" class="word-cloud-delete">×</button>
                    </div>
                </li>
            <?php endforeach; ?>
            </ul>

            <script>
            function deleteWord(wordCloudId, word) {
                fetch('/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/<?php echo $wordCloudData['id']; ?>/word/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'word=' + encodeURIComponent(word)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Add a small delay before refreshing to ensure server has updated the data
                        setTimeout(refreshWordList, 300);
                    } else {
                        alert('<?php echo htmlspecialchars($lang->translate('wordcloud_failed_delete')); ?>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php echo htmlspecialchars($lang->translate('wordcloud_error_delete')); ?>');
                });
            }

            // Dynamic refresh for the word list
            const wordListUl = document.getElementById('word-list');
            function refreshWordList() {
                fetch('/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/<?php echo $wordCloudData['id']; ?>/words')
                    .then(response => response.json())
                    .then(words => {
                        wordListUl.innerHTML = '';
                        words.forEach(word => {
                            const wordText = Array.isArray(word) ? word[0] : word;
                            if (!wordText) return;
                            
                            const li = document.createElement('li');
                            li.className = 'wordcloud-list-item';
                            
                            const div = document.createElement('div');
                            div.className = 'wordcloud-list-content';
                            
                            const span = document.createElement('span');
                            span.className = 'wordcloud-list-question';
                            span.textContent = wordText;
                            
                            const button = document.createElement('button');
                            button.className = 'word-cloud-delete';
                            button.textContent = '×';
                            button.onclick = function() {
                                deleteWord(<?php echo $wordCloudData['id']; ?>, wordText.replace(/'/g, "\\'"));
                            };
                            
                            div.appendChild(span);
                            div.appendChild(button);
                            li.appendChild(div);
                            wordListUl.appendChild(li);
                        });
                    });
            }
            
            // Call refreshWordList immediately when page loads
            refreshWordList();
            
            // Then refresh periodically
            setInterval(refreshWordList, 5000);
            </script>
        </div>
        <?php endif; ?>
    </article>
</main>

<script>
// Initialize fullscreen QR block when the fullscreen event is triggered
document.addEventListener('DOMContentLoaded', function() {
    console.log("Setting up wordcloud-fullscreen event listener");
    
    // Monitor for fullscreen change events from WordCloudManager
    window.addEventListener('wordcloud-fullscreen-change', function(e) {
        console.log("Fullscreen change event received", e.detail);
        if (e.detail.isFullScreen) {
            // Gather the necessary URL parameters
            const scheme = window.location.protocol.replace(':', '');
            const host = window.location.host;
            const lang = '<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>';
            const eventKey = '<?php echo htmlspecialchars($eventData["key"]); ?>';
            const wordCloudId = '<?php echo $wordCloudData["id"]; ?>';
            const eventPassword = <?php echo json_encode($eventData['password'] ?? null); ?>;
            
            // Construct the add word URL
            const addWordUrl = `${scheme}://${host}/${lang}/involved/${eventKey}/wordcloud/${wordCloudId}/add`;
            
            // Use OverlayObjectHelper to display QR code
            if (window.OverlayObjectHelper) {
                // First ensure the QR block is hidden before setting new data
                window.OverlayObjectHelper.hideQrBlock();
                
                // Set QR data and show it
                setTimeout(function() {
                    window.OverlayObjectHelper.setQrData(
                        addWordUrl,
                        eventKey,
                        eventPassword,
                        '',  // No additional text
                        true // Show share button
                    );
                    
                    // Show QR container
                    window.OverlayObjectHelper.showQrBlock();
                }, 100); // Small delay to ensure proper rendering
            } else {
                console.error('OverlayObjectHelper not available for QR display');
            }
        } else {
            // Hide QR when exiting fullscreen
            if (window.OverlayObjectHelper) {
                window.OverlayObjectHelper.hideQrBlock();
            }
        }
    });
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

<!-- WordCloud library and JavaScript are now loaded through the app.php getJavaScriptFiles method -->
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
