<?php

session_start();

if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] != 1) {
    if ($_SESSION['user_id']) {
        header('Location: ../../index.php');
        exit;
    } else {
        header('Location: admin_login.php');
        exit;
    }
}


require_once "../../../../vendor/autoload.php";

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

try {
    $loader = new FilesystemLoader('../admin_templates');
    $twig = new Environment($loader);

    // Render the homepage template
    echo $twig->render('admin_edit_course.html.twig');
} catch (Exception $e) {
    // Handle any Twig errors
    echo 'Error loading template: ' . $e->getMessage();
}
?>