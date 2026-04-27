<?php
require_once 'config/db.php';

redirectIfNotLoggedIn(); // ADD THIS

$u_id = $_SESSION['user_id']; // For convenience

// Update queries to filter by user
$templates = $pdo->prepare("SELECT * FROM templates WHERE user_id = ?");
$templates->execute([$u_id]);
$templates = $templates->fetchAll();

$totalWorkouts = $pdo->prepare("SELECT COUNT(*) FROM sessions WHERE user_id = ?");
$totalWorkouts->execute([$u_id]);
$totalWorkouts = $totalWorkouts->fetchColumn();

// Total volume for THIS user
$totalVolume = $pdo->prepare("SELECT SUM(weight_val * reps_val) FROM session_sets 
                              JOIN sessions ON session_sets.session_id = sessions.id 
                              WHERE sessions.user_id = ?");
$totalVolume->execute([$u_id]);
$totalVolume = $totalVolume->fetchColumn() ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Web Workout | Overview</title>
    <link rel="stylesheet" href="assets/css/index_style.css">
    <script src="https://kit.fontawesome.com/9526c175c2.js" crossorigin="anonymous"></script>
</head>
<body>

    <div class="content">
        <!-- SIDENAV: Standardized across all pages -->
        <nav class="side-nav">
            <div class="nav-title">Web Workout</div>
            <hr class="nav-sep">
            
            <a href="index.php" class="side-nav-button overview-btn active">
                <i class="fa-solid fa-chart-line"></i> Overview
            </a>
            
            <hr class="nav-sep">
            
            <div style="flex:1; overflow-y: auto;">
                <?php foreach ($templates as $template): ?>
                    <a href="tracker.php?template_id=<?php echo $template['id']; ?>" class="side-nav-button">
                        <i class="fa-solid fa-dumbbell"></i> <?php echo htmlspecialchars($template['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <hr class="nav-sep">
            <a href="create_template.php" class="side-nav-button" style="color:var(--accent);">
                <i class="fa-solid fa-plus-circle"></i> New Template
            </a>
            <hr class="nav-sep">
            <a href="logout.php" class="side-nav-button" style="color:#ff7b72;">
                <i class="fa-solid fa-power-off"></i> Logout
            </a>
        </nav>

        <div class="logger-history">
            <h1 style="margin-bottom: 2rem;">Workout Dashboard</h1>

            <!-- STATS GRID -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
                <div class="workout-card" style="margin-bottom: 0; text-align: center;">
                    <p style="color: var(--text-dim); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">Total Sessions</p>
                    <h2 style="font-size: 3rem; margin: 10px 0; color: var(--accent);"><?php echo $totalWorkouts; ?></h2>
                </div>
                <div class="workout-card" style="margin-bottom: 0; text-align: center;">
                    <p style="color: var(--text-dim); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">Total Volume</p>
                    <h2 style="font-size: 3rem; margin: 10px 0; color: #2ea043;"><?php echo number_format($totalVolume); ?> <span style="font-size: 1rem;">kg</span></h2>
                </div>
            </div>

            <!-- RECENT ACTIVITY -->
            <h2 style="color: var(--text-dim); margin-bottom: 1.5rem;">Recent Activity</h2>
            <?php if(empty($recentSessions)): ?>
                <div class="workout-card history-card">No workouts recorded yet. Pick a template to start!</div>
            <?php else: ?>
                <?php foreach($recentSessions as $sess): ?>
                    <div class="workout-card history-card" style="padding: 1.5rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: var(--accent);"><?php echo htmlspecialchars($sess['template_name']); ?></strong>
                            <div style="font-size: 0.85rem; color: var(--text-dim);"><?php echo date("F j, Y", strtotime($sess['workout_date'])); ?></div>
                        </div>
                        <a href="tracker.php?template_id=<?php echo $sess['template_id']; ?>" class="diff-btn" style="text-decoration: none;">View Template</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>