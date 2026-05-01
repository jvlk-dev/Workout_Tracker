<?php
require_once '../config/db.php';
redirectIfNotLoggedIn();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $t_id = $_POST['template_id'];
    $u_id = $_SESSION['user_id'];
    $name = $_POST['template_name'];
    $names = $_POST['ex_name'];
    $sets = $_POST['ex_sets'];
    $types = $_POST['ex_type'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE templates SET name = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $t_id, $u_id]);

        $del = $pdo->prepare("DELETE FROM template_exercises WHERE template_id = ?");
        $del->execute([$t_id]);

        $ins = $pdo->prepare("INSERT INTO template_exercises (template_id, exercise_name, default_sets, tracking_type) VALUES (?, ?, ?, ?)");
        
        foreach ($names as $idx => $exName) {
            if (!empty($exName)) {
                // Determine values for this specific row
                $setCount = (isset($sets[$idx]) && (int)$sets[$idx] > 0) ? (int)$sets[$idx] : 3;
                $trackType = $types[$idx] ?? 'reps';

                $ins->execute([$t_id, $exName, $setCount, $trackType]);
            }
        }

        $pdo->commit();
        header("Location: ../manage_templates.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Critical Error: " . $e->getMessage());
    }
}