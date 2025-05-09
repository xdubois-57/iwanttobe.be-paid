<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
require_once __DIR__ . '/../../../lib/QrHelper.php';
$lang = LanguageController::getInstance();
?>
<style>
    .event-item-wrapper {
        position: relative;
        min-height: 400px;
        margin-bottom: 2rem;
    }
    .answer-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 1rem;
        background: var(--background-secondary,#f4f4f4);
        margin: 0.3rem 0;
        border-radius: 0.8rem;
    }
    
    .answer-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.2em;
        height: 1.2em;
        border-radius: 50%;
        border: 1.5px solid var(--text-secondary,#666);
        background: var(--background-secondary,#f4f4f4);
        color: var(--text-secondary,#666);
        font-size: 0.7em;
        margin-left: 0.5em;
        margin-right: 0.5em;
        line-height: 1.2em;
        box-sizing: border-box;
        user-select: none;
        min-width: 1.2em;
    }
    .answer-value {
        font-size: 1rem;
    }
    .delete-btn, .event-item-delete {
        background: none;
        border: none;
        color: var(--text-secondary,#666);
        cursor: pointer;
        font-size: 1.2rem;
        text-decoration: none;
        padding: 0;
        outline: none;
        transition: color 0.2s;
        margin-left: 0;
    }
    .delete-btn:hover, .event-item-delete:hover {
        color: #dc3545;
        text-decoration: none;
    }
    
    .event-item-list-item {
        display: inline-block;
        margin: 0.3rem;
        position: relative;
    }
    
    .event-item-list-content {
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background: var(--background-secondary, #f4f4f4);
        color: var(--text-primary, #121212);
        border-radius: 1rem;
        transition: background 0.2s;
        cursor: pointer;
    }
    
    .event-item-list-content:hover {
        background: var(--background-secondary-hover, #e0e0e0);
    }
    
    .event-item-list-question {
        font-size: 1rem;
    }
    
</style>
<?php require_once __DIR__ . '/../../../views/header.php'; ?>
<main class="container">
    <article>
        <h1><?php echo htmlspecialchars($eventItem['question']); ?></h1>
        <p>
            <a href="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($event['key']); ?>">
                ← Back to event <?php echo htmlspecialchars($event['key']); ?>
            </a>
        </p>
    </article>

    <div class="grid" style="margin-top:2rem; gap:2rem;">
        <!-- Left column: Future animations -->
        <article style="grid-column: span 1;">
            <span id="future-animations" style="width:100%; height:100%; display:block;"></span>
        </article>
        
        <!-- Right column: Answers management -->
        <article style="grid-column: span 2;">
            <div class="event-item-wrapper">
                <h4>Answers</h4>
                
                <!-- Answer form -->
                <form id="add-answer-form" class="add-item-form">
                    <input type="text" id="answer-value" name="answer-value" placeholder="Your answer..." required>
                    <button type="submit">Add</button>
                </form>
                
                <!-- Answer list -->
                <div class="answers-wrapper">
                    <ul id="answer-list" class="answer-list"></ul>
                </div>
            </div>
        </article>
    </div>

</main>

<script>
// Submit an emoji directly to the AJAX endpoint
async function submitEmoji(emoji) {
    try {
        const response = await fetch(`/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo htmlspecialchars($event['key']); ?>/emoji`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                url: `/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo htmlspecialchars($event['key']); ?>`,
                emoji: emoji
            })
        });
        const data = await response.json();
        if (!data.success) {
            console.error('Failed to submit emoji:', data.error);
        }
    } catch (error) {
        console.error('Error submitting emoji:', error);
    }
}

(function(){
    // Common variables
    const lang='<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>';
    const eventKey='<?php echo htmlspecialchars($event['key']); ?>';
    const itemId=<?php echo (int)$eventItem['id']; ?>;
    const itemType='<?php echo $eventItem['type']; ?>';
    const answersEndpoint=`/${lang}/involved/${eventKey}/eventitem/${itemId}/answers`;
    const addEndpoint=`/${lang}/involved/${eventKey}/eventitem/${itemId}/answer/add`;
    const listEl=document.getElementById('answer-list');
    const form=document.getElementById('add-answer-form');
    const input=document.getElementById('answer-value');
    
    // Overlay helper instance
    let overlayHelper = null;
    if (typeof OverlayObjectHelper !== 'undefined') {
        overlayHelper = window.__itemOverlayHelper = (window.__itemOverlayHelper || new OverlayObjectHelper());
    }
    
    // Initialize answers functionality only
    // All chart and wordcloud code has been removed
    
    // Handle answers functionality
    initAnswers();
    
    // Define the submitAnswer function for global access
    window.submitAnswer = function() {
        const input = document.getElementById('answer-value');
        const value = input.value.trim();
        if (!value) return;
        
        console.log('Submitting answer:', value);
        
        // Create FormData object for submission - trying both field names
        const formData = new FormData();
        formData.append('value', value);
        
        // This is the correct endpoint from the app.php route definition:  /{lang}/involved/{code}/eventitem/{itemid}/answer/add
        fetch(addEndpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Answer added successfully', data);
                input.value = '';
                // Add the new answer to the list immediately
                const answer = { id: data.id || Date.now(), value: value };
                addAnswerToList(answer);
            } else {
                console.error('Failed to add answer:', data);
                alert('Failed to add your answer. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error submitting answer:', error);
            alert('An error occurred. Please try again.');
        });
    };
    
    // Initialize answers functionality
    function initAnswers() {
        // Load initial answers
        loadAnswers();
        
        // Refresh answers periodically
        setInterval(loadAnswers, 5000);
    }
    
    function addAnswerToList(answer) {
        const answerList = document.getElementById('answer-list');
        const li = document.createElement('li');
        li.className = 'event-item-list-item';
        li.dataset.value = answer.value.toLowerCase(); // Store lowercase value for sorting
        li.innerHTML = `
            <div class="event-item-list-content">
                <span class="event-item-list-question" style="cursor:pointer;">${answer.value}</span>
                <span class="answer-count" style="cursor:pointer;">${answer.votes || 1}</span>
                <span class="event-item-delete" onclick="window.deleteAnswer(${answer.id}, ${itemId})">×</span>
            </div>
        `;
        // Add click handler for incrementing count
        const contentDiv = li.querySelector('.event-item-list-content');
        const questionSpan = contentDiv.querySelector('.event-item-list-question');
        const countSpan = contentDiv.querySelector('.answer-count');
        function incrementCountHandler() {
            // Optimistically update the count
            let count = parseInt(countSpan.textContent, 10) || 1;
            countSpan.textContent = count + 1;
            // Background AJAX call
            const formData = new FormData();
            formData.append('value', answer.value);
            fetch(addEndpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert UI if server failed
                    countSpan.textContent = count;
                    alert('Failed to register vote.');
                }
            })
            .catch(() => {
                countSpan.textContent = count;
                alert('Failed to register vote.');
            });
        }
        questionSpan.onclick = incrementCountHandler;
        countSpan.onclick = incrementCountHandler;
        
        // Insert in alphabetical order
        let inserted = false;
        const children = Array.from(answerList.children);
        for (let i = 0; i < children.length; i++) {
            if (answer.value.toLowerCase() < children[i].dataset.value) {
                answerList.insertBefore(li, children[i]);
                inserted = true;
                break;
            }
        }
        if (!inserted) {
            answerList.appendChild(li);
        }
    }
    
    function loadAnswers() {
        console.log('Loading answers from:', answersEndpoint);
        fetch(answersEndpoint)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Server responded with status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Answers data received:', data);
                const answerList = document.getElementById('answer-list');
                answerList.innerHTML = '';
                
                // Ensure data is an array
                const answersArray = Array.isArray(data) ? data : 
                                    (data && data.answers ? data.answers : []);
                
                if (answersArray.length === 0) {
                    console.log('No answers found');
                    // Add a placeholder message if needed
                    // answerList.innerHTML = '<p>No answers yet. Be the first to add one!</p>';
                    return;
                }
                
                // Sort answers alphabetically by value
                const sortedAnswers = [...answersArray].sort((a, b) => {
                    const valueA = (a.value || a.text || a.answer || '').toLowerCase();
                    const valueB = (b.value || b.text || b.answer || '').toLowerCase();
                    return valueA.localeCompare(valueB);
                });
                
                // Render sorted answers
                sortedAnswers.forEach(answer => {
                    // Make sure the answer has the expected structure
                    if (answer && (answer.value || answer.text || answer.answer)) {
                        const formattedAnswer = {
                            id: answer.id || 0,
                            value: answer.value || answer.text || answer.answer,
                            votes: answer.votes || answer.count || 0
                        };
                        addAnswerToList(formattedAnswer);
                    }
                });
            })
            .catch(error => {
                console.error('Error loading answers:', error);
            });
    }
    
    // Expose deleteAnswer to global scope for button click handlers
    window.deleteAnswer = function(answerId, eventItemId) {
        if (!confirm('Are you sure you want to delete this answer?')) {
            return;
        }
        
        console.log(`Deleting answer ID: ${answerId} from event item ID: ${eventItemId}`);
        
        // Use the correct delete endpoint we just added to app.php
        const deleteEndpoint = `/${lang}/involved/${eventKey}/eventitem/${eventItemId}/answer/${answerId}/delete`;
        console.log('Delete endpoint:', deleteEndpoint);
        
        fetch(deleteEndpoint, {
            method: 'POST'
        
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to delete answer');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                loadAnswers();
                if (typeof Logger !== 'undefined' && Logger.getInstance) {
                    Logger.getInstance().info('Deleted answer', {
                        'answer_id': answerId,
                        'event_item_id': eventItemId
                    });
                }
            } else {
                console.error('Failed to delete answer:', data);
                alert('Failed to delete the answer. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error deleting answer:', error);
            alert('An error occurred while deleting the answer. Please try again.');
        });
    };
    
    // Common form handling
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const val = input.value.trim();
        if (!val) return;
        
        const fd = new FormData();
        fd.append('value', val);
        
        fetch(addEndpoint, {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                input.value = '';
                // Refresh the data
                loadAnswers();
            }
        });
    });
})();
</script>

<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
