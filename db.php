<?php
// PostgreSQL database connection settings
$host = 'localhost';
$user = 'postgres';       // PostgreSQL username
$password = '11042001/05/2004';  // PostgreSQL password
$database = 'webappDB';    // Database name

// Create connection
$conn = pg_connect("host=$host dbname=$database user=$user password=$password");

// Check connection
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// Query to fetch all users from the 'users' table
$query = "SELECT * FROM users";
$result = pg_query($conn, $query);

// Check if the query was successful
if (!$result) {
    die("Error in query execution: " . pg_last_error());
}

// Fetch data and return it as JSON
$data = [];
while ($row = pg_fetch_assoc($result)) {
    $data[] = $row;
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($data);

// Close the connection
pg_close($conn);
?>
