<?php
require_once '../includes/header.php';
requireRole(['student']);

$attempt_id = $_GET['attempt_id'] ?? null;
if (!$attempt_id) {
    setFlashMessage('Attempt ID is required', 'danger');
    redirect('Courses.php');
}

// Get attempt details
$sql = "SELECT a.*, q.title_en as quiz_title, q.passing_score,
        c.title_en as course_title, c.course_id
        FROM quiz_attempt a
        JOIN quiz q ON a.quiz_id = q.quiz_id
        JOIN course c ON q.course_id = c.course_id
        WHERE a.attempt_id = ? AND a.student_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$attempt_id, $_SESSION['user_id']]);
$attempt = $stmt->fetch();

if (!$attempt) {
    setFlashMessage('Attempt not found', 'danger');
    redirect('Courses.php');
}

// Get questions and answers
$sql = "SELECT q.*, a.selected_option_id, a.text_answer, a.is_correct, a.points_earned,
        o.option_text_en as selected_option_text
        FROM question q
        LEFT JOIN quiz_answer a ON q.question_id = a.question_id AND a.attempt_id = ?
        LEFT JOIN question_option o ON a.selected_option_id = o.option_id
        WHERE q.quiz_id = ?
        ORDER BY q.`order`";
$stmt = $pdo->prepare($sql);
$stmt->execute([$attempt_id, $attempt['quiz_id']]);
$questions = $stmt->fetchAll();

// Calculate statistics
$total_questions = count($questions);
$correct_answers = count(array_filter($questions, function($q) {
    return $q['is_correct'];
}));
$total_points = array_sum(array_column($questions, 'points'));
$earned_points = array_sum(array_column($questions, 'points_earned'));
$score_percentage = $total_points > 0 ? ($earned_points / $total_points) * 100 : 0;
$passed = $score_percentage >= $attempt['passing_score'];
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
                        <a class="nav-link" href="Enrolled-Course.php?course_id=<?php echo $attempt['course_id']; ?>">
                            <i class="fas fa-arrow-left"></i> Back to Course
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Quiz Results</h1>
            </div>

            <div class="row">
                <!-- Quiz summary -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Quiz Summary</h5>
                        </div>
                        <div class="card-body">
                            <h4 class="card-title"><?php echo $attempt['quiz_title']; ?></h4>
                            <p class="card-text">Course: <?php echo $attempt['course_title']; ?></p>
                            
                            <div class="progress mb-3">
                                <div class="progress-bar <?php echo $passed ? 'bg-success' : 'bg-danger'; ?>" 
                                     role="progressbar" 
                                     style="width: <?php echo $score_percentage; ?>%"
                                     aria-valuenow="<?php echo $score_percentage; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?php echo number_format($score_percentage, 1); ?>%
                                </div>
                            </div>

                            <div class="row text-center">
                                <div class="col">
                                    <h5><?php echo $correct_answers; ?>/<?php echo $total_questions; ?></h5>
                                    <small class="text-muted">Correct Answers</small>
                                </div>
                                <div class="col">
                                    <h5><?php echo $earned_points; ?>/<?php echo $total_points; ?></h5>
                                    <small class="text-muted">Points Earned</small>
                                </div>
                            </div>

                            <hr>

                            <div class="d-grid">
                                <?php if ($passed): ?>
                                <button type="button" class="btn btn-success" onclick="generateCertificate()">
                                    <i class="fas fa-certificate"></i> Download Certificate
                                </button>
                                <?php else: ?>
                                <button type="button" class="btn btn-primary" onclick="retakeQuiz()">
                                    <i class="fas fa-redo"></i> Retake Quiz
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Question review -->
                <div class="col-md-8">
                    <?php foreach ($questions as $index => $question): ?>
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Question <?php echo $index + 1; ?></h5>
                            <span class="badge <?php echo $question['is_correct'] ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $question['points_earned']; ?>/<?php echo $question['points']; ?> points
                            </span>
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
                                        <input class="form-check-input" type="radio" disabled
                                               <?php echo $question['selected_option_id'] == $option['option_id'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label <?php echo $option['is_correct'] ? 'text-success' : ''; ?>">
                                            <?php echo $option['option_text_en']; ?>
                                            <?php if ($option['is_correct']): ?>
                                            <i class="fas fa-check"></i>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                            <?php elseif ($question['question_type'] === 'true_false'): ?>
                                <div class="options">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" disabled
                                               <?php echo $question['selected_option_id'] == 'true' ? 'checked' : ''; ?>>
                                        <label class="form-check-label">True</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" disabled
                                               <?php echo $question['selected_option_id'] == 'false' ? 'checked' : ''; ?>>
                                        <label class="form-check-label">False</label>
                                    </div>
                                </div>

                            <?php elseif ($question['question_type'] === 'short_answer'): ?>
                                <div class="form-group">
                                    <textarea class="form-control" rows="3" disabled><?php echo $question['text_answer']; ?></textarea>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function generateCertificate() {
    window.location.href = `Certificate-Generate.php?attempt_id=<?php echo $attempt_id; ?>`;
}

function retakeQuiz() {
    if (confirm('Are you sure you want to retake this quiz? Your previous attempt will be archived.')) {
        window.location.href = `Quiz-Take.php?quiz_id=<?php echo $attempt['quiz_id']; ?>`;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?> 