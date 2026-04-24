<?php
require_once 'config/db.php';

// 1. Fetch all templates for the sidebar
$stmt = $pdo->query("SELECT * FROM templates");
$templates = $stmt->fetchAll();

// 2. Determine which template we are looking at
// If no ID is in the URL, default to the first template found
$current_template_id = $_GET['template_id'] ?? ($templates[0]['id'] ?? null);

// 3. Fetch exercises for the selected template
$exercises = [];
$template_name = "No Template Selected";
if ($current_template_id) {
    $stmt = $pdo->prepare("SELECT * FROM template_exercises WHERE template_id = ?");
    $stmt->execute([$current_template_id]);
    $exercises = $stmt->fetchAll();

    // Get the name of the current template for the title
    foreach($templates as $t) {
        if($t['id'] == $current_template_id) $template_name = $t['name'];
    }
}

// 4. Helper function to get the last weight/reps for placeholders
function getLastSessionSets($pdo, $exercise_name) {
    // This subquery finds the ID of the last session that included this exercise
    $sql = "SELECT weight_val, reps_val, difficulty 
            FROM session_sets 
            WHERE exercise_name = ? 
            AND session_id = (
                SELECT session_id FROM session_sets 
                WHERE exercise_name = ? 
                ORDER BY id DESC LIMIT 1
            )
            ORDER BY id ASC"; // ASC ensures we get Set 1, then Set 2, then Set 3
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$exercise_name, $exercise_name]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC); // Notice: fetchAll instead of fetch
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Web Workout | <?php echo htmlspecialchars($template_name); ?></title>
    <link rel="stylesheet" href="assets/css/index_style.css">
    <script src="https://kit.fontawesome.com/9526c175c2.js" crossorigin="anonymous"></script>
    <script src="assets/js/index.js" defer></script>
