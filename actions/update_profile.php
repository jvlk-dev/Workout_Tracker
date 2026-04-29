<?php
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    $weight = $_POST['weight'] ?: null;
    $height = $_POST['height'] ?: null;
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'] ?: null;

    $stmt = $pdo->prepare("UPDATE users SET weight = ?, height = ?, gender = ?, birthdate = ? WHERE id = ?");
    if ($stmt->execute([$weight, $height, $gender, $birthdate, $u_id])) {
        header("Location: ../profile.php?success=1");
    } else {
        echo "Error updating profile.";
    }
}