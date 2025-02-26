<?php
session_start();
header("Content-Type: application/json");

error_log("Session Data: " . print_r($_SESSION, true));

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}


// PostgreSQL database connection settings
$host = 'localhost';
$user = 'postgres';
$password = '11042001/05/2004';
$database = 'webappDB';

// Create connection
$conn = pg_connect("host=$host dbname=$database user=$user password=$password");

// Check connection
if (!$conn) {
    die(json_encode(["error" => "Connection failed: " . pg_last_error()]));
}

$user_id = $_SESSION['user_id'];

// Check if session user ID is valid
if (empty($user_id)) {
    echo json_encode(["error" => "Invalid session"]);
    exit;
}

// Fetch user details
$userQuery = "SELECT firstname, email FROM users WHERE user_id = $1";
$userResult = pg_prepare($conn, "get_user", $userQuery);
if (!$userResult) {
    echo json_encode(["error" => "Failed to prepare query: " . pg_last_error($conn)]);
    exit;
}

$userExec = pg_execute($conn, "get_user", [$user_id]);
if (!$userExec) {
    echo json_encode(["error" => "Failed to execute query: " . pg_last_error($conn)]);
    exit;
}

$userData = pg_fetch_assoc($userExec);
if (!$userData) {
    echo json_encode(["error" => "User not found"]);
    exit;
}

// Fetch saved restaurants
$restaurantQuery = "
    SELECT p.restaurant_id, p.displayname_text, p.types, p.rating, p.google_maps_uri 
    FROM places p
    JOIN user_ratings ur ON ur.restaurant_id = p.restaurant_id
    WHERE ur.user_id = $1
";
$restaurantResult = pg_prepare($conn, "get_restaurants", $restaurantQuery);
if (!$restaurantResult) {
    echo json_encode(["error" => "Failed to prepare restaurants query: " . pg_last_error($conn)]);
    exit;
}

$restaurantExec = pg_execute($conn, "get_restaurants", [$user_id]);
if (!$restaurantExec) {
    echo json_encode(["error" => "Failed to execute restaurants query: " . pg_last_error($conn)]);
    exit;
}

$restaurants = pg_fetch_all($restaurantExec) ?: [];

echo json_encode([
    "user_id" => $user_id,
    "firstname" => $userData["firstname"],
    "email" => $userData["email"],
    "restaurants" => $restaurants
]);

?>
