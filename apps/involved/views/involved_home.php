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
    <div class="grid" style="margin-top: 2rem;">
        <!-- Join Event (first) -->
        <article>
            <h2>Join Event</h2>
            <p>Enter the 4-character code and optional password to join an existing event.</p>
            <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/join">
                <label for="event-code">Event Code</label>
                <input id="event-code" name="event_code" placeholder="4-character code (e.g. AB12)" required maxlength="4" style="text-transform:uppercase; width: 100%;">
                
                <label for="join-password">Password</label>
                <input id="join-password" name="password" type="password" placeholder="Enter password if required" style="width: 100%;">
                
                <button class="primary" type="submit" style="margin-top: 1rem; width: 100%;">Join Event</button>
            </form>
        </article>

        <!-- Create Event (second) -->
        <article>
            <h2>Create Event</h2>
            <p>Generate a new event with an optional password for secure access.</p>
            <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/create">
                <label for="create-password">Password (Optional)</label>
                <input id="create-password" name="password" type="password" placeholder="Add password for secure access" style="width: 100%;">
                
                <button class="primary" type="submit" style="margin-top: 1rem; width: 100%;">Create New Event</button>
            </form>
        </article>
    </div>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
