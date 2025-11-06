<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

require_once '../Controller/Student/Assessment.php';

$assessments = getStudentAssessments($student_id, $offset, $limit);
$total_assessments = getStudentAssessmentCount($student_id);
$total_pages = ceil($total_assessments / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assessments - Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'inc/NavBar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2">My Assessments</h1>
                </div>

                <!-- Assessments List -->
                <div class="row">
                    <?php if (empty($assessments)): ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                No assessments available at the moment.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($assessments as $assessment): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($assessment['title']); ?></h5>
                                        <p class="card-text">
                                            <?php echo htmlspecialchars($assessment['description']); ?>
                                        </p>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> Duration: <?php echo $assessment['duration']; ?> minutes
                                            </small>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> Due: <?php echo date('M d, Y', strtotime($assessment['due_date'])); ?>
                                            </small>
                                        </div>
                                        <?php if (isset($assessment['score'])): ?>
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-star"></i> Score: <?php echo $assessment['score']; ?>%
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-transparent border-top border-secondary">
                                        <?php if (!isset($assessment['score'])): ?>
                                            <a href="Quiz-Take.php?id=<?php echo $assessment['assessment_id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-pencil-alt"></i> Take Assessment
                                            </a>
                                        <?php else: ?>
                                            <a href="Quiz-Result.php?id=<?php echo $assessment['assessment_id']; ?>" 
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> View Results
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html> 