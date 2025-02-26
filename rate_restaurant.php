<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

$host = 'localhost';
$user = 'postgres';
$password = '11042001/05/2004';
$database = 'webappDB';

$conn = pg_connect("host=$host dbname=$database user=$user password=$password");

if (!$conn) {
    die(json_encode(["error" => "Connection failed: " . pg_last_error()]));
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $restaurant_id = $_POST['restaurant_id'];
    $rate = $_POST['rate'];
    $user_email = $_SESSION['email'] ?? null;

    if ($rate < 1 || $rate > 5) {
        die("Invalid rating value. Rating must be between 1 and 5.");
    }

    // Update the rating in the database
    $updateQuery = "
        UPDATE user_ratings
        SET rate = $1
        WHERE restaurant_id = $2 AND email = $3
    ";

    $result = pg_query_params($conn, $updateQuery, array($rate, $restaurant_id, $user_email));

    if ($result) {
        // Redirect back to the user profile page after updating the rating
        header("Location: user.php");
        exit();
    } else {
        die(json_encode(["error" => "Failed to update rating: " . pg_last_error()]));
    }
}
?>

