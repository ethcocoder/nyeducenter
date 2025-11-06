// static/js/lab.js

document.addEventListener('DOMContentLoaded', () => {
    const labForm = document.getElementById('lab-form');
    const userInput = document.getElementById('user-input');
    const checkBtn = document.getElementById('check-btn');
    const feedbackEl = document.getElementById('feedback');

    labForm.addEventListener('submit', async (event) => {
        event.preventDefault(); // Stop the form from submitting normally

        // Disable button to prevent multiple submissions
        checkBtn.disabled = true;
        checkBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Checking...
        `;
        userInput.classList.remove('is-invalid');

        try {
            const response = await fetch('/api/lab/check_step', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    step_id: stepId,
                    user_input: userInput.value,
                }),
            });

            if (!response.ok) throw new Error('Network response was not ok.');
            
            // static/js/lab.js

// ... (inside the try block of the event listener)
            const result = await response.json();

            // --- ADD THIS DEBUGGING LINE ---
            console.log("API Response:", result);

            if (result.correct) {
                // On success, redirect to the next step
                window.location.href = result.next_url;
            } else {
                // ... (rest of the code)
                // On failure, show an error message
                userInput.classList.add('is-invalid');
                feedbackEl.textContent = result.message || 'Incorrect. Please try again.';
                // Re-enable the button so the user can try again
                checkBtn.disabled = false;
                checkBtn.innerHTML = 'Check Answer';
            }
        } catch (error) {
            console.error('Error checking step:', error);
            alert('An error occurred. Please try again.');
            checkBtn.disabled = false;
            checkBtn.innerHTML = 'Check Answer';
        }
    });
});