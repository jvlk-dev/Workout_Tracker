<?php
require_once 'config/db.php';
redirectIfNotLoggedIn();

$u_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$u_id]);
$user = $stmt->fetch();

// Fetch templates for sidebar consistency
$stmtT = $pdo->prepare("SELECT * FROM templates WHERE user_id = ?");
$stmtT->execute([$u_id]);
$templates = $stmtT->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile | Web Workout</title>
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
            <h1>User Profile</h1>
            <div class="workout-card">
                <form action="actions/update_profile.php" method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="input-group" style="width:100%;">
                            <span class="input-label">Weight (kg)</span>
                            <input type="number" name="weight" step="0.1" class="input-field" style="width:100%;" value="<?php echo $user['weight']; ?>" placeholder="75.0">
                        </div>
                        <div class="input-group" style="width:100%;">
                            <span class="input-label">Height (cm)</span>
                            <input type="number" name="height" step="0.1" class="input-field" style="width:100%;" value="<?php echo $user['height']; ?>" placeholder="180.0">
                        </div>
                        <div class="input-group" style="width:100%;">
                            <span class="input-label">Gender</span>
                            <select name="gender" class="input-field" style="width:100%; appearance: none; background: var(--bg-input);">
                                <option value="Male" <?php if($user['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                                <option value="Female" <?php if($user['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                                <option value="Other" <?php if($user['gender'] == 'Other') echo 'selected'; ?>>Other</option>
                            </select>
                        </div>
                        <div class="input-group" style="width:100%;">
                            <span class="input-label">Birthdate</span>
                            <input type="date" name="birthdate" class="input-field" style="width:100%;" value="<?php echo $user['birthdate']; ?>">
                        </div>
                    </div>
                    <button type="submit" class="save-button" style="margin-top: 30px;">UPDATE PROFILE</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>