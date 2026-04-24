<?php
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. Get the "Header" info
    $focus = $_POST['focus'];
    $workout_date = $_POST['workout_date'];
    
    // --- NEW: Get the template ID from the hidden form field ---
    $template_id = $_POST['template_id'] ?? null; 

    try {
        $pdo->beginTransaction();

        // --- UPDATED: Added template_id to the INSERT and the VALUES (?) ---
        $sql1 = "INSERT INTO sessions (workout_date, focus, template_id) VALUES (?, ?, ?)";
        $stmt1 = $pdo->prepare($sql1);
        
        // --- UPDATED: Pass the $template_id in the execution array ---
        $stmt1->execute([$workout_date, $focus, $template_id]);
        
        $sessionId = $pdo->lastInsertId();

        if (isset($_POST['weight'])) {
            $weights = $_POST['weight'];
            $reps = $_POST['reps'];
            $exercise_names = $_POST['exercise_name'];
            $difficulties = $_POST['difficulty']; // This will be an array like [0 => 'Easy', 1 => 'Moderate'...]

            $sql2 = "INSERT INTO session_sets (session_id, exercise_name, weight_val, reps_val, difficulty) VALUES (?, ?, ?, ?, ?)";
            $stmt2 = $pdo->prepare($sql2);

            foreach ($weights as $index => $weight) {
                if (!empty($weight) && !empty($reps[$index])) {
                    // We use the same $index to find the difficulty
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

        // --- UPDATED: Redirect back to the specific template you were on ---
        header("Location: index.php?template_id=" . $template_id . "&status=success");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error saving workout: " . $e->getMessage());
    }
}