</head>
<body>

    <header class="logo-section">
        <p class="logo-text">Web Workout</p>
        <button type="button" id="log-out-button">Log out</button>
    </header>

    <main class="content">
        <!-- SIDE NAV: DYNAMIC TEMPLATES -->
        <nav class="side-nav">
            <?php foreach ($templates as $template): ?>
                <a href="index.php?template_id=<?php echo $template['id']; ?>" 
                   class="side-nav-button <?php echo ($template['id'] == $current_template_id) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($template['name']); ?>
                </a>
            <?php endforeach; ?>
            <hr>
            <button type="button" class="side-nav-button">+ New Template</button>
        </nav>

        <div class="logger-history">
        
            <!-- SECTION 1: CURRENT WORKOUT FORM -->
            <div class="logger current-workout">
                <form id="workout-form" method="POST" action="submit.php">
                    <input type="hidden" name="template_id" value="<?php echo $current_template_id; ?>">
                    
                    <h1 class="workout_type">Log Workout: <?php echo htmlspecialchars($template_name); ?></h2>
                    
                    <div class="form-header">
                        <label for="focus">Focus:</label>
                        <input type="text" name="focus" class="focus" placeholder="e.g. Heavy Push" required>
                        
                        <label for="date">Date:</label>
                        <input type="date" name="workout_date" class="input_logger_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="exercises">
                        <?php 
                        $global_set_index = 0; 
                        foreach ($exercises as $ex): 
                            // 1. Fetch the ARRAY of all sets from the last time you did this exercise
                            $lastSessionSets = getLastSessionSets($pdo, $ex['exercise_name']); 
                        ?>
                            <div class="exercise">
                                <label><?php echo htmlspecialchars($ex['exercise_name']); ?></label>
                                
                                    <?php for ($i = 1; $i <= 3; $i++): 
                                        // 2. Get the specific data for THIS set number (if it exists)
                                        // Set 1 is index 0, Set 2 is index 1, etc.
                                        $setData = $lastSessionSets[$i - 1] ?? null;
                                        $placeholder_weight = "Kg";
                                        $initial_class = "border-default"; // Default

                                        if ($setData) {
                                            $placeholder_weight = "Last: " . $setData['weight_val'] . "kg";
                                            if ($setData['difficulty'] == 'Easy') {
                                                $initial_class = "border-easy";
                                            } elseif ($setData['difficulty'] == 'Hard') {
                                                $initial_class = "border-hard";
                                            }
                                        }
                                    ?>
                                    <div class="set">
                                        <p>Set <?php echo $i; ?></p>
                                        <div class="set_row">
                                            <!-- Added the $initial_class here -->
                                            <input 
                                                type="number" 
                                                name="weight[]" 
                                                class="input_logger_text <?php echo $initial_class; ?>" 
                                                placeholder="<?php echo $placeholder_weight; ?>">
                                            
                                            <input 
                                                type="number" 
                                                name="reps[]" 
                                                class="input_logger_text <?php echo $initial_class; ?>" 
                                                placeholder="<?php echo $setData ? 'Last: '.$setData['reps_val'] . "x" : 'Reps'; ?>">

                                            <div class="difficulty-picker">
                                                <label class="diff-btn">
                                                    <input type="radio" name="difficulty[<?php echo $global_set_index; ?>]" value="Easy" class="diff-radio">
                                                    <span class="btn-label easy">Easy</span>
                                                </label>
                                                <label class="diff-btn">
                                                    <input type="radio" name="difficulty[<?php echo $global_set_index; ?>]" value="Moderate" class="diff-radio">
                                                    <span class="btn-label moderate">Moderate</span>
                                                </label>
                                                <label class="diff-btn">
                                                    <input type="radio" name="difficulty[<?php echo $global_set_index; ?>]" value="Hard" class="diff-radio">
                                                    <span class="btn-label hard">Hard</span>
                                                </label>
                                            </div>
                                            <input type="hidden" name="exercise_name[]" value="<?php echo htmlspecialchars($ex['exercise_name']); ?>">
                                        </div>
                                    </div>
                                    <?php $global_set_index++; ?>
                                <?php endfor; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="save-button">Save Workout</button>
                </form>
            </div>

            <!-- SECTION 2: HISTORY HEADER -->
            <h1 class="history-title">Workout History</h1>

            <!-- SECTION 3: HISTORY ENTRIES -->
            <?php
            $stmt = $pdo->prepare("SELECT * FROM sessions WHERE template_id = ? ORDER BY workout_date DESC");
            $stmt->execute([$current_template_id]);
            $historySessions = $stmt->fetchAll();

            foreach ($historySessions as $session): 
                $setStmt = $pdo->prepare("SELECT * FROM session_sets WHERE session_id = ?");
                $setStmt->execute([$session['id']]);
                $loggedSets = $setStmt->fetchAll();
                
                $groupedSets = [];
                foreach ($loggedSets as $set) {
                    $groupedSets[$set['exercise_name']][] = $set;
                }
            ?>
                <div class="logger history-entry">
                    <h2 class="history_entry_date"><?php echo $session['workout_date']; ?></h3>

                    <div class="exercises">
                        <?php foreach ($groupedSets as $exName => $sets): ?>
                            <div class="exercise">
                                <label class="history_entry_exercise_name"><?php echo htmlspecialchars($exName); ?></label>
                                    <?php foreach ($sets as $index => $set): ?>
                                        <div class="set">
                                            <p>Set <?php echo $index + 1; ?></p>
                                            <div class="set_row">
                                                <input type="text" class="input_logger_text" value="<?php echo $set['weight_val']; ?>kg" readonly>
                                                <input type="text" class="input_logger_text" value="<?php echo $set['reps_val']; ?>x" readonly>
                                                
                                                <!-- Visual Indicator for Difficulty -->
                                                <?php 
                                                    $color = "#888"; // Default
                                                    if($set['difficulty'] == 'Easy') $color = "#28a745"; // Green
                                                    if($set['difficulty'] == 'Moderate') $color = "#ffc107"; // Yellow
                                                    if($set['difficulty'] == 'Hard') $color = "#dc3545"; // Red
                                                ?>
                                                <span class="history_set_difficulty" style="color: <?php echo $color; ?>; font-weight: bold;">
                                                    <?php echo strtoupper($set['difficulty']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </main>
</body>
</html>