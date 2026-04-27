<?php
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['template_name'];
    $ex_names = $_POST['ex_name'];
    $ex_urls = $_POST['ex_url'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO templates (name) VALUES (?)");
        $stmt->execute([$name]);
        $templateId = $pdo->lastInsertId();

        $stmtEx = $pdo->prepare("INSERT INTO template_exercises (template_id, exercise_name, youtube_url) VALUES (?, ?, ?)");
        
        for ($i = 0; $i < count($ex_names); $i++) {
            if (!empty($ex_names[$i])) {
                $stmtEx->execute([$templateId, $ex_names[$i], $ex_urls[$i]]);
            }
        }

        $pdo->commit();
        header("Location: ../tracker.php?template_id=" . $templateId);
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}