<?php
session_start();
// Database connection settings
$host = 'localhost';
$dbname = 'webappDB';
$user = 'postgres';
$db_password = '11042001/05/2004'; // Keep this secure.
$port = '5432';

// Establish PostgreSQL connection
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$db_password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve email and password from the form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to check if the email exists
    $query = "SELECT user_id, firstname, email, password FROM users WHERE email = $1";
    $result = pg_query_params($conn, $query, array($email));

    if ($row = pg_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['firstname'] = $row['firstname']; // Store firstname in session
            $_SESSION['email'] = $row['email'];
            $_SESSION['logged_in'] = true;

            session_regenerate_id(true);
            error_log("Session Set: " . print_r($_SESSION, true));

                // Redirect to dashboard if login is successful
            header("Location: home.php");
            exit();
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "User not found.";
    }
    } else {
        $error_message = "Error executing query: " . pg_last_error($conn);
    }


pg_close($conn); // Close the connection
?>