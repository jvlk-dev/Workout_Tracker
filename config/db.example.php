<?php
// Define session lifetime (30 days = 30 * 24 * 60 * 60 seconds)
$timeout = 2592000;

// Set the max lifetime of session data on the server
ini_set('session.gc_maxlifetime', $timeout);

// Set the session cookie to last for 30 days in the browser
session_set_cookie_params($timeout);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = '100.81.14.21';
$db   = 'workout_tracker';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function redirectIfNotLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>