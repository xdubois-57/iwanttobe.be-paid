<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1><?php echo htmlspecialchars($eventItem['question']); ?></h1>
        <?php if (isset($_GET['voted']) && $_GET['voted'] === 'true'): ?>
        <div style="background:#d4edda; color:#155724; padding:1rem; margin:1rem 0; border-radius:0.3rem;">
            <?php echo htmlspecialchars($lang->translate('vote_recorded', 'Vote recorded!')); ?>
        </div>
        <?php endif; ?>
        <ul id="answer-list" style="list-style:none; padding:0; margin-top:1rem;">
            <?php foreach ($answers as $a): ?>
            <li style="margin:0.5rem 0;">
                <button class="primary answer-btn" data-id="<?php echo $a['id']; ?>" style="width:100%; padding:0.8rem 1rem; font-size:1.15rem;">
                    <?php echo htmlspecialchars($a['value']); ?>
                </button>
            </li>
            <?php endforeach; ?>
            <?php if (empty($answers)): ?>
                <li><em><?php echo htmlspecialchars($lang->translate('no_answers_yet', 'No answers yet.')); ?></em></li>
            <?php endif; ?>
        </ul>
    </article>
</main>
<script>
(function(){
 const buttons=document.querySelectorAll('.answer-btn');
 const lang='<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>';
 const eventKey='<?php echo htmlspecialchars($event['key']); ?>';
 const pid=<?php echo (int)$poll['id']; ?>;
 buttons.forEach(btn=>{
   btn.addEventListener('click',()=>{
     const aid=btn.getAttribute('data-id');
     const url=`/${lang}/involved/${eventKey}/poll/${pid}/answer/${aid}/vote`;
     fetch(url,{method:'POST'}).then(r=>r.json()).then(d=>{
       if(d.success){
         window.location.reload();
       }else{
         alert('Error');
       }
     }).catch(err=>console.error(err));
   });
 });
})();
</script>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
