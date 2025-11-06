<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['student_id'])) {
    include "../Controller/Student/LearningPath.php";
    
    if (isset($_GET['id'])) {
        $path_id = $_GET['id'];
        $learning_path = getLearningPathProgress($_SESSION['student_id'], $path_id);
        
        if (!$learning_path) {
            $em = "Learning path not found or you are not enrolled";
            Util::redirect("Learning-Paths.php", "error", $em);
        }
        
        $modules = getLearningPathModules($path_id, $_SESSION['student_id']);
    } else {
        $em = "Invalid request";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    # Header
    $title = "EduPulse - " . $learning_path['title'];
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2"><?= htmlspecialchars($learning_path['title']) ?></h2>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2"><?= htmlspecialchars($learning_path['grade_level']) ?></span>
                        <span class="badge bg-info me-2"><?= htmlspecialchars($learning_path['subject']) ?></span>
                        <span class="text-muted">
                            <i class="fa fa-clock me-1"></i> <?= $learning_path['credits'] ?> Credits
                            <span class="mx-2">|</span>
                            <i class="fa fa-calendar me-1"></i> <?= $learning_path['semester'] ?> Semester
                        </span>
                    </div>
                </div>
                <a href="Learning-Paths.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Learning Paths
                </a>
            </div>

            <!-- Progress Section -->
            <div class="card bg-dark text-white mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-3">Overall Progress</h5>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?= $learning_path['progress_percentage'] ?>%" 
                                     aria-valuenow="<?= $learning_path['progress_percentage'] ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?= $learning_path['progress_percentage'] ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                <?= $learning_path['completed_modules'] ?> of <?= $learning_path['total_modules'] ?> modules completed
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex flex-column align-items-end">
                                <span class="h4 mb-0"><?= $learning_path['progress_percentage'] ?>%</span>
                                <small class="text-muted">Complete</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modules Section -->
            <div class="row">
                <div class="col-md-8">
                    <!-- Current Module -->
                    <?php if ($learning_path['current_module']) { ?>
                        <div class="card bg-dark text-white mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Current Module</h5>
                                <span class="badge bg-primary">In Progress</span>
                            </div>
                            <div class="card-body">
                                <h4><?= htmlspecialchars($learning_path['current_module']['title']) ?></h4>
                                <p class="text-muted"><?= htmlspecialchars($learning_path['current_module']['description']) ?></p>
                                
                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: <?= $learning_path['current_module']['progress_percentage'] ?>%" 
                                         aria-valuenow="<?= $learning_path['current_module']['progress_percentage'] ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <?= $learning_path['current_module']['completed_resources'] ?> of <?= $learning_path['current_module']['total_resources'] ?> resources completed
                                    </small>
                                    <a href="Module-View.php?id=<?= $learning_path['current_module']['module_id'] ?>" class="btn btn-primary">
                                        Continue Learning
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <!-- Module List -->
                    <div class="card bg-dark text-white">
                        <div class="card-header">
                            <h5 class="mb-0">Learning Modules</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($modules as $module) { ?>
                                <div class="list-group-item bg-dark text-white border-secondary">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($module['title']) ?></h6>
                                            <p class="text-muted small mb-0"><?= htmlspecialchars($module['description']) ?></p>
                                        </div>
                                        <div class="text-end">
                                            <div class="progress mb-2" style="width: 100px; height: 5px;">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: <?= $module['progress_percentage'] ?>%" 
                                                     aria-valuenow="<?= $module['progress_percentage'] ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <a href="Module-View.php?id=<?= $module['module_id'] ?>" class="btn btn-sm btn-primary">
                                                <?= $module['progress_percentage'] > 0 ? 'Continue' : 'Start' ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Learning Path Info -->
                    <div class="card bg-dark text-white mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">About This Path</h5>
                        </div>
                        <div class="card-body">
                            <p><?= htmlspecialchars($learning_path['description']) ?></p>
                            <hr class="border-secondary">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Modules:</span>
                                <span><?= $learning_path['total_modules'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Hours:</span>
                                <span><?= $learning_path['total_hours'] ?> hours</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Last Accessed:</span>
                                <span><?= date('M d, Y', strtotime($learning_path['last_accessed'])) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Support Section -->
                    <div class="card bg-dark text-white">
                        <div class="card-header">
                            <h5 class="mb-0">Need Help?</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Get support from your teachers and peers:</p>
                            <a href="Support-Services.php" class="btn btn-outline-light w-100 mb-2">
                                <i class="fa fa-life-ring me-2"></i> Support Services
                            </a>
                            <a href="Discussion-Forum.php" class="btn btn-outline-light w-100">
                                <i class="fa fa-comments me-2"></i> Discussion Forum
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "inc/Footer.php"; ?>
<?php
} else { 
    $em = "First login ";
    Util::redirect("../login.php", "error", $em);
}
?> 