<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['student_id'])) {
    include "../Controller/Student/LearningPath.php";
    
    if (isset($_GET['id'])) {
        $module_id = $_GET['id'];
        $module = getModuleProgress($_SESSION['student_id'], $module_id);
        
        if (!$module) {
            $em = "Module not found or you don't have access";
            Util::redirect("Learning-Paths.php", "error", $em);
        }
        
        $resources = getModuleResources($module_id);
    } else {
        $em = "Invalid request";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    # Header
    $title = "EduPulse - " . $module['title'];
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2"><?= htmlspecialchars($module['title']) ?></h2>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2"><?= htmlspecialchars($module['path_title']) ?></span>
                        <span class="text-muted">
                            <i class="fa fa-clock me-1"></i> <?= $module['duration'] ?> hours
                        </span>
                    </div>
                </div>
                <a href="Learning-Path-View.php?id=<?= $module['path_id'] ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Learning Path
                </a>
            </div>

            <!-- Progress Section -->
            <div class="card bg-dark text-white mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-3">Module Progress</h5>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?= $module['progress_percentage'] ?>%" 
                                     aria-valuenow="<?= $module['progress_percentage'] ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?= $module['progress_percentage'] ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                <?= $module['completed_resources'] ?> of <?= $module['total_resources'] ?> resources completed
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex flex-column align-items-end">
                                <span class="h4 mb-0"><?= $module['progress_percentage'] ?>%</span>
                                <small class="text-muted">Complete</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div class="row">
                <div class="col-md-8">
                    <!-- Module Description -->
                    <div class="card bg-dark text-white mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">About This Module</h5>
                            <p><?= htmlspecialchars($module['description']) ?></p>
                        </div>
                    </div>

                    <!-- Learning Resources -->
                    <div class="card bg-dark text-white">
                        <div class="card-header">
                            <h5 class="mb-0">Learning Resources</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($resources as $resource) { ?>
                                <div class="list-group-item bg-dark text-white border-secondary">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <?php if ($resource['type'] == 'Video') { ?>
                                                    <i class="fa fa-video-camera text-danger me-2"></i>
                                                <?php } elseif ($resource['type'] == 'Document') { ?>
                                                    <i class="fa fa-file-text text-primary me-2"></i>
                                                <?php } elseif ($resource['type'] == 'Quiz') { ?>
                                                    <i class="fa fa-question-circle text-warning me-2"></i>
                                                <?php } else { ?>
                                                    <i class="fa fa-tasks text-info me-2"></i>
                                                <?php } ?>
                                                <?= htmlspecialchars($resource['title']) ?>
                                            </h6>
                                            <p class="text-muted small mb-0"><?= htmlspecialchars($resource['description']) ?></p>
                                            <?php if ($resource['duration']) { ?>
                                                <small class="text-muted">
                                                    <i class="fa fa-clock-o"></i> <?= $resource['duration'] ?> minutes
                                                </small>
                                            <?php } ?>
                                        </div>
                                        <div class="text-end">
                                            <?php if ($resource['completed']) { ?>
                                                <span class="badge bg-success me-2">Completed</span>
                                            <?php } ?>
                                            <a href="Resource-View.php?id=<?= $resource['resource_id'] ?>" class="btn btn-sm btn-primary">
                                                <?= $resource['completed'] ? 'Review' : 'Start' ?>
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
                    <!-- Module Navigation -->
                    <div class="card bg-dark text-white mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Module Navigation</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($resources as $resource) { ?>
                                <a href="Resource-View.php?id=<?= $resource['resource_id'] ?>" 
                                   class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="d-block text-muted"><?= $resource['type'] ?></small>
                                        <?= htmlspecialchars($resource['title']) ?>
                                    </div>
                                    <?php if ($resource['completed']) { ?>
                                        <i class="fa fa-check-circle text-success"></i>
                                    <?php } ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Support Section -->
                    <div class="card bg-dark text-white">
                        <div class="card-header">
                            <h5 class="mb-0">Need Help?</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Get support for this module:</p>
                            <a href="Support-Services.php?module=<?= $module_id ?>" class="btn btn-outline-light w-100 mb-2">
                                <i class="fa fa-life-ring me-2"></i> Get Help
                            </a>
                            <a href="Discussion-Forum.php?module=<?= $module_id ?>" class="btn btn-outline-light w-100">
                                <i class="fa fa-comments me-2"></i> Discuss
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