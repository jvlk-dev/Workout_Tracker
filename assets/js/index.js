document.addEventListener('DOMContentLoaded', function() {
    
// ==========================================
    // 1. REFINED MANUAL REST TIMER
    // ==========================================
    const widget = document.getElementById('timer-widget');
    const timerText = document.getElementById('timer-text');
    const tInput = document.getElementById('t-input');
    let restTimerInt = null;
    let restTimeLeft = parseInt(tInput.value);

    // Expand/Collapse logic
    if (widget) {
        widget.addEventListener('click', (e) => {
            if (!widget.classList.contains('expanded')) widget.classList.add('expanded');
        });
        document.addEventListener('click', (e) => {
            if (!widget.contains(e.target)) widget.classList.remove('expanded');
        });
    }

    function updateRestDisplay() {
        let m = Math.floor(restTimeLeft / 60);
        let s = restTimeLeft % 60;
        timerText.innerText = `${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
        // Visual cue when finished
        if (restTimeLeft === 0) {
            timerText.style.color = "#ff7b72";
            timerText.classList.add('blink');
        } else {
            timerText.style.color = "var(--text-main)";
            timerText.classList.remove('blink');
        }
    }

    document.getElementById('t-start').addEventListener('click', (e) => {
        e.stopPropagation();
        if (restTimerInt) return; // Already running
        
        restTimerInt = setInterval(() => {
            if(restTimeLeft > 0) { 
                restTimeLeft--; 
                updateRestDisplay(); 
            } else { 
                clearInterval(restTimerInt);
                restTimerInt = null;
            }
        }, 1000);
    });

    document.getElementById('t-pause').addEventListener('click', (e) => { 
        e.stopPropagation(); 
        clearInterval(restTimerInt); 
        restTimerInt = null;
    });

    document.getElementById('t-reset').addEventListener('click', (e) => {
        e.stopPropagation();
        clearInterval(restTimerInt);
        restTimerInt = null;
        restTimeLeft = parseInt(tInput.value);
        updateRestDisplay();
    });

    // Helper to add/subtract time quickly
    window.adjustTimer = function(seconds) {
        restTimeLeft = Math.max(0, restTimeLeft + seconds);
        updateRestDisplay();
    }

    if (tInput) {
        tInput.addEventListener('click', (e) => e.stopPropagation());
        tInput.addEventListener('change', () => { 
            restTimeLeft = parseInt(tInput.value); 
            updateRestDisplay(); 
        });
    }


    // ==========================================
    // 2. WORKOUT SESSION TIMER (Start Workout)
    // ==========================================
    let sessionSeconds = 0;
    let sessionInterval;

    const startBtnArea = document.getElementById('start-btn-area');
    const startBtn = document.getElementById('btn-start-workout');
    const workoutFormContainer = document.getElementById('workout-form-container');
    const liveTimerContainer = document.getElementById('live-timer-container');
    const liveTimerText = document.getElementById('live-timer-text');
    const durationInput = document.getElementById('session_duration_input');
    const workoutDateTimeInput = document.getElementById('workout_datetime');

    if (startBtn) {
        startBtn.addEventListener('click', () => {
            // UI Transitions
            startBtnArea.style.display = 'none';
            workoutFormContainer.style.display = 'block';
            liveTimerContainer.style.display = 'flex';
            
            // Set current date and time in the input (correcting for timezone)
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            workoutDateTimeInput.value = now.toISOString().slice(0, 16);

            // Start Session Clock
            sessionInterval = setInterval(() => {
                sessionSeconds++;
                let m = Math.floor(sessionSeconds / 60);
                let s = sessionSeconds % 60;
                liveTimerText.innerText = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                durationInput.value = sessionSeconds;
            }, 1000);
        });
    }


    // ==========================================
    // 3. INPUT & DIFFICULTY LOGIC
    // ==========================================
    const form = document.getElementById('workout-form');
    const rows = document.querySelectorAll('.set-row');

    rows.forEach(row => {
        const weightInput = row.querySelector('input[name="weight[]"]');
        const repsInput = row.querySelector('input[name="reps[]"]');
        const picker = row.querySelector('.difficulty-picker');
        const radios = row.querySelectorAll('.diff-radio');

        if (!weightInput || !repsInput || !picker) return;

        const checkInputs = () => {
            // Only show difficulty if BOTH weight and reps have a value (0 is allowed)
            if (weightInput.value.length > 0 && repsInput.value.length > 0) {
                picker.style.display = 'flex';
            } else {
                picker.style.display = 'none';
                radios.forEach(r => r.checked = false);
                weightInput.classList.remove('bg-easy', 'bg-moderate', 'bg-hard');
                repsInput.classList.remove('bg-easy', 'bg-moderate', 'bg-hard');
            }
        };

        [weightInput, repsInput].forEach(input => {
            input.addEventListener('input', () => {
                // Remove previous "last workout" indicators when user starts typing
                input.classList.remove('last-easy', 'last-moderate', 'last-hard');
                checkInputs();
            });
        });

        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                const diff = this.value.toLowerCase();
                // Apply background colors to indicate selection
                [weightInput, repsInput].forEach(el => {
                    el.classList.remove('bg-easy', 'bg-moderate', 'bg-hard');
                    el.classList.add('bg-' + diff);
                });
                row.style.background = "none"; // Clear any red error highlights
            });
        });
    });


    // ==========================================
    // 4. FORM SUBMISSION VALIDATION
    // ==========================================
    if (form) {
        form.addEventListener('submit', (e) => {
            let allFilled = true;
            let allDiffChosen = true;

            rows.forEach(row => {
                const weight = row.querySelector('input[name="weight[]"]');
                const reps = row.querySelector('input[name="reps[]"]');
                const radios = row.querySelectorAll('.diff-radio');

                if (!weight) return;

                // 1. Ensure Weight and Reps are not empty
                if (weight.value.length === 0 || reps.value.length === 0) {
                    allFilled = false;
                    weight.style.borderColor = "var(--hard)";
                    reps.style.borderColor = "var(--hard)";
                } else {
                    weight.style.borderColor = "var(--border)";
                    reps.style.borderColor = "var(--border)";
                    
                    // 2. Ensure a Difficulty is picked for this specific row
                    let diffPicked = false;
                    radios.forEach(r => { if(r.checked) diffPicked = true; });
                    
                    if (!diffPicked) {
                        allDiffChosen = false;
                        row.style.background = "rgba(255,0,0,0.1)"; // Highlight missing difficulty
                    }
                }
            });

            if (!allFilled) {
                alert("Please fill in Weight and Reps for ALL sets. Enter 0 if you skipped a set.");
                e.preventDefault();
            } else if (!allDiffChosen) {
                alert("Please select a Difficulty for every set before saving.");
                e.preventDefault();
            } else {
                // Success: Clear the session timer interval before navigating away
                clearInterval(sessionInterval);
            }
        });
    }
});