<?php
require_once 'config/db.php';
$templates = $pdo->query("SELECT * FROM templates")->fetchAll();
// ... stats logic ...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Overview</title>
    <link rel="stylesheet" href="assets/css/index_style.css">
    <script src="https://kit.fontawesome.com/9526c175c2.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="content">
        <nav class="side-nav">
            <div class="nav-title">Web Workout</div>
            <hr class="nav-sep">
            
            <a href="overview.php" class="side-nav-button overview-btn">
                <i class="fa-solid fa-chart-line"></i> Overview
            </a>
            
            <hr class="nav-sep">
            
            <div style="flex:1; overflow-y: auto;">
                <?php foreach ($templates as $template): ?>
                    <a href="index.php?template_id=<?php echo $template['id']; ?>" 
                       class="side-nav-button <?php echo ($template['id'] == $current_template_id) ? 'active' : ''; ?>">
                        <i class="fa-solid fa-dumbbell"></i> <?php echo htmlspecialchars($template['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <a href="create_template.php" class="side-nav-button" style="margin-top:10px; color:var(--accent);">
                <i class="fa-solid fa-plus-circle"></i> New Template
            </a>

            <hr class="nav-sep">

            <a href="logout.php" class="side-nav-button" style="color:#ff7b72;">
                <i class="fa-solid fa-power-off"></i> Logout
            </a>
        </nav>

        <div class="logger-history">
            <h1>Dashboard</h1>
            <div class="workout-card">
                <h3>Statistics coming soon...</h3>
                <p>Welcome to your central hub.</p>
            </div>
        </div>
    </div>
</body>
</html>