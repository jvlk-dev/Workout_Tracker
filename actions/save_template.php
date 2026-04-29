<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    $name = $_POST['template_name'];
    $ex_names = $_POST['ex_name'];
    $ex_sets = $_POST['ex_sets']; // Added this

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO templates (name, user_id) VALUES (?, ?)");
        $stmt->execute([$name, $u_id]);
        $templateId = $pdo->lastInsertId();

        // Updated query to include default_sets
        $stmtEx = $pdo->prepare("INSERT INTO template_exercises (template_id, exercise_name, default_sets) VALUES (?, ?, ?)");
        
        for ($i = 0; $i < count($ex_names); $i++) {
            if (!empty($ex_names[$i])) {
                // Ensure sets is at least 1
                $setCount = (int)$ex_sets[$i] > 0 ? (int)$ex_sets[$i] : 3;
                $stmtEx->execute([$templateId, $ex_names[$i], $setCount]);
            }
        }

        $pdo->commit();
        header("Location: ../tracker.php?template_id=" . $templateId);
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}