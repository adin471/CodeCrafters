<?php
require_once "../../../vendor/autoload.php";

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

try {
    $loader = new FilesystemLoader('../templates');
    $twig = new Environment($loader);

    // Render the homepage template
    echo $twig->render('courses.html.twig');
} catch (Exception $e) {
    // Handle any Twig errors
    echo 'Error loading template: ' . $e->getMessage();
}
?>