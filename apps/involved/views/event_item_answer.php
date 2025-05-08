<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1><?php echo htmlspecialchars($eventItem['question']); ?></h1>
        <p><?php echo htmlspecialchars($lang->translate('add_your_answer')); ?></p>
    </article>

    <article style="margin-top: 2rem;">
        <form id="answer-form" method="post">
            <div class="grid">
                <div>
                    <?php if ($eventItem['type'] === 'wordcloud'): ?>
                    <label for="answer">
                        <?php echo htmlspecialchars($lang->translate('your_word', 'Your word')); ?>
                        <input type="text" id="answer" name="value" placeholder="<?php echo htmlspecialchars($lang->translate('word_placeholder', 'Enter a word')); ?>" maxlength="30" required>
                    </label>
                    <?php else: ?>
                    <label for="answer">
                        <?php echo htmlspecialchars($lang->translate('your_answer', 'Your answer')); ?>
                        <input type="text" id="answer" name="value" placeholder="<?php echo htmlspecialchars($lang->translate('answer_placeholder', 'Enter your answer')); ?>" required>
                    </label>
                    <?php endif; ?>
                </div>
                <div>
                    <button type="submit" class="primary"><?php echo htmlspecialchars($lang->translate('submit_answer', 'Submit')); ?></button>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
            <div id="success-message" role="alert" style="background:#d4edda; color:#155724; padding:1rem; border-radius:0.5rem; margin-top:1rem;">
                <?php echo htmlspecialchars($lang->translate('answer_submitted_successfully', 'Answer submitted successfully!')); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
            <div id="error-message" role="alert" style="background:#f8d7da; color:#721c24; padding:1rem; border-radius:0.5rem; margin-top:1rem;">
                <?php 
                $errorMsg = 'An error occurred';
                switch ($_GET['error']) {
                    case 'missing_word':
                        $errorMsg = $lang->translate('missing_word_error', 'Please enter a word');
                        break;
                    default:
                        $errorMsg = $lang->translate('general_error', 'An error occurred');
                }
                echo htmlspecialchars($errorMsg);
                ?>
            </div>
            <?php endif; ?>
        </form>
    </article>

    <article style="margin-top: 2rem; text-align: center;">
        <a href="javascript:window.close();" role="button"><?php echo htmlspecialchars($lang->translate('close_window', 'Close Window')); ?></a>
    </article>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('answer-form');
    const input = document.getElementById('answer');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const value = input.value.trim();
        if (!value) return;
        
        const formData = new FormData();
        formData.append('value', value);
        
        const endpoint = '/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($event['key']); ?>/eventitem/<?php echo $eventItem['id']; ?>/answer/add';
        
        fetch(endpoint, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                
                // Show success message
                let successMsg = document.getElementById('success-message');
                if (!successMsg) {
                    successMsg = document.createElement('div');
                    successMsg.id = 'success-message';
                    successMsg.setAttribute('role', 'alert');
                    successMsg.style.cssText = 'background:#d4edda; color:#155724; padding:1rem; border-radius:0.5rem; margin-top:1rem;';
                    form.appendChild(successMsg);
                }
                successMsg.textContent = '<?php echo htmlspecialchars($lang->translate('answer_submitted_successfully', 'Answer submitted successfully!')); ?>';
                
                // Clear any error message
                const errorMsg = document.getElementById('error-message');
                if (errorMsg) {
                    errorMsg.style.display = 'none';
                }
            } else {
                // Show error message
                let errorMsg = document.getElementById('error-message');
                if (!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.id = 'error-message';
                    errorMsg.setAttribute('role', 'alert');
                    errorMsg.style.cssText = 'background:#f8d7da; color:#721c24; padding:1rem; border-radius:0.5rem; margin-top:1rem;';
                    form.appendChild(errorMsg);
                }
                errorMsg.textContent = data.error || '<?php echo htmlspecialchars($lang->translate('general_error', 'An error occurred')); ?>';
                errorMsg.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Show error message
            let errorMsg = document.getElementById('error-message');
            if (!errorMsg) {
                errorMsg = document.createElement('div');
                errorMsg.id = 'error-message';
                errorMsg.setAttribute('role', 'alert');
                errorMsg.style.cssText = 'background:#f8d7da; color:#721c24; padding:1rem; border-radius:0.5rem; margin-top:1rem;';
                form.appendChild(errorMsg);
            }
            errorMsg.textContent = '<?php echo htmlspecialchars($lang->translate('network_error', 'Network error. Please try again.')); ?>';
            errorMsg.style.display = 'block';
        });
    });
});
</script>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
