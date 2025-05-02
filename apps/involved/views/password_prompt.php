<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1>Protected Event</h1>
        
        <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
        <div role="alert" style="background-color: #f8d7da; color: #721c24; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; border: 1px solid #f5c6cb;">
            <strong>Error:</strong> <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>
        
        <p>This event requires a password. Please enter it below to continue.</p>
        
        <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/verify-password">
            <input type="hidden" name="event_code" value="<?php echo htmlspecialchars($eventCode); ?>">
            
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter event password" required style="width: 100%;">
            
            <button class="primary" type="submit" style="margin-top: 1rem; width: 100%;">Continue</button>
        </form>
    </article>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
