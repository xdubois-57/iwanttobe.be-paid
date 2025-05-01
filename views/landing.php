<?php
require_once __DIR__ . '/../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/header.php';
?>
<main class="container">
    <h1><?php echo $lang->translate('landing_choose_app') ?? 'Choose an application'; ?></h1>
    <ul class="app-list">
        <li><a class="button" href="/<?php echo $lang->getCurrentLanguage(); ?>/paid">Paid!</a></li>
        <li><a class="button" href="/<?php echo $lang->getCurrentLanguage(); ?>/involved">Involved!</a></li>
        <li><a class="button" href="/<?php echo $lang->getCurrentLanguage(); ?>/drive">Drive</a></li>
    </ul>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
