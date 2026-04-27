<?php
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $workout_date = $_POST['workout_date']; // Now contains YYYY-MM-DDTHH:MM
    $template_id = $_POST['template_id'] ?? null; 
    $notes = $_POST['notes'] ?? '';
    $duration = $_POST['session_duration'] ?? 0; // Captured from JS timer

    try {
        $pdo->beginTransaction();

        // Updated query to include duration
        $stmt1 = $pdo->prepare("INSERT INTO sessions (workout_date, template_id, notes, duration) VALUES (?, ?, ?, ?)");
        $stmt1->execute([$workout_date, $template_id, $notes, $duration]);
        $sessionId = $pdo->lastInsertId();

        if (isset($_POST['weight'])) {
            $weights = $_POST['weight'];
            $reps = $_POST['reps'];
            $exercise_names = $_POST['exercise_name'];
            $difficulties = $_POST['difficulty'];

            $stmt2 = $pdo->prepare("INSERT INTO session_sets (session_id, exercise_name, weight_val, reps_val, difficulty) VALUES (?, ?, ?, ?, ?)");

            foreach ($weights as $index => $weight) {
                if (strlen($weight) > 0 && strlen($reps[$index]) > 0) {
                    $difficulty_value = $difficulties[$index] ?? 'Moderate';
                    $stmt2->execute([
                        $sessionId, 
                        $exercise_names[$index], 
                        $weight, 
                        $reps[$index],
                        $difficulty_value
                    ]);
                }
            }
        }

        $pdo->commit();
        header("Location: ../tracker.php?template_id=" . $template_id);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error saving: " . $e->getMessage());
    }
}