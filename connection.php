<?php
// Central Database Connection
$db_host = "localhost";
$db_user = "root";
$db_pass = ""; 
$db_name = "diza_ticketing_db";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Start PHP session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
