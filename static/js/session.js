// static/js/session.js

document.addEventListener('DOMContentLoaded', () => {
    const checkBtn = document.getElementById('check-code-btn');
    const editor = document.getElementById('user-code-editor');
    const checklist = document.getElementById('requirements-checklist');
    const statusIcons = checklist.querySelectorAll('.status-icon');

    checkBtn.addEventListener('click', async () => {
        const userCode = editor.value;

        // Provide visual feedback while checking
        checkBtn.disabled = true;
        checkBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Checking...
        `;

        try {
            const response = await fetch('/api/session/validate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: sessionId, // This variable comes from the inline script in the HTML
                    user_code: userCode,
                }),
            });
            
            if (!response.ok) {
                throw new Error(`Network response was not ok. Status: ${response.status}`);
            }

            const data = await response.json();
            
            // Update the UI based on the results from the API
            updateChecklist(data.results, data.is_complete);

        } catch (error) {
            console.error('Validation Error:', error);
            alert('An error occurred while checking your code. Please try again.');
        } finally {
            // Re-enable the button
            checkBtn.disabled = false;
            checkBtn.innerHTML = 'Check My Code';
        }
    });

    function updateChecklist(results, is_complete) {
        const statusIcons = checklist.querySelectorAll('.status-icon'); // Query inside to get latest
        results.forEach((isCorrect, index) => {
            const icon = statusIcons[index];
            if (icon) {
                // Remove all old status classes
                icon.classList.remove('bi-circle', 'bi-check-circle-fill', 'bi-x-circle-fill');
                
                if (isCorrect) {
                    icon.classList.add('bi-check-circle-fill');
                    icon.style.color = 'green';
                } else {
                    icon.classList.add('bi-x-circle-fill');
                    icon.style.color = 'red';
                }
            }
        });

        if (is_complete) {
            // Trigger confetti
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 }
            });
            // Redirect to the completion page after a short delay
            setTimeout(() => {
                window.location.href = `/session/${sessionId}/complete`;
            }, 2000); // 2 second delay
        }
    }
});