<?php
$host = "localhost"; // Change if hosted elsewhere
$port = "5432";
$dbname = "webappDB";
$user = "postgres";
$password = "11042001/05/2004";

// Connect to PostgreSQL
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Database connection failed!");
}
?>