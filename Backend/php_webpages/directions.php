<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: attendance.php'); // Redirect to another page if not logged in
    exit();
}

require_once "../../../vendor/autoload.php";

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

try {
    $loader = new FilesystemLoader('../templates');
    $twig = new Environment($loader);

    // Render the homepage template
    echo $twig->render('directions.html.twig');
} catch (Exception $e) {
    // Handle any Twig errors
    echo 'Error loading template: ' . $e->getMessage();
}
?>