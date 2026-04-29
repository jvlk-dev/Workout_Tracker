<?php
require_once 'config/db.php';
redirectIfNotLoggedIn();
$u_id = $_SESSION['user_id'];
$t_id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM templates WHERE id = ? AND user_id = ?");
$stmt->execute([$t_id, $u_id]);
$template = $stmt->fetch();
if (!$template) die("Unauthorized.");

$exStmt = $pdo->prepare("SELECT * FROM template_exercises WHERE template_id = ? ORDER BY id ASC");
$exStmt->execute([$t_id]);
$exercises = $exStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Template | Web Workout</title>
    <link rel="stylesheet" href="assets/css/index_style.css">
    <script src="https://kit.fontawesome.com/9526c175c2.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="logger-history" style="max-width: 700px;">
        <h1 style="margin-bottom:2rem;">Edit: <?php echo htmlspecialchars($template['name']); ?></h1>
        
        <form action="actions/update_template.php" method="POST">
            <input type="hidden" name="template_id" value="<?php echo $t_id; ?>">
            
            <div class="workout-card">
                <label class="input-label">Template Name</label>
                <input type="text" name="template_name" class="input-field" style="width:100%; margin-bottom:2rem;" value="<?php echo htmlspecialchars($template['name']); ?>" required>

                <div id="exercise-list">
                    <label class="input-label">Exercises & Set Counts</label>
                    <?php foreach ($exercises as $ex): ?>
                        <div class="exercise-input-row" style="display:flex; gap:10px; margin-bottom:10px;">
                            <input type="text" name="ex_name[]" class="input-field" style="flex:2;" value="<?php echo htmlspecialchars($ex['exercise_name']); ?>" required>
                            <input type="number" name="ex_sets[]" class="input-field" style="flex:0.5;" value="<?php echo $ex['default_sets']; ?>" placeholder="Sets">
                            <button type="button" class="diff-btn hard" onclick="this.parentElement.remove()">X</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" onclick="addEx()" class="diff-btn" style="width:100%; margin-top:10px;">+ Add Exercise</button>
            </div>
            
            <button type="submit" class="save-button">UPDATE TEMPLATE</button>
            <a href="manage_templates.php" style="display:block; text-align:center; margin-top:20px; color:var(--text-dim); text-decoration:none;">Cancel</a>
        </form>
    </div>

    <script>
    function addEx() {
        const div = document.createElement('div');
        div.className = 'exercise-input-row';
        div.style = "display:flex; gap:10px; margin-bottom:10px;";
        div.innerHTML = `
            <input type="text" name="ex_name[]" class="input-field" style="flex:2;" placeholder="Exercise Name" required>
            <input type="number" name="ex_sets[]" class="input-field" style="flex:0.5;" value="3">
            <button type="button" class="diff-btn hard" onclick="this.parentElement.remove()">X</button>
        `;
        document.getElementById('exercise-list').appendChild(div);
    }
    </script>
</body>
</html>