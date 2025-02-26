<?php
session_start();
include 'db_connect.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Get user details from database
    $query = "SELECT * FROM users WHERE email = $1";
    $stmt = pg_prepare($conn, "get_user", $query);
    $result = pg_execute($conn, "get_user", array($email));

    if ($row = pg_fetch_assoc($result)) {
        // Verify password
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['firstname'] = $row['firstname'];
            $_SESSION['email'] = $row['email'];

            // Redirect to homepage after login
            header("Location: index.html");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with this email.";
    }
}
?>
