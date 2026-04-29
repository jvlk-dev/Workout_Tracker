<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $t_id = $_POST['template_id'];
    $u_id = $_SESSION['user_id'];
    $name = $_POST['template_name'];
    $names = $_POST['ex_name'];
    $sets = $_POST['ex_sets'];

    try {
        $pdo->beginTransaction();

        // 1. Update Template Name
        $stmt = $pdo->prepare("UPDATE templates SET name = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $t_id, $u_id]);

        // 2. Wipe old exercises and insert new list
        $del = $pdo->prepare("DELETE FROM template_exercises WHERE template_id = ?");
        $del->execute([$t_id]);

        $ins = $pdo->prepare("INSERT INTO template_exercises (template_id, exercise_name, default_sets) VALUES (?, ?, ?)");
        foreach ($names as $idx => $exName) {
            if (!empty($exName)) {
                $ins->execute([$t_id, $exName, $sets[$idx] ?: 3]);
            }
        }

        $pdo->commit();
        header("Location: ../manage_templates.php");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}