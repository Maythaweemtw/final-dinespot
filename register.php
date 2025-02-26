<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: login_style.html"); // Redirect to login page
    exit();
}
    // Retrieve user input
    $firstname = trim($_POST['firstname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $repeat_password = trim($_POST['repeat-password']);
    $age = trim($_POST['age']);
    $mealcost = trim($_POST['mealcost']);

    // Check if passwords matchs
    if ($password !== $repeat_password) {
        die("Passwords do not match!");
    }

    // Database credentials
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

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL query
    $query = "INSERT INTO users (firstname, email, password, age, mealcost) VALUES ($1, $2, $3, $4, $5)";
    $result = pg_query_params($conn, $query, array($firstname, $email, $hashed_password, $age, $mealcost));

    if ($result) {
        // After inserting the user, fetch the user details to set in session
        $query = "SELECT * FROM users WHERE email = $1";
        $result = pg_query_params($conn, $query, array($email));
        $row = pg_fetch_assoc($result);
        if ($row) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['firstname'] = $row['firstname'];
            $_SESSION['email'] = $email;
            $_SESSION['logged_in'] = true;
            
            session_write_close();
            header("Location: index.php");
            exit();
        } else {
            echo "Error fetching user data after registration!";
        }
    } else {
        echo "Registration Failed: " . pg_last_error($conn);
    }
    

    // Close connection
    pg_close($conn);
?>
