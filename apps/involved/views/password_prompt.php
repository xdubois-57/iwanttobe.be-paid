<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1><?php echo htmlspecialchars($lang->translate('protected_event_title')); ?></h1>
        
        <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
        <div role="alert" style="background-color: #f8d7da; color: #721c24; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; border: 1px solid #f5c6cb;">
            <strong><?php echo htmlspecialchars($lang->translate('error_heading')); ?></strong> <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>
        
        <p><?php echo htmlspecialchars($lang->translate('password_prompt_description')); ?></p>
        
        <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/verify-password">
            <input type="hidden" name="event_code" value="<?php echo htmlspecialchars($eventCode); ?>">
            
            <label for="password"><?php echo htmlspecialchars($lang->translate('password_label')); ?></label>
            <input id="password" name="password" type="password" 
                   placeholder="<?php echo htmlspecialchars($lang->translate('password_prompt_placeholder')); ?>" 
                   required 
                   style="width: 100%;"
                   autocomplete="new-password">
            
            <button class="primary" type="submit" style="margin-top: 1rem; width: 100%;"><?php echo htmlspecialchars($lang->translate('continue_button')); ?></button>
        </form>
    </article>
</main>
<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
