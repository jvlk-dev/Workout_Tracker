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

// Fetch User Biometrics
$stmtU = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtU->execute([$u_id]);
$userData = $stmtU->fetch();

// BMI Calculation logic
$bmi = 0;
$bmi_text = "N/A";
if ($userData['weight'] > 0 && $userData['height'] > 0) {
    $heightInMeters = $userData['height'] / 100;
    $bmi = $userData['weight'] / ($heightInMeters * $heightInMeters);
    $bmi = round($bmi, 1);
    
    if ($bmi < 18.5) $bmi_text = "Underweight";
    elseif ($bmi < 25) $bmi_text = "Healthy";
    elseif ($bmi < 30) $bmi_text = "Overweight";
    else $bmi_text = "Obese";
}
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
            
            <a href="index.php" class="side-nav-button <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-line"></i> Overview
            </a>
            
            <hr class="nav-sep">
            
            <a href="create_template.php" class="side-nav-button" style="color:var(--accent);">
                <i class="fa-solid fa-plus-circle"></i> New Template
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
            <h1 style="margin-bottom: 2rem;">Workout Dashboard</h1>

            <!-- STATS GRID -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
                <div class="workout-card" style="margin-bottom: 0; text-align: center;">
                    <p style="color: var(--text-dim); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">Total Sessions</p>
                    <h2 style="font-size: 3rem; margin: 10px 0; color: var(--accent);"><?php echo $totalWorkouts; ?></h2>
                </div>
                <div class="workout-card" style="margin-bottom: 0; text-align: center;">
                    <p style="color: var(--text-dim); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">Total Volume</p>
                    <h2 style="font-size: 3rem; margin: 10px 0; color: #2ea043;"><?php echo number_format(floatval($totalVolume)); ?> <span style="font-size: 1rem;">kg</span></h2>
                </div>

                <!-- NEW BIOMETRICS CARD -->
                <div class="workout-card" style="margin-bottom: 0; text-align: center; border-color: var(--accent);">
                    <p style="color: var(--text-dim); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">Body Mass Index (BMI)</p>
                    <h2 style="font-size: 3rem; margin: 10px 0; color: var(--accent);"><?php echo $bmi ?: '--'; ?></h2>
                    <p style="font-size: 0.9rem; color: var(--text-dim);"><?php echo $bmi_text; ?></p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>