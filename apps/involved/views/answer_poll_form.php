<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<style>
.answer-option {
    display: block;
    width: 100%;
    margin: 0.5rem 0;
    padding: 0.8rem 1rem;
    font-size: 1.15rem;
    text-align: center;
    text-decoration: none;
    border-radius: 0.3rem;
    background-color: var(--primary, #1095c1);
    color: white;
    cursor: pointer;
    transition: background-color 0.2s, opacity 0.2s;
}
.answer-option:hover, .answer-option:focus {
    background-color: var(--primary-hover, #0d80a5);
    text-decoration: none;
    color: white;
}
.answer-option.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
#vote-status {
    display: none;
    background: #d4edda;
    color: #155724;
    padding: 1rem;
    margin: 1rem 0;
    border-radius: 0.3rem;
}
</style>
<main class="container">
    <article>
        <h1><?php echo htmlspecialchars($eventItem['question']); ?></h1>
        <div id="vote-status">
            <?php echo htmlspecialchars($lang->translate('vote_recorded', 'Vote recorded!')); ?>
        </div>
        <div id="answer-options" style="margin-top:1rem;">
            <?php foreach ($answers as $a): ?>
            <a href="#" class="answer-option" data-id="<?php echo $a['id']; ?>" role="button">
                <?php echo htmlspecialchars($a['value']); ?>
            </a>
            <?php endforeach; ?>
            <?php if (empty($answers)): ?>
            <div style="text-align:center; margin:2rem 0;">
                <em><?php echo htmlspecialchars($lang->translate('no_answers_yet', 'No answers yet.')); ?></em>
            </div>
            <?php endif; ?>
        </div>
    </article>
</main>
<script>
(function(){
 const answerOptions = document.querySelectorAll('.answer-option');
 const voteStatus = document.getElementById('vote-status');
 const optionsContainer = document.getElementById('answer-options');
 const lang = '<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>';
 const eventKey = '<?php echo htmlspecialchars($event['key']); ?>';
 const pid = <?php echo (int)$poll['id']; ?>;
 
 // Check if already voted (from localStorage)
 const storageKey = `poll_${pid}_voted`;
 if (localStorage.getItem(storageKey)) {
   disableAllOptions();
   voteStatus.style.display = 'block';
 }
 
 answerOptions.forEach(option => {
   option.addEventListener('click', (e) => {
     e.preventDefault();
     
     if (option.classList.contains('disabled')) {
       return;
     }
     
     const aid = option.getAttribute('data-id');
     const url = `/${lang}/involved/${eventKey}/poll/${pid}/answer/${aid}/vote`;
     
     disableAllOptions();
     option.style.opacity = '1'; // Highlight selected option
     
     fetch(url, {
       method: 'POST'
     })
     .then(r => r.json())
     .then(data => {
       if (data.success) {
         voteStatus.style.display = 'block';
         localStorage.setItem(storageKey, 'true');
       } else {
         alert('Error recording your vote. Please try again.');
         enableAllOptions();
       }
     })
     .catch(err => {
       console.error('Error voting:', err);
       alert('Error recording your vote. Please try again.');
       enableAllOptions();
     });
   });
 });
 
 function disableAllOptions() {
   answerOptions.forEach(opt => {
     opt.classList.add('disabled');
   });
 }
 
 function enableAllOptions() {
   answerOptions.forEach(opt => {
     opt.classList.remove('disabled');
   });
 }
})();
</script>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
