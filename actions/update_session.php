<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $session_id = $_POST['session_id'];
    $workout_date = $_POST['workout_date'];
    $notes = $_POST['notes'];
    $template_id = $_POST['template_id'];

    $set_ids = $_POST['set_ids'];
    $weights = $_POST['weight'];
    $reps = $_POST['reps'];
    $difficulties = $_POST['difficulty'];

    try {
        $pdo->beginTransaction();

        // 1. Update the main session
        $stmt = $pdo->prepare("UPDATE sessions SET workout_date = ?, notes = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$workout_date, $notes, $session_id, $_SESSION['user_id']]);

        // 2. Update each set
        $setStmt = $pdo->prepare("UPDATE session_sets SET weight_val = ?, reps_val = ?, difficulty = ? WHERE id = ?");
        
        foreach ($set_ids as $index => $sid) {
            $diff = $difficulties[$index] ?? 'Moderate';
            $setStmt->execute([$weights[$index], $reps[$index], $diff, $sid]);
        }

        $pdo->commit();
        header("Location: ../tracker.php?template_id=" . $template_id);
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}