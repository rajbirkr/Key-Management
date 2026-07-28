
<?php
// Database configuration
// Use only environment variables for credentials.
$db_host = getenv('DB_HOST');
$db_port = getenv('DB_PORT');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');
$db_name = getenv('DB_NAME');

if (!$db_host || !$db_port || !$db_user || !$db_pass || !$db_name) {
    die('Database configuration is incomplete. Please set DB_HOST, DB_PORT, DB_USER, DB_PASS, and DB_NAME.');
}

$conn = mysqli_init();
if (!$conn) {
    die("Database initialization failed.");
}

// Optional timeout
mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 10);

// Connect using SSL.
if (!mysqli_real_connect(
    $conn,
    $db_host,
    $db_user,
    $db_pass,
    $db_name,
    (int)$db_port,
    null,
    MYSQLI_CLIENT_SSL
)) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// if (empty(mysqli_get_ssl_cipher($conn))) {
//     die("Database Connection Failed: SSL is required for database connections.");
// }

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
