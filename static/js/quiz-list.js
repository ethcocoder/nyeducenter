// static/js/quiz-list.js

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('quiz-list-container');
    const countdownTimers = new Map(); // To manage active timers

    function createQuizCard(quiz) {
        let buttonHtml = '';
        switch (quiz.status) {
            case 'completed':
                buttonHtml = `<button class="btn btn-success w-100 disabled">Completed</button>`;
                break;
            case 'locked':
                // The button's text will be updated by the timer
                buttonHtml = `<button id="quiz-btn-${quiz.id}" class="btn btn-secondary w-100 disabled" data-unlock-time="${quiz.unlock_time}">Locked</button>`;
                break;
            default: // 'available'
                buttonHtml = `<a href="/quiz/${quiz.id}" class="btn btn-primary w-100">Start Quiz</a>`;
        }

        return `
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">${quiz.title}</h5>
                        <p class="card-text">
                            This quiz contains <strong>${quiz.question_count} questions</strong>.
                            A score of ${quiz.passing_score} is required to pass.
                        </p>
                    </div>
                    <div class="card-footer">
                        ${buttonHtml}
                    </div>
                </div>
            </div>
        `;
    }

    function updateCountdown() {
        countdownTimers.forEach((data, btnId) => {
            const btn = document.getElementById(btnId);
            if (!btn) {
                clearInterval(data.interval);
                countdownTimers.delete(btnId);
                return;
            }

            const now = new Date().getTime();
            const distance = data.unlockTime - now;

            if (distance < 0) {
                clearInterval(data.interval);
                btn.outerHTML = `<a href="/quiz/${data.quizId}" class="btn btn-warning w-100">Retry Quiz</a>`;
                countdownTimers.delete(btnId);
            } else {
                const minutes = Math.floor(distance / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                btn.innerText = `Locked (${minutes}:${seconds.toString().padStart(2, '0')})`;
            }
        });
    }

    async function fetchAndRenderQuizzes() {
        try {
            const response = await fetch('/api/quizzes');
            if (!response.ok) throw new Error('Network response was not ok');
            const quizzes = await response.json();

            container.innerHTML = ''; // Clear the loading spinner
            if (quizzes.length === 0) {
                container.innerHTML = `<div class="col-12"><div class="alert alert-info">There are currently no quizzes available.</div></div>`;
                return;
            }

            quizzes.forEach(quiz => {
                container.innerHTML += createQuizCard(quiz);
                if (quiz.status === 'locked') {
                    const btnId = `quiz-btn-${quiz.id}`;
                    countdownTimers.set(btnId, {
                        unlockTime: new Date(quiz.unlock_time).getTime(),
                        interval: setInterval(updateCountdown, 1000),
                        quizId: quiz.id
                    });
                }
            });
            updateCountdown(); // Initial call to set text immediately
        } catch (error) {
            console.error('Failed to fetch quizzes:', error);
            container.innerHTML = `<div class="col-12"><div class="alert alert-danger">Could not load quizzes. Please try again later.</div></div>`;
        }
    }

    // Initial load
    fetchAndRenderQuizzes();
});