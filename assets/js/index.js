document.addEventListener('DOMContentLoaded', function() {
    
    // --- TIMER ENGINE ---
    const widget = document.getElementById('timer-widget');
    const timerText = document.getElementById('timer-text');
    const tInput = document.getElementById('t-input');
    let timerInt;
    let timeLeft = 90;

    widget.addEventListener('click', (e) => {
        if (!widget.classList.contains('expanded')) widget.classList.add('expanded');
    });

    document.addEventListener('click', (e) => {
        if (!widget.contains(e.target)) widget.classList.remove('expanded');
    });

    function updateT() {
        let m = Math.floor(timeLeft / 60);
        let s = timeLeft % 60;
        timerText.innerText = `${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
    }

    document.getElementById('t-start').addEventListener('click', (e) => {
        e.stopPropagation();
        clearInterval(timerInt);
        timerInt = setInterval(() => {
            if(timeLeft > 0) { timeLeft--; updateT(); }
            else { clearInterval(timerInt); timerText.style.color = "#ff7b72"; }
        }, 1000);
    });

    document.getElementById('t-pause').addEventListener('click', (e) => { e.stopPropagation(); clearInterval(timerInt); });
    document.getElementById('t-reset').addEventListener('click', (e) => {
        e.stopPropagation();
        clearInterval(timerInt);
        timeLeft = parseInt(tInput.value);
        timerText.style.color = "var(--text-main)";
        updateT();
    });

    tInput.addEventListener('click', (e) => e.stopPropagation());
    tInput.addEventListener('change', () => { timeLeft = tInput.value; updateT(); });


    // --- INPUT & DIFFICULTY LOGIC ---
    const form = document.getElementById('workout-form');
    const rows = document.querySelectorAll('.set-row');

    rows.forEach(row => {
        const weight = row.querySelector('input[name="weight[]"]');
        const reps = row.querySelector('input[name="reps[]"]');
        const picker = row.querySelector('.difficulty-picker');
        if (!picker) return;

        const radios = row.querySelectorAll('.diff-radio');

        [weight, reps].forEach(input => {
            input.addEventListener('input', () => {
                // Remove indicators when typing
                input.classList.remove('last-easy', 'last-moderate', 'last-hard', 'bg-easy', 'bg-moderate', 'bg-hard');
                
                if (weight.value.trim() !== "" && reps.value.trim() !== "") {
                    picker.style.display = 'flex';
                } else {
                    picker.style.display = 'none';
                    radios.forEach(r => r.checked = false);
                }
            });
        });

        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                const diff = this.value.toLowerCase();
                [weight, reps].forEach(el => {
                    el.classList.remove('bg-easy', 'bg-moderate', 'bg-hard');
                    el.classList.add('bg-' + diff);
                });
                row.style.background = "none"; // Clear error background if selected
            });
        });
    });

    // --- FORM SUBMISSION VALIDATION ---
    form.addEventListener('submit', (e) => {
        let valid = true;
        let filledRows = 0;

        rows.forEach(row => {
            const weight = row.querySelector('input[name="weight[]"]');
            const reps = row.querySelector('input[name="reps[]"]');
            const radios = row.querySelectorAll('.diff-radio');

            if (!weight) return;

            // Check if weight/reps are filled
            const hasData = weight.value.trim() !== "" && reps.value.trim() !== "";
            
            if (hasData) {
                filledRows++;
                let diffPicked = false;
                radios.forEach(r => { if(r.checked) diffPicked = true; });

                if (!diffPicked) {
                    valid = false;
                    row.style.background = "rgba(255,0,0,0.1)"; // Highlight row red
                }
            }
        });

        if (filledRows === 0) {
            alert("Please complete at least one set before saving!");
            e.preventDefault();
        } else if (!valid) {
            alert("Please select a difficulty for every set you performed.");
            e.preventDefault();
        }
    });
});