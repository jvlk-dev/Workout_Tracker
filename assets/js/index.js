document.addEventListener('DOMContentLoaded', function() {
    const setRows = document.querySelectorAll('.set_row');

    setRows.forEach(row => {
        const weightInput = row.querySelector('input[name="weight[]"]');
        const repsInput = row.querySelector('input[name="reps[]"]');
        const diffPicker = row.querySelector('.difficulty-picker');
        const radios = row.querySelectorAll('.diff-radio');

        // Function to check if we should show the difficulty picker
        function togglePicker() {
            if (weightInput.value.trim() !== "" && repsInput.value.trim() !== "") {
                diffPicker.classList.add('visible');
            } else {
                diffPicker.classList.remove('visible');
                // Optional: Uncheck radios if inputs are cleared
                radios.forEach(r => r.checked = false);
            }
        }

        // Event listener for typing in inputs
        [weightInput, repsInput].forEach(input => {
            input.addEventListener('input', function() {
                // 1. Reset border to default when user starts typing/changing
                this.classList.remove('border-easy', 'border-moderate', 'border-hard');
                this.classList.add('border-default');

                // 2. Check if we should show the buttons
                togglePicker();
            });
        });

        // Event listener for clicking the difficulty buttons
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                const difficulty = this.value.toLowerCase();
                
                [weightInput, repsInput].forEach(input => {
                    input.classList.remove('border-default', 'border-easy', 'border-moderate', 'border-hard');
                    input.classList.add('border-' + difficulty);
                });
            });
        });
    });
});