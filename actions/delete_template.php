<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();

$t_id = $_GET['id'] ?? null;
$u_id = $_SESSION['user_id'];

if ($t_id) {
    $check = $pdo->prepare("SELECT id FROM templates WHERE id = ? AND user_id = ?");
    $check->execute([$t_id, $u_id]);
    if ($check->fetch()) {
        $pdo->prepare("DELETE FROM templates WHERE id = ?")->execute([$t_id]);
    }
}
header("Location: ../manage_templates.php");