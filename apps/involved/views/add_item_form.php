<?php
/**
 * Form for adding content to an event item
 */
require_once __DIR__ . '/../../../lib/LanguageController.php';
$lang = LanguageController::getInstance();
$langSlug = $lang->getCurrentLanguage();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($langSlug); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($eventItem['question']); ?> - <?php echo htmlspecialchars($event['key']); ?></title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/involved.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo $lang->translate('add_content'); ?></h1>
            <h2><?php echo htmlspecialchars($eventItem['question']); ?></h2>
            <p class="event-code"><?php echo $lang->translate('event'); ?>: <?php echo htmlspecialchars($event['key']); ?></p>
        </header>

        <main>
            <form action="/<?php echo htmlspecialchars($langSlug); ?>/involved/<?php echo htmlspecialchars($event['key']); ?>/eventitem/<?php echo (int)$eventItem['id']; ?>/add" method="post" class="add-content-form">
                <div class="form-group">
                    <label for="content"><?php echo $lang->translate('your_content'); ?>:</label>
                    <input type="text" id="content" name="content" maxlength="100" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $lang->translate('submit'); ?></button>
                    <a href="/<?php echo htmlspecialchars($langSlug); ?>/involved/<?php echo htmlspecialchars($event['key']); ?>/eventitem/<?php echo (int)$eventItem['id']; ?>" class="btn btn-secondary"><?php echo $lang->translate('cancel'); ?></a>
                </div>
            </form>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> QR Transfer</p>
        </footer>
    </div>
</body>
</html>
