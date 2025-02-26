<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$host = 'localhost';
$user = 'postgres';
$password = '11042001/05/2004';
$database = 'webappDB';

$conn = pg_connect("host=$host dbname=$database user=$user password=$password");
if (!$conn) {
    echo json_encode(["error" => "Connection failed: " . pg_last_error()]);
    exit();
}

$user_email = $_SESSION['email'];
$restaurant_id = $_POST['restaurant_id'] ?? null;

if ($restaurant_id) {
    $query = "UPDATE user_ratings SET rate = NULL WHERE email = $1 AND restaurant_id = $2";
    $result = pg_query_params($conn, $query, [$user_email, $restaurant_id]);

    echo json_encode($result ? ["success" => "Rating cleared successfully"] : ["error" => "Failed to clear rating"]);
} else {
    echo json_encode(["error" => "Invalid request: Missing restaurant_id"]);
}
?>
