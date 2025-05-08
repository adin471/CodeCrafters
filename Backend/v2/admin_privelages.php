<?php
session_start();

header('Content-Type: application/json');

try {
    $_SESSION['user_level'] = 1;
    echo json_encode(["status" => "session set"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Session error."]);
}
