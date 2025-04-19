<?php
// Start output buffering to prevent "headers already sent" issues
ob_start();

// Start the session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'db';
$dbname = 'chat_app';
$username = 'user';
$password = 'password';

sleep(1);

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$page = $_GET['page'] ?? 'login';
$messages = [];