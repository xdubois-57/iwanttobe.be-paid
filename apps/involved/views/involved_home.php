<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1>Involved!</h1>
        <p><?php echo htmlspecialchars($lang->translate('involved_intro_text') ?? 'Create interactive events or join one with a short 4-character code.'); ?></p>
    </article>

    <!-- Grid container for responsive layout -->
    <div class="grid" style="margin-top: 2rem; gap: 2rem;">
        <!-- Join Event (first) -->
        <article style="padding: 1rem;">
            <h2>Join Event</h2>
            <p style="margin: 0.5rem 0;">Enter a 4-character code to join an existing event.</p>
            <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/join" style="margin-top: 1rem;">
                <input id="event-code" name="event_code" placeholder="Event code" required maxlength="4" style="width: 100%;">
                
                <button class="primary" type="submit" style="margin-top: 1rem; width: 100%;">Join Event</button>
            </form>
        </article>

        <!-- Create Event (second) -->
        <article style="padding: 1rem;">
            <h2>Create Event</h2>
            <p style="margin: 0.5rem 0;">Generate a new event with an optional password for secure access.</p>
            <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/create" style="margin-top: 1rem;">
                <input id="create-password" name="password" type="password" placeholder="Password (optional)" style="width: 100%;">
                
                <button class="primary" type="submit" style="margin-top: 1rem; width: 100%;">Create New Event</button>
            </form>
        </article>
    </div>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
