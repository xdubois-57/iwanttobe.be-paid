<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1><?php echo htmlspecialchars($lang->translate('involved_intro_title')); ?></h1>
        <p><?php echo htmlspecialchars($lang->translate('involved_intro_text')); ?></p>
    </article>

    <!-- Grid container for responsive layout -->
    <div class="grid" style="margin-top: 2rem; gap: 2rem;">
        <!-- Join Event (first) -->
        <article style="padding: 1rem;">
            <h2><?php echo htmlspecialchars($lang->translate('join_event_title')); ?></h2>
            <p style="margin: 0.5rem 0;"><?php echo htmlspecialchars($lang->translate('join_event_description')); ?></p>
            <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/join" style="margin-top: 1rem;">
                <input id="event-code" name="event_code" placeholder="<?php echo htmlspecialchars($lang->translate('event_code_placeholder')); ?>" required maxlength="4" style="width: 100%;">
                
                <button class="primary" type="submit" style="margin-top: 1rem; width: 100%;"><?php echo htmlspecialchars($lang->translate('join_event_button')); ?></button>
            </form>
        </article>

        <!-- Create Event (second) -->
        <article style="padding: 1rem;">
            <h2><?php echo htmlspecialchars($lang->translate('create_event_title')); ?></h2>
            <p style="margin: 0.5rem 0;"><?php echo htmlspecialchars($lang->translate('create_event_description')); ?></p>
            <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/create" style="margin-top: 1rem;">
                <input id="create-password" name="password" type="password" placeholder="<?php echo htmlspecialchars($lang->translate('password_placeholder')); ?>" style="width: 100%;">
                
                <button class="primary" type="submit" style="margin-top: 1rem; width: 100%;"><?php echo htmlspecialchars($lang->translate('create_event_button')); ?></button>
            </form>
        </article>
    </div>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
