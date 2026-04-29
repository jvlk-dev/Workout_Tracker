<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();

$session_id = $_GET['id'] ?? null;
$template_id = $_GET['template_id'] ?? null;
$u_id = $_SESSION['user_id'];

if ($session_id) {
    // SECURITY: Ensure this session belongs to the user
    $check = $pdo->prepare("SELECT id FROM sessions WHERE id = ? AND user_id = ?");
    $check->execute([$session_id, $u_id]);
    
    if ($check->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE id = ?");
        $stmt->execute([$session_id]);
    }
}

header("Location: ../tracker.php?template_id=" . $template_id);
exit();