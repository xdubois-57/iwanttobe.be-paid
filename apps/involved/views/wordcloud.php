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
        <h1><?php echo htmlspecialchars($wordCloudData['question']); ?></h1>
        <p>
            <a href="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>">
                ← Back to event <?php echo htmlspecialchars($eventData['key']); ?>
            </a>
        </p>
    </article>
    <div class="grid" style="margin-top: 2rem; gap: 2rem;">
        <article style="grid-column: span 3;">
            <div id="word-cloud-container" class="word-cloud-wrapper" data-wordcloud-url="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/<?php echo $wordCloudData['id']; ?>/words"></div>
        </article>
        <article style="grid-column: span 1; text-align: center;">
            <div style="margin: 1rem 0;">
                <?php
                $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
                $currentUrl = $scheme . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                // Change URL to point to the add word form
                $addWordUrl = $scheme . "://" . $_SERVER['HTTP_HOST'] . '/' . 
                    htmlspecialchars($lang->getCurrentLanguage()) . '/involved/' . 
                    urlencode($eventData['key']) . '/' . 
                    $wordCloudData['id'] . '/add';
                $qrSvg = QrHelper::renderSvg($addWordUrl);
                ?>
                <div style="max-width: 200px; margin: 0 auto;">
                    <a href="<?php echo $addWordUrl; ?>">
                        <?php echo $qrSvg; ?>
                    </a>
                </div>
                <div style="margin-top: 1rem; display: flex; justify-content: center; align-items: center; height: 50px;">
                    <a href="<?php echo $addWordUrl; ?>" role="button" target="_blank">
                        Add Your Word
                    </a>
                </div>
                <?php if (!empty($eventData['password'])): ?>
                <div style="margin-top: 1rem; text-align: center;">
                    <h3>Event Password</h3>
                    <p style="word-break: break-all;">
                        <?php echo htmlspecialchars($eventData['password']); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
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
                <li style="display:inline-block; margin:0.3rem; position:relative;">
                    <div style="display:flex; align-items:center; padding:0.5rem 1rem; background:#f4f4f4; border-radius:1rem;">
                        <?php echo htmlspecialchars($word['word']); ?>
                        <button onclick="deleteWord(<?php echo $wordCloudData['id']; ?>, '<?php echo addslashes($word['word']); ?>')"
                                style="margin-left:0.5rem; padding:0; background:none; border:none; cursor:pointer; color:#666; font-size:1.2rem;">
                            ×
                        </button>
                    </div>
                </li>
            <?php endforeach; ?>
            </ul>

            <script>
            function deleteWord(wordCloudId, word) {
                fetch('/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/<?php echo $wordCloudData['id']; ?>/delete', {
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
                fetch('/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/<?php echo $wordCloudData['id']; ?>/words')
                    .then(response => response.json())
                    .then(words => {
                        wordListUl.innerHTML = '';
                        if (words.length > 0) {
                            console.log('First word object:', words[0]); // DEBUG
                        }
                        words.forEach(word => {
                            const li = document.createElement('li');
                            li.style.display = 'inline-block';
                            li.style.margin = '0.3rem';
                            li.style.position = 'relative';
                            const div = document.createElement('div');
                            div.style.display = 'flex';
                            div.style.alignItems = 'center';
                            div.style.padding = '0.5rem 1rem';
                            div.style.background = '#f4f4f4';
                            div.style.borderRadius = '1rem';
                            div.appendChild(document.createTextNode(word[0]));
                            const btn = document.createElement('button');
                            btn.textContent = '×';
                            btn.style.marginLeft = '0.5rem';
                            btn.style.padding = '0';
                            btn.style.background = 'none';
                            btn.style.border = 'none';
                            btn.style.cursor = 'pointer';
                            btn.style.color = '#666';
                            btn.style.fontSize = '1.2rem';
                            btn.onclick = () => deleteWord(<?php echo $wordCloudData['id']; ?>, word[0]);
                            div.appendChild(btn);
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
<!-- Include WordCloud library -->
<script src="/vendor/timdream/wordcloud2.js"></script>
<script src="/js/wordcloud.js"></script>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
