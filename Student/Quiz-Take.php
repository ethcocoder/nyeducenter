<?php
require_once '../includes/header.php';
requireRole(['student']);

$quiz_id = $_GET['quiz_id'] ?? null;
if (!$quiz_id) {
    setFlashMessage('Quiz ID is required', 'danger');
    redirect('Courses.php');
}

// Get quiz details
$sql = "SELECT q.*, c.title_en as course_title 
        FROM quiz q 
        JOIN course c ON q.course_id = c.course_id 
        WHERE q.quiz_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    setFlashMessage('Quiz not found', 'danger');
    redirect('Courses.php');
}

// Check if student has already started an attempt
$sql = "SELECT * FROM quiz_attempt 
        WHERE quiz_id = ? AND student_id = ? AND status = 'in_progress'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$quiz_id, $_SESSION['user_id']]);
$attempt = $stmt->fetch();

if (!$attempt) {
    // Create new attempt
    $sql = "INSERT INTO quiz_attempt (quiz_id, student_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$quiz_id, $_SESSION['user_id']]);
    $attempt_id = $pdo->lastInsertId();
} else {
    $attempt_id = $attempt['attempt_id'];
}

// Get questions
$sql = "SELECT * FROM question WHERE quiz_id = ? ORDER BY `order`";
$stmt = $pdo->prepare($sql);
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

// Get student's answers
$sql = "SELECT * FROM quiz_answer WHERE attempt_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$attempt_id]);
$answers = $stmt->fetchAll();
$student_answers = [];
foreach ($answers as $answer) {
    $student_answers[$answer['question_id']] = $answer;
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
                        <a class="nav-link" href="Enrolled-Course.php?course_id=<?php echo $quiz['course_id']; ?>">
                            <i class="fas fa-arrow-left"></i> Back to Course
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $quiz['title_en']; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="saveProgress">
                            <i class="fas fa-save"></i> Save Progress
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="submitQuiz">
                            <i class="fas fa-check"></i> Submit Quiz
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Quiz content -->
                <div class="col-md-8">
                    <form id="quizForm">
                        <input type="hidden" name="attempt_id" value="<?php echo $attempt_id; ?>">
                        
                        <?php foreach ($questions as $index => $question): ?>
                        <div class="card mb-4 question-card" data-question-id="<?php echo $question['question_id']; ?>">
                            <div class="card-header">
                                <h5 class="mb-0">Question <?php echo $index + 1; ?></h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo $question['question_text_en']; ?></p>
                                
                                <?php if ($question['question_type'] === 'multiple_choice'): ?>
                                    <?php
                                    $sql = "SELECT * FROM question_option WHERE question_id = ? ORDER BY `order`";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute([$question['question_id']]);
                                    $options = $stmt->fetchAll();
                                    ?>
                                    <div class="options">
                                        <?php foreach ($options as $option): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" 
                                                   name="answers[<?php echo $question['question_id']; ?>][selected_option_id]" 
                                                   value="<?php echo $option['option_id']; ?>"
                                                   <?php echo isset($student_answers[$question['question_id']]) && 
                                                         $student_answers[$question['question_id']]['selected_option_id'] == $option['option_id'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <?php echo $option['option_text_en']; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                
                                <?php elseif ($question['question_type'] === 'true_false'): ?>
                                    <div class="options">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" 
                                                   name="answers[<?php echo $question['question_id']; ?>][selected_option_id]" 
                                                   value="true"
                                                   <?php echo isset($student_answers[$question['question_id']]) && 
                                                         $student_answers[$question['question_id']]['selected_option_id'] == 'true' ? 'checked' : ''; ?>>
                                            <label class="form-check-label">True</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" 
                                                   name="answers[<?php echo $question['question_id']; ?>][selected_option_id]" 
                                                   value="false"
                                                   <?php echo isset($student_answers[$question['question_id']]) && 
                                                         $student_answers[$question['question_id']]['selected_option_id'] == 'false' ? 'checked' : ''; ?>>
                                            <label class="form-check-label">False</label>
                                        </div>
                                    </div>
                                
                                <?php elseif ($question['question_type'] === 'short_answer'): ?>
                                    <div class="form-group">
                                        <textarea class="form-control" 
                                                  name="answers[<?php echo $question['question_id']; ?>][text_answer]" 
                                                  rows="3"><?php echo isset($student_answers[$question['question_id']]) ? 
                                                  $student_answers[$question['question_id']]['text_answer'] : ''; ?></textarea>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </form>
                </div>

                <!-- Quiz info -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Quiz Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Course:</strong> <?php echo $quiz['course_title']; ?></p>
                            <p><strong>Passing Score:</strong> <?php echo $quiz['passing_score']; ?>%</p>
                            <?php if ($quiz['time_limit']): ?>
                            <p><strong>Time Limit:</strong> <?php echo floor($quiz['time_limit'] / 60); ?> minutes</p>
                            <div id="timer" class="alert alert-warning">
                                Time Remaining: <span id="timeRemaining"></span>
                            </div>
                            <?php endif; ?>
                            <p><strong>Questions:</strong> <?php echo count($questions); ?></p>
                            <p><strong>Total Points:</strong> <?php echo array_sum(array_column($questions, 'points')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Timer functionality
<?php if ($quiz['time_limit']): ?>
let timeLimit = <?php echo $quiz['time_limit']; ?>;
let startTime = <?php echo strtotime($attempt['start_time']); ?>;
let endTime = startTime + timeLimit;

function updateTimer() {
    let now = Math.floor(Date.now() / 1000);
    let remaining = endTime - now;
    
    if (remaining <= 0) {
        clearInterval(timerInterval);
        document.getElementById('submitQuiz').click();
        return;
    }
    
    let minutes = Math.floor(remaining / 60);
    let seconds = remaining % 60;
    document.getElementById('timeRemaining').textContent = 
        minutes + 'm ' + seconds + 's';
}

let timerInterval = setInterval(updateTimer, 1000);
updateTimer();
<?php endif; ?>

// Save progress
document.getElementById('saveProgress').addEventListener('click', async () => {
    try {
        const formData = new FormData(document.getElementById('quizForm'));
        formData.append('action', 'save');
        
        const response = await fetch('Action/quiz-submit.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            setFlashMessage('Progress saved successfully', 'success');
        } else {
            setFlashMessage(result.message || 'Failed to save progress', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        setFlashMessage('An error occurred while saving progress', 'danger');
    }
});

// Submit quiz
document.getElementById('submitQuiz').addEventListener('click', async () => {
    if (!confirm('Are you sure you want to submit the quiz? This action cannot be undone.')) {
        return;
    }
    
    try {
        const formData = new FormData(document.getElementById('quizForm'));
        formData.append('action', 'submit');
        
        const response = await fetch('Action/quiz-submit.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            setFlashMessage('Quiz submitted successfully', 'success');
            window.location.href = `Quiz-Result.php?attempt_id=${result.attempt_id}`;
        } else {
            setFlashMessage(result.message || 'Failed to submit quiz', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        setFlashMessage('An error occurred while submitting the quiz', 'danger');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>