<?php
require_once 'config/db.php';
redirectIfNotLoggedIn();

$u_id = $_SESSION['user_id'];
$session_id = $_GET['id'] ?? null;

// 1. Fetch Session Info + Security Check
$stmt = $pdo->prepare("SELECT * FROM sessions WHERE id = ? AND user_id = ?");
$stmt->execute([$session_id, $u_id]);
$session = $stmt->fetch();

if (!$session) die("Workout not found or access denied.");

// 2. Fetch templates for sidebar
$templates = $pdo->query("SELECT * FROM templates WHERE user_id = $u_id")->fetchAll();

// 3. Fetch Session Sets
$setStmt = $pdo->prepare("SELECT * FROM session_sets WHERE session_id = ? ORDER BY id ASC");
$setStmt->execute([$session_id]);
$loggedSets = $setStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Workout | Web Workout</title>
    <link rel="stylesheet" href="assets/css/index_style.css">
    <script src="https://kit.fontawesome.com/9526c175c2.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="content">
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

        <div class="logger-history">
            <h1 style="margin-bottom: 2rem;">Edit Workout Session</h1>
            
            <div class="workout-card">
                <form action="actions/update_session.php" method="POST">
                    <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
                    <input type="hidden" name="template_id" value="<?php echo $session['template_id']; ?>">
                    
                    <div style="display:flex; gap: 20px; margin-bottom: 2rem;">
                        <div class="input-group">
                            <span class="input-label">Workout Date</span>
                            <input type="datetime-local" name="workout_date" class="input-field" style="width:250px;" value="<?php echo date('Y-m-d\TH:i', strtotime($session['workout_date'])); ?>">
                        </div>
                    </div>

                    <?php 
                    $current_ex = "";
                    foreach ($loggedSets as $idx => $s): 
                        if ($current_ex != $s['exercise_name']): 
                            $current_ex = $s['exercise_name'];
                    ?>
                        <div class="exercise-title" style="margin-top:2rem;"><?php echo htmlspecialchars($current_ex); ?></div>
                    <?php endif; ?>

                    <div class="set-row">
                        <input type="hidden" name="set_ids[]" value="<?php echo $s['id']; ?>">
                        <div class="set-label">Set</div>
                        <div class="input-group">
                            <input type="number" step="any" name="weight[]" class="input-field bg-<?php echo strtolower($s['difficulty']); ?>" value="<?php echo floatval($s['weight_val']); ?>">
                        </div>
                        <div class="input-group">
                            <input type="number" name="reps[]" class="input-field bg-<?php echo strtolower($s['difficulty']); ?>" value="<?php echo $s['reps_val']; ?>">
                        </div>
                        <div class="difficulty-picker" style="display:flex;">
                            <?php foreach(['Easy', 'Moderate', 'Hard'] as $lvl): ?>
                                <label>
                                    <!-- Added class="diff-radio" -->
                                    <input type="radio" 
                                        name="difficulty[<?php echo $idx; ?>]" 
                                        value="<?php echo $lvl; ?>" 
                                        class="diff-radio"
                                        style="display:none;" 
                                        <?php echo ($s['difficulty'] == $lvl) ? 'checked' : ''; ?>>
                                    <span class="diff-btn <?php echo strtolower($lvl); ?>"><?php echo $lvl; ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <textarea name="notes" class="notes-area" rows="3" style="margin-top:2rem;"><?php echo htmlspecialchars($session['notes']); ?></textarea>
                    <button type="submit" class="save-button">SAVE CHANGES</button>
                    <a href="tracker.php?template_id=<?php echo $session['template_id']; ?>" style="display:block; text-align:center; margin-top:15px; color:var(--text-dim); text-decoration:none;">Cancel</a>
                </form>
            </div>
        </div>
    </div>
<script>
document.querySelectorAll('.set-row').forEach(row => {
    const radios = row.querySelectorAll('.diff-radio');
    const inputs = row.querySelectorAll('.input-field');

    radios.forEach(radio => {
        // When the page loads, if a radio is already checked, color the inputs
        if (radio.checked) {
            const diff = radio.value.toLowerCase();
            inputs.forEach(el => el.classList.add('bg-' + diff));
        }

        // When a user clicks a new difficulty
        radio.addEventListener('change', function() {
            const diff = this.value.toLowerCase();
            inputs.forEach(el => {
                el.classList.remove('bg-easy', 'bg-moderate', 'bg-hard');
                el.classList.add('bg-' + diff);
            });
        });
    });
});
</script>
</body>
</html>

