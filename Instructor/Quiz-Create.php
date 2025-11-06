<?php
require_once '../includes/header.php';
requireRole(['admin', 'instructor']);

// Get course and chapter data
$course_id = $_GET['course_id'] ?? null;
$chapter_id = $_GET['chapter_id'] ?? null;
$topic_id = $_GET['topic_id'] ?? null;

if (!$course_id) {
    setFlashMessage('Course ID is required', 'danger');
    redirect('Courses.php');
}

// Get course details
$course = $course->getCourseById($course_id);
if (!$course) {
    setFlashMessage('Course not found', 'danger');
    redirect('Courses.php');
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="Courses.php">
                            <i class="fas fa-book"></i> All Courses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Courses-View.php?course_id=<?php echo $course_id; ?>">
                            <i class="fas fa-arrow-left"></i> Back to Course
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Create New Quiz</h1>
            </div>

            <form id="createQuizForm" class="needs-validation" novalidate>
                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                <?php if ($chapter_id): ?>
                <input type="hidden" name="chapter_id" value="<?php echo $chapter_id; ?>">
                <?php endif; ?>
                <?php if ($topic_id): ?>
                <input type="hidden" name="topic_id" value="<?php echo $topic_id; ?>">
                <?php endif; ?>

                <!-- Quiz Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Quiz Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- English -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title_en" class="form-label">Title (English)</label>
                                    <input type="text" class="form-control" id="title_en" name="title_en" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description_en" class="form-label">Description (English)</label>
                                    <textarea class="form-control" id="description_en" name="description_en" rows="3"></textarea>
                                </div>
                            </div>
                            <!-- Amharic -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title_am" class="form-label">Title (Amharic)</label>
                                    <input type="text" class="form-control" id="title_am" name="title_am" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description_am" class="form-label">Description (Amharic)</label>
                                    <textarea class="form-control" id="description_am" name="description_am" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="passing_score" class="form-label">Passing Score (%)</label>
                                    <input type="number" class="form-control" id="passing_score" name="passing_score" min="0" max="100" value="70">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="time_limit" class="form-label">Time Limit (minutes)</label>
                                    <input type="number" class="form-control" id="time_limit" name="time_limit" min="1">
                                    <div class="form-text">Leave empty for no time limit</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Public">Public</option>
                                <option value="Private">Private</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Questions -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Questions</h5>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addQuestion()">
                            <i class="fas fa-plus"></i> Add Question
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="questionsContainer">
                            <!-- Questions will be added here dynamically -->
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Quiz</button>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
let questionCount = 0;

function addQuestion() {
    const container = document.getElementById('questionsContainer');
    const questionDiv = document.createElement('div');
    questionDiv.className = 'question-card card mb-3';
    questionDiv.innerHTML = `
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Question ${questionCount + 1}</h6>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestion(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Question Text (English)</label>
                        <textarea class="form-control" name="questions[${questionCount}][question_text_en]" required></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Question Text (Amharic)</label>
                        <textarea class="form-control" name="questions[${questionCount}][question_text_am]" required></textarea>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Question Type</label>
                    <select class="form-select" name="questions[${questionCount}][question_type]" onchange="toggleOptions(this)">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                        <option value="short_answer">Short Answer</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Points</label>
                    <input type="number" class="form-control" name="questions[${questionCount}][points]" value="1" min="1">
                </div>
            </div>

            <div class="options-container">
                <div class="mb-3">
                    <label class="form-label">Options</label>
                    <div class="options-list">
                        <div class="option-item mb-2">
                            <div class="input-group">
                                <input type="text" class="form-control" name="questions[${questionCount}][options][0][option_text_en]" placeholder="Option (English)" required>
                                <input type="text" class="form-control" name="questions[${questionCount}][options][0][option_text_am]" placeholder="Option (Amharic)" required>
                                <div class="input-group-text">
                                    <input type="radio" name="questions[${questionCount}][correct_option]" value="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="option-item mb-2">
                            <div class="input-group">
                                <input type="text" class="form-control" name="questions[${questionCount}][options][1][option_text_en]" placeholder="Option (English)" required>
                                <input type="text" class="form-control" name="questions[${questionCount}][options][1][option_text_am]" placeholder="Option (Amharic)" required>
                                <div class="input-group-text">
                                    <input type="radio" name="questions[${questionCount}][correct_option]" value="1" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addOption(this)">
                        <i class="fas fa-plus"></i> Add Option
                    </button>
                </div>
            </div>
        </div>
    `;
    container.appendChild(questionDiv);
    questionCount++;
}

function removeQuestion(button) {
    button.closest('.question-card').remove();
}

function addOption(button) {
    const optionsList = button.previousElementSibling;
    const questionIndex = button.closest('.question-card').querySelector('select').name.match(/\[(\d+)\]/)[1];
    const optionCount = optionsList.children.length;
    
    const optionDiv = document.createElement('div');
    optionDiv.className = 'option-item mb-2';
    optionDiv.innerHTML = `
        <div class="input-group">
            <input type="text" class="form-control" name="questions[${questionIndex}][options][${optionCount}][option_text_en]" placeholder="Option (English)" required>
            <input type="text" class="form-control" name="questions[${questionIndex}][options][${optionCount}][option_text_am]" placeholder="Option (Amharic)" required>
            <div class="input-group-text">
                <input type="radio" name="questions[${questionIndex}][correct_option]" value="${optionCount}" required>
            </div>
            <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    optionsList.appendChild(optionDiv);
}

function removeOption(button) {
    button.closest('.option-item').remove();
}

function toggleOptions(select) {
    const optionsContainer = select.closest('.card-body').querySelector('.options-container');
    if (select.value === 'short_answer') {
        optionsContainer.style.display = 'none';
    } else {
        optionsContainer.style.display = 'block';
    }
}

document.getElementById('createQuizForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    try {
        const formData = new FormData(e.target);
        const response = await fetch('Action/quiz-create.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            setFlashMessage('Quiz created successfully', 'success');
            window.location.href = `Courses-View.php?course_id=${formData.get('course_id')}`;
        } else {
            setFlashMessage(result.message || 'Failed to create quiz', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        setFlashMessage('An error occurred while creating the quiz', 'danger');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?> 