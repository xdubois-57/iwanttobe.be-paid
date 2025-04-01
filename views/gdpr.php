<?php
require_once __DIR__ . '/../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
include 'header.php';
?>

<main class="container">
    <h1><?php echo $lang->translate('gdpr_title'); ?></h1>
    <p>GDPR content goes here</p>
</main>
