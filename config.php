<?php
// Database configuration
$db_host = "127.0.0.1";
$db_port = 3307;
$db_user = "root";
$db_pass = "";
$db_name = "campus_booking";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

// Check connection
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Start PHP Session for keeping track of logged in users
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
