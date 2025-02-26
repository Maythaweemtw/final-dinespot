<?php
session_start();
include 'db_connect.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    // Insert user into database
    $query = "INSERT INTO users (firstname, email, password) VALUES ($1, $2, $3)";
    $stmt = pg_prepare($conn, "insert_user", $query);
    $result = pg_execute($conn, "insert_user", array($firstname, $email, $password));

    if ($result) {
        echo "Signup successful! <a href='login.html'>Login here</a>";
    } else {
        echo "Error: Could not register user.";
    }
}
?>
