<?php
require_once 'config/db.php';
redirectIfNotLoggedIn();
$u_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM templates WHERE user_id = ? ORDER BY name ASC");
$stmt->execute([$u_id]);
$templates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Templates | Web Workout</title>
    <link rel="stylesheet" href="assets/css/index_style.css">
    <script src="https://kit.fontawesome.com/9526c175c2.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="content">
        <!-- Re-use your sidebar here -->
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
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                <h1>Manage Templates</h1>
                <a href="create_template.php" class="save-button" style="width:auto; padding: 10px 20px; text-decoration:none;">+ Create New</a>
            </div>

            <?php foreach ($templates as $t): ?>
                <div class="workout-card" style="padding: 1.5rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="font-size: 1.2rem; color: var(--accent);"><?php echo htmlspecialchars($t['name']); ?></strong>
                    </div>
                    <div style="display: flex; gap: 15px;">
                        <a href="edit_template.php?id=<?php echo $t['id']; ?>" class="diff-btn moderate" style="text-decoration:none;"><i class="fa-solid fa-pen"></i> Edit</a>
                        <a href="actions/delete_template.php?id=<?php echo $t['id']; ?>" class="diff-btn hard" style="text-decoration:none;" onclick="return confirm('WARNING: This will delete the template and all history associated with it. Continue?');"><i class="fa-solid fa-trash"></i> Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>