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
    
    .fullscreen-qr-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 10001;
        background-color: rgba(255, 255, 255, 0.9);
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        max-width: 300px;
        text-align: center;
        display: none;
    }
    
    .fullscreen-qr-container img, 
    .fullscreen-qr-container svg {
        max-width: 100%;
        height: auto;
    }
    
    .fullscreen-password {
        margin-top: 10px;
        font-size: 0.9rem;
        word-break: break-all;
    }
    
    @media (max-width: 768px) {
        .word-cloud-wrapper {
            margin-bottom: 1.5rem;
        }
        
        .word-cloud-wrapper canvas {
            max-width: 100%;
            height: auto;
        }
        
        .fullscreen-qr-container {
            max-width: 180px;
        }
    }
</style>
<?php
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1><?php echo htmlspecialchars($wordCloudData['question']); ?></h1>
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
                    Add Your Word
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
                
                // Render the QR/event info block with the question as additional text and show share button
                new EventQrBlock(
                    '#wordcloud-qr-block',
                    addWordUrl,
                    eventKey,
                    eventPassword,
                    <?php echo json_encode($wordCloudData['question']); ?>,
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
                <li style="display: inline-block; margin: 0.3rem; position: relative;">
                    <div style="display: flex; align-items: center; padding: 0.5rem 1rem; background: rgb(244, 244, 244); border-radius: 1rem;">
                        <?php echo htmlspecialchars($wordText, ENT_QUOTES, 'UTF-8'); ?>
                        <button onclick="deleteWord(<?php echo $wordCloudData['id']; ?>, '<?php echo addslashes($wordText); ?>')" style="margin-left: 0.5rem; padding: 0px; background: none; border: medium; cursor: pointer; color: rgb(102, 102, 102); font-size: 1.2rem;">×</button>
                    </div>
                </li>
            <?php endforeach; ?>
            </ul>

            <script>
            function deleteWord(wordCloudId, word) {
                fetch('/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/<?php echo $wordCloudData['id']; ?>/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'word=' + encodeURIComponent(word)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        refreshWordList();
                    } else {
                        alert('Failed to delete word');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the word');
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
                            const li = document.createElement('li');
                            li.setAttribute('style', 'display: inline-block; margin: 0.3rem; position: relative;');
                            const div = document.createElement('div');
                            div.setAttribute('style', 'display: flex; align-items: center; padding: 0.5rem 1rem; background: rgb(244, 244, 244); border-radius: 1rem;');
                            div.innerHTML = `${Array.isArray(word) ? word[0] : word}<button onclick=\"deleteWord(${<?php echo $wordCloudData['id']; ?>}, '${(Array.isArray(word) ? word[0] : word).replace(/'/g, "\\'")}')\" style=\"margin-left: 0.5rem; padding: 0px; background: none; border: medium; cursor: pointer; color: rgb(102, 102, 102); font-size: 1.2rem;\">×</button>`;
                            li.appendChild(div);
                            wordListUl.appendChild(li);
                        });
                    });
            }
            setInterval(refreshWordList, 5000);
            </script>
        </div>
        <?php endif; ?>
    </article>
</main>

<!-- Fullscreen QR container -->
<div class="fullscreen-qr-container" id="fullscreen-qr">
    <div id="fullscreen-qr-block"></div>
</div>

<script>
// Initialize fullscreen QR block when the fullscreen event is triggered
document.addEventListener('DOMContentLoaded', function() {
    // Custom handler for WordCloud fullscreen events
    window.addEventListener('wordcloud-fullscreen-change', function(e) {
        if (e.detail.isFullScreen) {
            // Initialize the fullscreen QR block only when needed
            if (!document.getElementById('fullscreen-qr-block').hasAttribute('data-initialized')) {
                const scheme = window.location.protocol.replace(':', '');
                const host = window.location.host;
                const lang = '<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>';
                const eventKey = '<?php echo htmlspecialchars($eventData["key"]); ?>';
                const wordCloudId = '<?php echo $wordCloudData["id"]; ?>';
                const eventPassword = <?php echo json_encode($eventData['password'] ?? null); ?>;
                
                const addWordUrl = `${scheme}://${host}/${lang}/involved/${eventKey}/wordcloud/${wordCloudId}/add`;
                
                new EventQrBlock('#fullscreen-qr-block', addWordUrl, eventKey, eventPassword, '', false);
                document.getElementById('fullscreen-qr-block').setAttribute('data-initialized', 'true');
            }
        }
    });
});
</script>

<!-- Include WordCloud library -->
<script src="/apps/involved/js/eventQrBlock.js"></script>
<script src="/vendor/timdream/wordcloud2.js"></script>
<script src="/js/wordcloud.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Setup fullscreen QR code toggle
    const fullscreenQR = document.getElementById('fullscreen-qr');
    
    // Custom handler for WordCloud fullscreen events
    window.addEventListener('wordcloud-fullscreen-change', function(e) {
        if (e.detail.isFullScreen) {
            fullscreenQR.style.display = 'block';
        } else {
            fullscreenQR.style.display = 'none';
        }
    });
});
</script>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
