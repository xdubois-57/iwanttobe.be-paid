<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1>Involved!</h1>
        <p><?php echo htmlspecialchars($lang->translate('involved_intro_text') ?? 'Create interactive events or join one with a short 4-character code.'); ?></p>
        <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/create" style="display:inline-block; margin-right:1rem;">
            <button class="primary" type="submit">Create</button>
        </form>
        <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/join" style="display:inline-block;">
            <input name="event_code" placeholder="Enter code" required maxlength="4" style="text-transform:uppercase;">
            <button type="submit" class="secondary">Join</button>
        </form>
    </article>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
