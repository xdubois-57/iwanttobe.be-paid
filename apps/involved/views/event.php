<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';

// Include the chillerlan QR code library
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../lib/QrHelper.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
?>
<main class="container">
    <article>
        <h1>Event <?php echo htmlspecialchars($eventData['key']); ?></h1>
        <p>Created at: <?php echo htmlspecialchars($eventData['created_at']); ?></p>
    </article>
    <style>
        .word-cloud-item {
            display: inline-block;
            margin: 0.3rem;
            position: relative;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .word-cloud-item:hover {
            transform: scale(1.05);
        }
        .word-cloud-item:hover .word-cloud-delete {
            color: #dc3545;
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
            background: #f4f4f4;
            border-radius: 1rem;
            transition: background 0.2s;
            cursor: pointer;
        }
        .wordcloud-list-content:hover {
            background: #e0e0e0;
        }
        .wordcloud-list-question {
            cursor: pointer;
            border-radius: 0.7rem;
            padding: 0.1rem 0.4rem;
            transition: none;
            background: none;
            font-size: 1rem;
            line-height: 1.2;
            display: flex;
            align-items: center;
        }
        .word-cloud-delete {
            margin-left: 0.5rem;
            padding: 0;
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 1.2rem;
            line-height: 1;
            vertical-align: middle;
            position: relative;
            top: 7px;
        }
    </style>
    <script>
    function deleteWordCloud(lang, eventCode, wordCloudId) {
        if (confirm('Are you sure you want to delete this word cloud?')) {
            const url = '/' + encodeURIComponent(lang) + '/involved/' + encodeURIComponent(eventCode) + '/wordcloud/' + encodeURIComponent(wordCloudId) + '/delete';
            console.log('Deleting word cloud, URL:', url);
            fetch(url, {
                method: 'POST'
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.indexOf('application/json') !== -1) {
                    return response.json();
                } else {
                    // Not JSON, treat as success
                    return { success: true };
                }
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Failed to delete word cloud');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the word cloud');
            });
        }
    }
    </script>
    </article>

    <!-- Two-column layout -->
    <div class="grid" style="margin-top: 2rem; gap: 2rem;">
        <!-- Left column (3/4 width) -->
        <article style="grid-column: span 3;">
            <?php if (!empty($wordClouds)): ?>
            <h3 style="margin-top:1.5rem;">Word Clouds</h3>
            <div style="margin-top:1rem;">
                <ul id="word-list" style="list-style:none; padding:0;">
                <?php foreach ($wordClouds as $wc): ?>
                    <li class="wordcloud-list-item">
                        <div class="wordcloud-list-content" onclick="window.open('/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/<?php echo $wc['id']; ?>', '_blank');">
                            <span class="wordcloud-list-question">
                                <?php echo htmlspecialchars($wc['question']); ?>
                            </span>
                            <button type="button" class="word-cloud-delete"
                                onclick="event.preventDefault(); event.stopPropagation(); deleteWordCloud('<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>', '<?php echo htmlspecialchars($eventData['key']); ?>', <?php echo $wc['id']; ?>)">
                                ×
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php else: ?>
            <p style="margin-top:1.5rem;">No word clouds yet.</p>
            <?php endif; ?>
            <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/create" style="margin-top:1.5rem;">
                <input type="text" name="question" placeholder="Enter question" required style="width:100%;margin-bottom:0.5rem;">
                <button class="primary" type="submit" style="width:100%;">Create Word Cloud</button>
            </form>
        </article>

        <!-- Right column (1/4 width) -->
        <article style="grid-column: span 1; text-align: center;">
            <div id="event-qr-block" style="margin: 1rem 0;"></div>
        </article>
    </div>
</main>
<script src="/apps/involved/js/eventQrBlock.js"></script>
<script>
// Compute the current event URL and data from PHP
const eventUrl = <?php echo json_encode((isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>;
const eventCode = <?php echo json_encode($eventData['key']); ?>;
const eventPassword = <?php echo json_encode($eventData['password'] ?? null); ?>;
// Render the QR/event info block
new EventQrBlock('#event-qr-block', eventUrl, eventCode, eventPassword);
</script>
<script>
document.querySelectorAll('.delete-wordcloud-form').forEach(form => {
    form.addEventListener('submit', function(event) {
        event.stopPropagation();
        if (!confirm('Are you sure you want to delete this word cloud?')) {
            event.preventDefault();
        }
    });
});
</script>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
