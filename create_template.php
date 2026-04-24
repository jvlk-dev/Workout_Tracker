<?php require_once 'config/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Template</title>
    <link rel="stylesheet" href="assets/css/index_style.css">
    <style>
        .template-form-box { background: #2c2c2c; padding: 2rem; border-radius: 12px; max-width: 600px; margin: 2rem auto; }
        .exercise-input-row { display: flex; gap: 10px; margin-bottom: 10px; }
        .exercise-input-row input { flex: 1; padding: 8px; background: #3a3a3a; border: 1px solid #555; color: white; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="template-form-box">
        <h2>Create New Workout Template</h2>
        <form action="actions/save_template.php" method="POST">
            <label>Template Name:</label>
            <input type="text" name="template_name" class="focus-input" placeholder="e.g., Leg Day" required style="width: 100%; margin-bottom: 20px;">
            
            <div id="exercise-list">
                <h3>Exercises</h3>
                <div class="exercise-input-row">
                    <input type="text" name="ex_name[]" placeholder="Exercise Name" required>
                    <input type="text" name="ex_url[]" placeholder="YouTube Link (Optional)">
                </div>
            </div>

            <button type="button" onclick="addExerciseRow()" style="background: #555; width: 100%; margin-top: 10px;">+ Add Another Exercise</button>
            <button type="submit" class="save-button">Create Template</button>
        </form>
    </div>

    <script>
        function addExerciseRow() {
            const container = document.getElementById('exercise-list');
            const row = document.createElement('div');
            row.className = 'exercise-input-row';
            row.innerHTML = `
                <input type="text" name="ex_name[]" placeholder="Exercise Name" required>
                <input type="text" name="ex_url[]" placeholder="YouTube Link (Optional)">
            `;
            container.appendChild(row);
        }
    </script>
</body>
</html>