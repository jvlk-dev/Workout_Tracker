<?php
require_once 'config/db.php';

$templates = $pdo->query("SELECT * FROM templates")->fetchAll();
$current_template_id = $_GET['template_id'] ?? ($templates[0]['id'] ?? null);

$exercises = [];
$template_name = "Selection Required";
if ($current_template_id) {
    $stmt = $pdo->prepare("SELECT * FROM template_exercises WHERE template_id = ?");
    $stmt->execute([$current_template_id]);
    $exercises = $stmt->fetchAll();
    foreach($templates as $t) { if($t['id'] == $current_template_id) $template_name = $t['name']; }
}

function getLastSessionSets($pdo, $exercise_name) {
    $sql = "SELECT weight_val, reps_val, difficulty FROM session_sets 
            WHERE exercise_name = ? 
            AND session_id = (SELECT session_id FROM session_sets WHERE exercise_name = ? ORDER BY id DESC LIMIT 1)
            ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$exercise_name, $exercise_name]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tracker | <?php echo htmlspecialchars($template_name); ?></title>
    <link rel="stylesheet" href="assets/css/index_style.css">
    <script src="https://kit.fontawesome.com/9526c175c2.js" crossorigin="anonymous"></script>
    <script src="assets/js/index.js" defer></script>
</head>
<body>

    <div class="content">
        <nav class="side-nav">
            <div class="nav-title">Web Workout</div>
            <hr class="nav-sep">
            
            <a href="index.php" class="side-nav-button overview-btn">
                <i class="fa-solid fa-chart-line"></i> Overview
            </a>
            
            <hr class="nav-sep">
            
            <div style="flex:1; overflow-y: auto;">
                <?php foreach ($templates as $template): ?>
                    <a href="tracker.php?template_id=<?php echo $template['id']; ?>" 
                       class="side-nav-button <?php echo ($template['id'] == $current_template_id) ? 'active' : ''; ?>">
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
    
            <!-- 1. START WORKOUT BUTTON -->
            <div class="start-session-container" id="start-btn-area">
                <button type="button" class="start-button" id="btn-start-workout">
                    <i class="fa-solid fa-play"></i> Start New Workout
                </button>
            </div>

            <!-- 2. FULL WIDTH LIVE TIMER (Above Form) -->
            <div id="live-timer-container" class="session-timer-bar">
                <span class="timer-label">Session Time</span>
                <div class="timer-digits" id="live-timer-text">00:00</div>
                <i class="fa-solid fa-stopwatch fa-spin-pulse" style="color:var(--accent); font-size: 1.5rem;"></i>
            </div>

            <!-- 3. THE WORKOUT FORM -->
            <div class="workout-card" id="workout-form-container" style="display:none;">
                <form id="workout-form" method="POST" action="actions/submit.php">
                    <input type="hidden" name="template_id" value="<?php echo $current_template_id; ?>">
                    <input type="hidden" name="session_duration" id="session_duration_input" value="0">
                    
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2.5rem;">
                        <h1 style="margin:0;"><?php echo htmlspecialchars($template_name); ?></h1>
                        <input type="datetime-local" name="workout_date" id="workout_datetime" class="input-field" value="<?php echo date('Y-m-d\TH:i'); ?>" style="width:220px;">
                    </div>

                    <?php $global_idx = 0; foreach ($exercises as $ex): $last = getLastSessionSets($pdo, $ex['exercise_name']); ?>
                        <div class="exercise-block">
                            <div class="exercise-title"><?php echo htmlspecialchars($ex['exercise_name']); ?></div>
                            <?php 
                            // CHANGE: Using dynamic set count from database instead of hardcoded 3
                            $setCount = $ex['default_sets'] ?? 3; 
                            for ($i = 1; $i <= $setCount; $i++): 
                                $sData = $last[$i-1] ?? null;
                                $lastDiff = $sData ? 'last-'.strtolower($sData['difficulty']) : '';
                            ?>
                                <div class="set-row">
                                    <div class="set-label">Set <?php echo $i; ?></div>
                                    <div class="input-group">
                                        <span class="input-label">Weight</span>
                                        <input type="number" name="weight[]" step="any" class="input-field <?php echo $lastDiff; ?>" placeholder="<?php echo $sData ? $sData['weight_val'].'kg' : '--'; ?>">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-label">Reps</span>
                                        <input type="number" name="reps[]" class="input-field <?php echo $lastDiff; ?>" placeholder="<?php echo $sData ? $sData['reps_val'] : '--'; ?>">
                                    </div>
                                    <div class="difficulty-picker">
                                        <?php foreach(['Easy', 'Moderate', 'Hard'] as $lvl): ?>
                                            <label><input type="radio" name="difficulty[<?php echo $global_idx; ?>]" value="<?php echo $lvl; ?>" style="display:none;" class="diff-radio"><span class="diff-btn <?php echo strtolower($lvl); ?>"><?php echo $lvl; ?></span></label>
                                        <?php endforeach; ?>
                                    </div>
                                    <input type="hidden" name="exercise_name[]" value="<?php echo htmlspecialchars($ex['exercise_name']); ?>">
                                </div>
                            <?php $global_idx++; endfor; ?>
                        </div>
                    <?php endforeach; ?>

                    <textarea name="notes" class="notes-area" rows="2" placeholder="Session notes (optional)..."></textarea>
                    <button type="submit" class="save-button">SAVE WORKOUT</button>
                </form>
            </div>

            <!-- 4. HISTORY SECTION -->
            <h2 style="color:var(--text-dim); margin-bottom:1.5rem;">Template History</h2>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM sessions WHERE template_id = ? ORDER BY workout_date DESC, id DESC LIMIT 10");
            $stmt->execute([$current_template_id]);
            while ($sess = $stmt->fetch()):
                $setStmt = $pdo->prepare("SELECT * FROM session_sets WHERE session_id = ?");
                $setStmt->execute([$sess['id']]);
                $logged = $setStmt->fetchAll();
                if(empty($logged)) continue;
                $grouped = []; foreach ($logged as $s) { $grouped[$s['exercise_name']][] = $s; }

                // Formatting Duration for History
                $mins = floor($sess['duration'] / 60);
                $secs = $sess['duration'] % 60;
                $time_formatted = sprintf("%dm %02ds", $mins, $secs);
            ?>
                <div class="workout-card history-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem; border-bottom: 1px solid #222; padding-bottom: 1rem;">
                        <span class="history-date" style="margin:0;">
                            <i class="fa-regular fa-calendar-check" style="margin-right:10px; color: var(--accent);"></i>
                            <?php echo date("F j, Y — H:i", strtotime($sess['workout_date'])); ?>
                        </span>
                        <span class="history-duration">
                            <i class="fa-solid fa-stopwatch" style="margin-right:5px;"></i> <?php echo $time_formatted; ?>
                        </span>
                    </div>
                    
                    <?php foreach ($grouped as $name => $sets): ?>
                        <div class="exercise-block" style="border-color:#222; margin-bottom:1rem;">
                            <div class="exercise-title" style="font-size:1.1rem;"><?php echo htmlspecialchars($name); ?></div>
                            <?php foreach ($sets as $idx => $s): ?>
                                <div class="set-row">
                                    <div class="set-label">Set <?php echo $idx+1; ?></div>
                                    <div class="input-group"><input type="text" class="input-field bg-<?php echo strtolower($s['difficulty']); ?>" value="<?php echo $s['weight_val']; ?>kg" readonly></div>
                                    <div class="input-group"><input type="text" class="input-field bg-<?php echo strtolower($s['difficulty']); ?>" value="<?php echo $s['reps_val']; ?>x" readonly></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if(!empty($sess['notes'])): ?><div style="font-size:0.9rem; color:var(--text-dim); font-style:italic; padding:10px; background:#000; border-radius:5px;">Note: <?php echo htmlspecialchars($sess['notes']); ?></div><?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- TIMER WIDGET -->
    <div id="timer-widget">
        <i class="fa-solid fa-stopwatch timer-icon" style="font-size:2rem; color:var(--accent);"></i>
        <div class="timer-content">
            <div class="timer-display" id="timer-text">01:30</div>
            
            <div class="timer-controls">
                <button id="t-start" class="t-ctrl-btn"><i class="fa-solid fa-play"></i></button>
                <button id="t-pause" class="t-ctrl-btn"><i class="fa-solid fa-pause"></i></button>
                <button id="t-reset" class="t-ctrl-btn"><i class="fa-solid fa-rotate-right"></i></button>
            </div>

            <div class="t-input-container">
                <div style="display:flex; gap:5px; margin-bottom:5px;">
                    <button class="t-adjust-btn" onclick="event.stopPropagation(); adjustTimer(-15)">-15s</button>
                    <button class="t-adjust-btn" onclick="event.stopPropagation(); adjustTimer(15)">+15s</button>
                </div>
                <input type="number" id="t-input" value="90">
            </div>
        </div>
    </div>
</body>
</html>