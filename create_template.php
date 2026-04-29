<?php 
require_once 'config/db.php'; 
redirectIfNotLoggedIn();
$u_id = $_SESSION['user_id'];

// Fetch user's templates for the sidebar
$stmt = $pdo->prepare("SELECT * FROM templates WHERE user_id = ?");
$stmt->execute([$u_id]);
$templates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Template | Web Workout</title>
    <link rel="stylesheet" href="assets/css/index_style.css">
    <script src="https://kit.fontawesome.com/9526c175c2.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="content">
        <!-- SIDENAV: Standardized -->
        <nav class="side-nav">
            <div class="nav-title">Web Workout</div>
            <hr class="nav-sep">
            
            <a href="index.php" class="side-nav-button <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-line"></i> Overview
            </a>
            
            <hr class="nav-sep">
            
            <a href="manage_templates.php" class="side-nav-button <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_templates.php' || basename($_SERVER['PHP_SELF']) == 'edit_template.php' || basename($_SERVER['PHP_SELF']) == 'create_template.php') ? 'active' : ''; ?>" style="color:var(--accent);">
                <i class="fa-solid fa-gear"></i> Manage Templates
            </a>

            <hr class="nav-sep-blank">
            
            <div style="flex:1; overflow-y: auto;">
                <?php foreach ($templates as $template): ?>
                    <!-- Dynamic Active Check for Templates (remains based on ID) -->
                    <a href="tracker.php?template_id=<?php echo $template['id']; ?>" 
                    class="side-nav-button <?php echo ($template['id'] == $current_template_id && basename($_SERVER['PHP_SELF']) == 'tracker.php') ? 'active' : ''; ?>">
                        <i class="fa-solid fa-dumbbell"></i> <?php echo htmlspecialchars($template['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <hr class="nav-sep">
            <a href="profile.php" class="side-nav-button <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-user"></i> Profile
            </a>
            <hr class="nav-sep">
            <a href="logout.php" class="side-nav-button" style="color:#ff7b72;">
                <i class="fa-solid fa-power-off"></i> Logout
            </a>
        </nav>

        <div class="logger-history" style="max-width: 800px;">
            <h1 style="margin-bottom: 2rem;">Create New Template</h1>

            <form action="actions/save_template.php" method="POST">
                <div class="workout-card">
                    <div style="margin-bottom: 2rem;">
                        <label class="input-label">Template Name</label>
                        <input type="text" name="template_name" class="input-field" placeholder="e.g., Push Day / Upper Body" required style="width: 100%; text-align: left;">
                    </div>

                    <div id="exercise-list">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <label class="input-label">Exercises & Default Sets</label>
                        </div>
                        
                        <!-- Initial Exercise Row -->
                        <div class="exercise-input-row" style="display: flex; gap: 15px; margin-bottom: 15px;">
                            <input type="text" name="ex_name[]" class="input-field" placeholder="Exercise Name" required style="flex: 3; text-align: left;">
                            <input type="number" name="ex_sets[]" class="input-field" placeholder="Sets" value="3" style="flex: 1;">
                            <div style="width: 40px;"></div> <!-- Spacer for delete button alignment -->
                        </div>
                    </div>

                    <button type="button" onclick="addExerciseRow()" class="diff-btn" style="width: 100%; margin-top: 10px; padding: 12px;">
                        <i class="fa-solid fa-plus"></i> Add Another Exercise
                    </button>
                </div>

                <button type="submit" class="save-button">
                    <i class="fa-solid fa-floppy-disk"></i> CREATE TEMPLATE
                </button>
                
                <a href="manage_templates.php" style="display: block; text-align: center; margin-top: 20px; color: var(--text-dim); text-decoration: none; font-size: 0.9rem;">
                    Cancel and go back
                </a>
            </form>
        </div>
    </div>

    <script>
        function addExerciseRow() {
            const container = document.getElementById('exercise-list');
            const row = document.createElement('div');
            row.className = 'exercise-input-row';
            row.style.display = 'flex';
            row.style.gap = '15px';
            row.style.marginBottom = '15px';
            
            row.innerHTML = `
                <input type="text" name="ex_name[]" class="input-field" placeholder="Exercise Name" required style="flex: 3; text-align: left;">
                <input type="number" name="ex_sets[]" class="input-field" placeholder="Sets" value="3" style="flex: 1;">
                <button type="button" class="diff-btn hard" onclick="this.parentElement.remove()" style="width: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            container.appendChild(row);
        }
    </script>
</body>
</html>