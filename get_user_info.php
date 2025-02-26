<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_style.php');
    exit();
  }

$response = [
    "logged_in" => isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true,
    "email" => $_SESSION['email'] ?? "Guest",
    "firstname" => $_SESSION['firstname'] ?? "Guest"
];

header('Content-Type: application/json');
echo json_encode($response);
?>
