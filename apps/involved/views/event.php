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
        <?php if (!empty($eventData['password'])): ?>
            <div style="margin-top:0.5em;display:flex;align-items:center;gap:0.5em;">
                <span style="margin:0;">
                    <?php echo htmlspecialchars($lang->translate('admin_password_label')); ?>
                </span>
                <span id="event-password-span" style="font-family:monospace;font-size:1em;user-select:all;letter-spacing:0.1em;cursor:pointer;" tabindex="0">
                    <?php echo str_repeat('•', mb_strlen($eventData['password'])); ?>
                </span>
            </div>
            <script>
            (function() {
                const pwdSpan = document.getElementById('event-password-span');
                const realPwd = <?php echo json_encode($eventData['password']); ?>;
                const bullets = '•'.repeat(realPwd.length);
                let showing = false;
                function show() {
                    pwdSpan.textContent = realPwd;
                    showing = true;
                }
                function hide() {
                    pwdSpan.textContent = bullets;
                    showing = false;
                }
                pwdSpan.addEventListener('mousedown', show);
                pwdSpan.addEventListener('touchstart', show);
                pwdSpan.addEventListener('mouseup', hide);
                pwdSpan.addEventListener('mouseleave', hide);
                pwdSpan.addEventListener('touchend', hide);
                pwdSpan.addEventListener('touchcancel', hide);
                // Accessibility: also allow keyboard
                pwdSpan.addEventListener('keydown', function(e) {
                    if (e.key === ' ' || e.key === 'Enter') { show(); }
                });
                pwdSpan.addEventListener('keyup', function(e) {
                    if (showing && (e.key === ' ' || e.key === 'Enter')) { hide(); }
                });
            })();
            </script>
        <?php endif; ?>
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
            <h3 style="margin-top:1.5rem;">Add a new question</h3>
            <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/wordcloud/create" style="margin-top:1.5rem;">
                <label for="event-item-type" style="font-weight:bold;">Type</label>
                <select id="event-item-type" name="event_item_type" style="width:100%;margin-bottom:0.5rem;">
                    <option value="wordcloud" selected>Word cloud</option>
                    <option value="multiple_choice">Multiple choice</option>
                    <option value="text">Text</option>
                    <!-- Add more types as needed -->
                </select>
                <input type="text" name="question" placeholder="<?php echo htmlspecialchars($lang->translate('enter_question_placeholder')); ?>" required style="width:100%;margin-bottom:0.5rem;">
                <button class="primary" type="submit" style="width:100%;">Add question</button>
            </form>
        </article>
        <!-- Right column (1/4 width): Event Items List -->
        <article style="grid-column: span 1;">
            <h3 style="margin-top:1.5rem;">Questions</h3>
            <?php
            require_once __DIR__ . '/../models/EventItemModel.php';
            $eventItemModel = new EventItemModel();
            $eventItems = $eventItemModel->getByEvent($eventData['id']);
            ?>
            <div style="margin-top:1rem;">
                <ul id="event-item-list" style="list-style:none; padding:0;">
                <?php foreach ($eventItems as $item): ?>
                    <li class="wordcloud-list-item" data-id="<?php echo $item['id']; ?>">
                        <div class="wordcloud-list-content">
                            <span class="drag-handle" style="font-size:1.3em;vertical-align:middle;margin-right:0.5em;cursor:grab;">≡</span>
                            <span class="wordcloud-list-question">
                                <?php echo htmlspecialchars($item['question']); ?>
                            </span>
                            <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/eventitem/<?php echo $item['id']; ?>/delete" style="display:inline;">
                                <button type="submit" class="word-cloud-delete" title="Delete event item" onclick="return confirm('Are you sure you want to delete this event item?');">×</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($eventItems)): ?>
                    <li><em>No event items yet.</em></li>
                <?php endif; ?>
                </ul>
            </div>
            <!-- SortableJS CDN for drag-and-drop -->
            <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
            <script>
            (function() {
                const list = document.getElementById('event-item-list');
                if (!list) return;
                const sortable = new Sortable(list, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: function () {
                        const orderedIds = Array.from(list.querySelectorAll('li')).map(li => parseInt(li.getAttribute('data-id'), 10));
                        fetch('/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($eventData['key']); ?>/eventitem/reorder', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ orderedIds })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (!data.success) {
                                console.error('Reorder failed', data.error);
                            }
                        })
                        .catch(err => console.error('Reorder error', err));
                    }
                });
            })();
            </script>
        </article>
    </div>

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

<script>
// Remember this event in local storage
(function() {
    try {
        // Check if event should be remembered (either from URL param or just viewing)
        const shouldRemember = new URLSearchParams(window.location.search).has('remember');
        const eventCode = '<?php echo htmlspecialchars($eventData['key']); ?>';
        
        if (eventCode) {
            // Load the helper script dynamically
            const script = document.createElement('script');
            script.src = '/apps/involved/js/eventRememberHelper.js';
            script.onload = function() {
                const helper = new EventRememberHelper();
                helper.addEvent(eventCode);
                
                // If redirected from creation with remember parameter, clean URL
                if (shouldRemember) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            };
            document.head.appendChild(script);
        }
    } catch (e) {
        console.error('Error remembering event:', e);
    }
})();
</script>

<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
