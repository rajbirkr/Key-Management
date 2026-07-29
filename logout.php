<?php
require_once __DIR__ . '/includes/config.php';

// Unset all session variables
$_SESSION = array();

// Destroy session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>
