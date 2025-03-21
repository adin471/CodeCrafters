<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: homepage.php'); // Redirect to another page if logged in
    exit();
}

require_once "../../../vendor/autoload.php";

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

try {
    $loader = new FilesystemLoader('../templates');
    $twig = new Environment($loader);

    // Render the attendance template
    echo $twig->render('attendance.html.twig');
} catch (Exception $e) {
    // Handle any Twig errors
    echo 'Error loading template: ' . $e->getMessage();
}
?>