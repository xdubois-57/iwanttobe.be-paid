<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1>Word Cloud <?php echo htmlspecialchars($wordCloudData['id']); ?></h1>
        <p>Question: <?php echo htmlspecialchars($wordCloudData['question']); ?></p>
        <p>Event: <?php echo htmlspecialchars($eventData['key']); ?></p>
    </article>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
