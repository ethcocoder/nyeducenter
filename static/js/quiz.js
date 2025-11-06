// static/js/quiz.js

document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const questionArea = document.getElementById('question-area');
    const submitBtn = document.getElementById('submit-btn');
    const progressBar = document.getElementById('progress-bar');
    const resultsArea = document.getElementById('results-area');
    const resultSummary = document.getElementById('result-summary');
    const resultFeedback = document.getElementById('result-feedback');
    const reviewBtn = document.getElementById('review-btn');
    const reviewArea = document.getElementById('review-area');
    const reviewContainer = document.getElementById('review-container');

    // State
    let score = 0;
    const answeredQuestions = new Set(); // Use a Set to track answered questions
    let userAnswers = {}; // Use an object to store {questionId: optionId}

    // API helper function (no changes)
    async function apiCall(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data),
        });
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    }

    function updateProgress() {
        const answeredCount = answeredQuestions.size;
        const progressPercentage = (answeredCount / totalQuestions) * 100;
        
        progressBar.style.width = `${progressPercentage}%`;
        progressBar.setAttribute('aria-valuenow', progressPercentage);
        progressBar.innerText = `${answeredCount} / ${totalQuestions}`;

        // Enable the submit button only when all questions have been answered
        submitBtn.disabled = answeredCount !== totalQuestions;
    }

    // Use event delegation to handle clicks on any option button
    questionArea.addEventListener('click', async (event) => {
        // Only act on clicks on the option buttons
        if (!event.target.matches('.options-container .btn')) return;

        const selectedButton = event.target;
        const questionCard = selectedButton.closest('.question-card');
        const questionId = parseInt(questionCard.dataset.questionId);
        
        // If this question has already been answered, do nothing
        if (answeredQuestions.has(questionId)) return;

        const optionId = parseInt(selectedButton.dataset.optionId);
        
        // Store the user's answer for the review
        userAnswers[questionId] = optionId;

        try {
            const result = await apiCall('/api/quiz/check_answer', { question_id: questionId, option_id: optionId });
            
            // Mark the question as answered
            answeredQuestions.add(questionId);

            if (result.correct) {
                score++;
                selectedButton.classList.replace('btn-outline-primary', 'btn-success');
            } else {
                selectedButton.classList.replace('btn-outline-primary', 'btn-danger');
                // Highlight the correct answer in green
                const correctButton = questionCard.querySelector(`[data-option-id='${result.correct_option_id}']`);
                if (correctButton) {
                    correctButton.classList.replace('btn-outline-primary', 'btn-success');
                }
            }
            
            // Disable all buttons within this specific question card
            const allButtonsInCard = questionCard.querySelectorAll('.options-container .btn');
            allButtonsInCard.forEach(btn => btn.disabled = true);
            
            updateProgress();

        } catch (error) {
            console.error('Error checking answer:', error);
            alert('Could not verify answer. Please refresh and try again.');
        }
    });

    submitBtn.addEventListener('click', async () => {
        questionArea.style.display = 'none';
        submitBtn.style.display = 'none';
        resultsArea.style.display = 'block';

        progressBar.classList.add('bg-success');
        progressBar.innerText = `Complete!`;

        resultSummary.innerText = `You scored ${score} out of ${totalQuestions}.`;
        
        try {
            const result = await apiCall('/api/quiz/submit_result', { quiz_id: quizId, score: score });
            
            if (result.passed) {
                resultFeedback.innerText = "Congratulations, you passed!";
                resultFeedback.classList.add('alert-success');
            } else {
                resultFeedback.innerText = "You did not pass this time. Please review your answers or try again in 1 minute.";
                resultFeedback.classList.add('alert-warning');
            }
        } catch (error) {
            console.error('Error submitting results:', error);
            resultFeedback.innerText = "There was an error saving your score.";
            resultFeedback.classList.add('alert-danger');
        }
    });

    reviewBtn.addEventListener('click', () => {
        // This function works similarly, but we need to adapt it slightly
        resultsArea.style.display = 'none';
        reviewArea.style.display = 'block';
        reviewContainer.innerHTML = '';

        questionsWithAnswersData.forEach((question) => {
            const questionCard = document.createElement('div');
            questionCard.classList.add('card', 'mb-3');

            let optionsHtml = '<ul class="list-group list-group-flush">';
            question.options.forEach(option => {
                let itemClass = '';
                const userSelectedThis = (userAnswers[question.id] === option.id);

                if (option.is_correct) {
                    itemClass = 'list-group-item-success';
                } else if (userSelectedThis && !option.is_correct) {
                    itemClass = 'list-group-item-danger';
                }
                
                optionsHtml += `<li class="list-group-item ${itemClass}">${option.text}</li>`;
            });
            optionsHtml += '</ul>';

            questionCard.innerHTML = `
                <div class="card-header">
                    ${question.text}
                </div>
                ${optionsHtml}
            `;
            reviewContainer.appendChild(questionCard);
        });
    });

    // Initial call to set the progress bar text correctly
    updateProgress();
});