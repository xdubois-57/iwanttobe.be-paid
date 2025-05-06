<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
require_once __DIR__ . '/../../../lib/QrHelper.php';
$lang = LanguageController::getInstance();
?>
<style>
    .poll-wrapper {
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
    .answer-value {
        font-size: 1rem;
    }
    .delete-btn {
        background:none; border:none; color:var(--text-secondary,#666); cursor:pointer; font-size:1.2rem;
    }
    .delete-btn:hover { color:#dc3545; }
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
        <!-- Left: chart placeholder -->
        <article style="grid-column: span 3;">
            <canvas id="poll-chart" class="poll-wrapper"></canvas>
            <div style="text-align:center; margin-top:1rem;">
                <a href="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/<?php echo urlencode($event['key']); ?>/poll/<?php echo $poll['id']; ?>/answer" class="primary" role="button" target="_blank" style="padding:0.8rem 2rem; font-size:1.1rem;">
                    <?php echo htmlspecialchars($lang->translate('add_your_vote', 'Add your vote')); ?>
                </a>
            </div>
        </article>
        <!-- Right: QR code only -->
        <article style="grid-column: span 1; text-align:center;">
            <div id="poll-qr-block" style="margin:1rem 0;"></div>
            <script>
            document.addEventListener('DOMContentLoaded',function(){
                const scheme=window.location.protocol.replace(':','');
                const host=window.location.host;
                const lang='<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>';
                const eventKey='<?php echo htmlspecialchars($event["key"]); ?>';
                const pid='<?php echo $poll['id']; ?>';
                const addVoteUrl=`${scheme}://${host}/${lang}/involved/${eventKey}/poll/${pid}/answer`;
                const eventPassword=<?php echo json_encode($event['password'] ?? null); ?>;
                new EventQrBlock('#poll-qr-block', addVoteUrl, eventKey, eventPassword, '', true);
            });
            </script>
        </article>
    </div>

    <!-- Add possible answers section in a separate article below -->
    <article style="margin-top:2rem;">
        <h3>Add possible answer</h3>
        <form id="add-answer-form" style="margin-top:0.5rem;">
            <input type="text" id="answer-value" placeholder="Answer" required style="width:100%; margin-bottom:0.5rem;">
            <button type="submit" class="primary" style="width:100%;">Add</button>
        </form>
        <ul id="answer-list" style="list-style:none;padding:0;margin-top:1rem;text-align:left;"></ul>
    </article>

</main>
<script>
(function(){
  // Load Chart.js
  const script=document.createElement('script');
  script.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
  script.onload=initPollPage;
  document.head.appendChild(script);

  function initPollPage(){
    const lang='<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>';
    const eventKey='<?php echo htmlspecialchars($event['key']); ?>';
    const pid=<?php echo (int)$poll['id']; ?>;
    const answersEndpoint=`/${lang}/involved/${eventKey}/poll/${pid}/answers`;
    const addEndpoint=`/${lang}/involved/${eventKey}/poll/${pid}/answer/add`;
    const listEl=document.getElementById('answer-list');
    const chartCtx=document.getElementById('poll-chart').getContext('2d');
    const chartType='<?php echo $poll['type']; ?>'.replace('_bar_chart','Bar').replace('vertical','bar').replace('horizontal','bar');
    let myChart=null;
    const form=document.getElementById('add-answer-form');
    const input=document.getElementById('answer-value');
    function renderAnswers(arr){
      listEl.innerHTML='';
      arr.forEach(a=>{
        const li=document.createElement('li');
        li.className='answer-list-item';
        li.innerHTML=`<span class="answer-value">${a.value}</span><span>${a.votes}</span>`;
        listEl.appendChild(li);
      });

      // update chart
      const labels=arr.map(a=>a.value);
      const dataValues=arr.map(a=>a.votes);
      if(!myChart){
          const config={
              type: getChartJsType('<?php echo $poll['type']; ?>'),
              data:{labels:labels,datasets:[{label:'Votes',data:dataValues,backgroundColor:generateColors(labels.length)}]},
              options:{indexAxis: chartType==='bar' && '<?php echo $poll['type']; ?>'==='horizontal_bar_chart'?'y':'x',animation:false}
          };
          myChart=new Chart(chartCtx,config);
      }else{
          myChart.data.labels=labels;
          myChart.data.datasets[0].data=dataValues;
          myChart.update();
      }
    }
    function fetchAnswers(){
      fetch(answersEndpoint).then(r=>r.json()).then(d=>{ if(d.success){renderAnswers(d.answers);} });
    }
    form.addEventListener('submit',function(e){e.preventDefault(); const val=input.value.trim(); if(!val) return; const fd=new FormData(); fd.append('value',val); fetch(addEndpoint,{method:'POST',body:fd}).then(r=>r.json()).then(d=>{if(d.success){input.value=''; fetchAnswers();}});});
    fetchAnswers();
    setInterval(fetchAnswers,10000);

    function getChartJsType(pollType){
        switch(pollType){
            case 'vertical_bar_chart': return 'bar';
            case 'horizontal_bar_chart': return 'bar';
            case 'pie_chart': return 'pie';
            case 'doughnut': return 'doughnut';
            default: return 'bar';
        }
    }
    function generateColors(n){
        const colors=[];
        for(let i=0;i<n;i++){
            const hue=i*360/n;
            colors.push(`hsl(${hue},70%,50%)`);
        }
        return colors;
    }
  }
 })();
</script>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
