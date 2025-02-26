<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login_style.php');
    exit();
}

$host = 'localhost';
$user = 'postgres';
$password = '11042001/05/2004';
$database = 'webappDB';

// Create connection
$conn = pg_connect("host=$host dbname=$database user=$user password=$password");

if (!$conn) {
    die(json_encode(["error" => "Connection failed: " . pg_last_error()]));
}

if (!isset($_SESSION['user_id'], $_SESSION['email'], $_SESSION['firstname'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

// Get user session data
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'];
$user_firstname = $_SESSION['firstname'];

// Decode JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Check if required data is present
if (!$data || !isset($data['restaurant_id'], $data['restaurant_name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit();
}

// Extract restaurant details
$restaurant_id = $data['restaurant_id'];
$restaurant_name = $data['restaurant_name'];

// **Step 1: Check if restaurant is already saved by the user**
$check_query = "SELECT COUNT(*) FROM user_ratings WHERE user_id = $1 AND restaurant_id = $2";
$check_result = pg_query_params($conn, $check_query, array($user_id, $restaurant_id));

if (!$check_result) {
    echo json_encode(['success' => false, 'message' => 'Failed to check for duplicates: ' . pg_last_error($conn)]);
    pg_close($conn);
    exit();
}

$check_row = pg_fetch_result($check_result, 0, 0);
if ($check_row > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already saved this restaurant.']);
    pg_close($conn);
    exit();
} 

// **Step 2: Fetch restaurant type from places table**
$type_query = "SELECT types FROM places WHERE restaurant_id = $1";
$type_result = pg_query_params($conn, $type_query, array($restaurant_id));

if (!$type_result) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch restaurant type: ' . pg_last_error($conn)]); // Log db error
    pg_close($conn);
    exit();
}

$type_row = pg_fetch_assoc($type_result);
$restaurant_type = $type_row ? $type_row['types'] : null;

if (!$restaurant_type) {
    echo json_encode(['success' => false, 'message' => 'Restaurant type not found for restaurant_id: ' . $restaurant_id]); // Add more context to the error
    pg_close($conn);
    exit();
}

// **Step 3: Insert into user_ratings table**
$insert_query = "INSERT INTO user_ratings (user_id, firstname, email, restaurant_id, displayname_text, types, created_at)
                 VALUES ($1, $2, $3, $4, $5, $6, NOW())";

$insert_params = array($user_id, $user_firstname, $user_email, $restaurant_id, $restaurant_name, $restaurant_type);
$insert_result = pg_query_params($conn, $insert_query, $insert_params);

if ($insert_result) {
    echo json_encode(['success' => true, 'message' => 'Restaurant saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save restaurant: ' . pg_last_error($conn)]);
}

pg_close($conn);
?>
