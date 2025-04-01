<?php
require_once __DIR__ . '/../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
include 'header.php';
?>

<main class="container">
    <h1><?php echo $lang->translate('about_title'); ?></h1>
    <p>About page content goes here</p>
</main>
