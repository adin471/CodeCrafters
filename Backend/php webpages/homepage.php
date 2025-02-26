<?php
require_once __DIR__ . '/vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);

try {
    $loader = new FilesystemLoader(__DIR__ . '/templates');
    $twig = new Environment($loader);

    // Render the homepage template
    echo $twig->render('homepage.html.twig');
} catch (Exception $e) {
    // Handle any Twig errors
    echo 'Error loading template: ' . $e->getMessage();
}
?>

