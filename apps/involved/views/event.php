<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1>Event <?php echo htmlspecialchars($eventData['key']); ?></h1>
        <p>Description: <?php echo htmlspecialchars($eventData['description'] ?? ''); ?></p>
        <p>Created at: <?php echo htmlspecialchars($eventData['created_at']); ?></p>
    </article>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
