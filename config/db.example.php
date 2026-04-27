<?php
// Start the session at the very beginning of every request
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = '100.81.14.21';
$db   = 'workout_tracker';
$user = 'root';
$pass = 'casaos';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper function to protect pages
function redirectIfNotLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>