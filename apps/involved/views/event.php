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
        <h1><?php echo htmlspecialchars($lang->translate('event_heading')); ?> <?php echo htmlspecialchars($eventData['key']); ?></h1>
        <p><?php echo htmlspecialchars($lang->translate('created_at')); ?> <?php echo htmlspecialchars($eventData['created_at']); ?></p>
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
            background: var(--background-secondary, #f4f4f4);
            color: var(--text-primary, #121212);
            border-radius: 1rem;
            transition: background 0.2s;
            cursor: pointer;
            /* Dark mode improvements */
            background: var(--background-secondary, #2d2d2d);
            color: var(--text-primary, #ffffff);
            border: 1px solid var(--border-color, #404040);
        }
        .wordcloud-list-content:hover {
            background: var(--background-secondary-hover, #353535);
        }
        .wordcloud-list-question {
            cursor: pointer;
            border-radius: 0.7rem;
            padding: 0.1rem 0.4rem;
            transition: none;
            background: var(--background-secondary, #2d2d2d);
            font-size: 1rem;
            line-height: 1.2;
            display: flex;
            align-items: center;
            color: var(--text-primary, #ffffff);
        }
        .word-cloud-delete {
            margin-left: 0.5rem;
            padding: 0;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-secondary, #ffffff);
            font-size: 1.2rem;
            line-height: 1;
            vertical-align: middle;
            position: relative;
            top: 7px;
        }
    </style>
    <script>
    function deleteWordCloud(lang, eventCode, wordCloudId) {
        if (confirm('<?php echo htmlspecialchars($lang->translate('confirm_delete_wordcloud')); ?>')) {
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
                    alert('<?php echo htmlspecialchars($lang->translate('delete_failed')); ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php echo htmlspecialchars($lang->translate('an_error_occurred')); ?>');
            });
        }
    }
    </script>
    </article>

    <!-- Two-column layout -->
    <div class="grid" style="margin-top: 2rem; gap: 2rem;">
        <!-- Left column (3/4 width) -->
        <article style="grid-column: span 3;">
            <h2><?php echo htmlspecialchars($lang->translate('word_cloud')); ?></h2>
            
            <?php if (!empty($wordClouds)): ?>
            <h3 style="margin-top:1.5rem;"><?php echo htmlspecialchars($lang->translate('word_clouds_title')); ?></h3>
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
            <p style="margin-top:1.5rem;"><?php echo htmlspecialchars($lang->translate('no_word_clouds')); ?></p>
            <?php endif; ?>
            
            <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/create" style="margin-top:1.5rem;">
                <input type="text" name="question" placeholder="<?php echo htmlspecialchars($lang->translate('enter_question_placeholder')); ?>" required style="width:100%;margin-bottom:0.5rem;">
                <button class="primary" type="submit" style="width:100%;"><?php echo htmlspecialchars($lang->translate('create_word_cloud_button')); ?></button>
            </form>
        </article>

        <!-- Right column (1/4 width) -->
        <article style="grid-column: span 1; text-align: center;">
            <div id="event-qr-block" style="margin: 1rem 0;"></div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Compute the current event URL
                const scheme = window.location.protocol.replace(':', '');
                const host = window.location.host;
                const lang = '<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>';
                const eventKey = '<?php echo htmlspecialchars($eventData["key"]); ?>';
                const eventPassword = <?php echo json_encode($eventData['password'] ?? null); ?>;
                
                // Construct the event URL
                const eventUrl = `${scheme}://${host}/${lang}/involved/${eventKey}`;
                
                // Render the QR/event info block
                new EventQrBlock(
                    '#event-qr-block',
                    eventUrl,
                    eventKey,
                    eventPassword
                );
            });
            </script>
        </article>
    </div>
</main>

<!-- Load EventQrBlock script -->
<script src="/apps/involved/js/eventQrBlock.js"></script>

<script>
document.querySelectorAll('.delete-wordcloud-form').forEach(form => {
    form.addEventListener('submit', function(event) {
        event.stopPropagation();
        if (!confirm('<?php echo htmlspecialchars($lang->translate('confirm_delete_wordcloud')); ?>')) {
            event.preventDefault();
        }
    });
});
</script>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
