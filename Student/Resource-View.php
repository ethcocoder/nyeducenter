<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['student_id'])) {
    include "../Controller/Student/LearningPath.php";
    
    if (isset($_GET['id'])) {
        $resource_id = $_GET['id'];
        $resource = getResourceDetails($_SESSION['student_id'], $resource_id);
        
        if (!$resource) {
            $em = "Resource not found or you don't have access";
            Util::redirect("Learning-Paths.php", "error", $em);
        }
    } else {
        $em = "Invalid request";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    # Header
    $title = "EduPulse - " . $resource['title'];
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2"><?= htmlspecialchars($resource['title']) ?></h2>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2"><?= htmlspecialchars($resource['module_title']) ?></span>
                        <span class="badge bg-info me-2"><?= htmlspecialchars($resource['type']) ?></span>
                        <?php if ($resource['duration']) { ?>
                            <span class="text-muted">
                                <i class="fa fa-clock me-1"></i> <?= $resource['duration'] ?> minutes
                            </span>
                        <?php } ?>
                    </div>
                </div>
                <a href="Module-View.php?id=<?= $resource['module_id'] ?>" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Module
                </a>
            </div>

            <!-- Content Section -->
            <div class="row">
                <div class="col-md-8">
                    <!-- Resource Content -->
                    <div class="card bg-dark text-white mb-4">
                        <div class="card-body">
                            <?php if ($resource['type'] == 'Video') { ?>
                                <!-- Video Player -->
                                <div class="ratio ratio-16x9 mb-4">
                                    <iframe src="<?= htmlspecialchars($resource['content_url']) ?>" 
                                            allowfullscreen></iframe>
                                </div>
                            <?php } elseif ($resource['type'] == 'Document') { ?>
                                <!-- Document Viewer -->
                                <div class="document-viewer mb-4">
                                    <iframe src="<?= htmlspecialchars($resource['content_url']) ?>" 
                                            class="w-100" style="height: 600px;"></iframe>
                                </div>
                            <?php } elseif ($resource['type'] == 'Quiz') { ?>
                                <!-- Quiz Interface -->
                                <div class="quiz-container">
                                    <form id="quizForm" action="Action/submit-quiz.php" method="POST">
                                        <input type="hidden" name="resource_id" value="<?= $resource_id ?>">
                                        <?php foreach ($resource['questions'] as $index => $question) { ?>
                                            <div class="question mb-4">
                                                <h5 class="mb-3">Question <?= $index + 1 ?></h5>
                                                <p><?= htmlspecialchars($question['text']) ?></p>
                                                
                                                <?php if ($question['type'] == 'multiple_choice') { ?>
                                                    <div class="options">
                                                        <?php foreach ($question['options'] as $option) { ?>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="radio" 
                                                                       name="q<?= $question['id'] ?>" 
                                                                       value="<?= $option['id'] ?>" 
                                                                       id="q<?= $question['id'] ?>_o<?= $option['id'] ?>">
                                                                <label class="form-check-label" 
                                                                       for="q<?= $question['id'] ?>_o<?= $option['id'] ?>">
                                                                    <?= htmlspecialchars($option['text']) ?>
                                                                </label>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                <?php } elseif ($question['type'] == 'text') { ?>
                                                    <div class="form-group">
                                                        <textarea class="form-control bg-dark text-white" 
                                                                  name="q<?= $question['id'] ?>" 
                                                                  rows="3"></textarea>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            Submit Quiz
                                        </button>
                                    </form>
                                </div>
                            <?php } else { ?>
                                <!-- Generic Content -->
                                <div class="content">
                                    <?= $resource['content'] ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Discussion Section -->
                    <div class="card bg-dark text-white">
                        <div class="card-header">
                            <h5 class="mb-0">Discussion</h5>
                        </div>
                        <div class="card-body">
                            <div class="discussion-list mb-4">
                                <?php foreach ($resource['discussions'] as $discussion) { ?>
                                    <div class="discussion-item mb-3">
                                        <div class="d-flex">
                                            <img src="<?= $discussion['user_avatar'] ?>" 
                                                 class="rounded-circle me-3" 
                                                 width="40" height="40" 
                                                 alt="<?= htmlspecialchars($discussion['username']) ?>">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($discussion['username']) ?></h6>
                                                <p class="mb-1"><?= htmlspecialchars($discussion['content']) ?></p>
                                                <small class="text-muted">
                                                    <?= date('M d, Y H:i', strtotime($discussion['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <form action="Action/add-discussion.php" method="POST">
                                <input type="hidden" name="resource_id" value="<?= $resource_id ?>">
                                <div class="form-group">
                                    <textarea class="form-control bg-dark text-white" 
                                              name="content" rows="3" 
                                              placeholder="Add to the discussion..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary mt-2">
                                    Post Comment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Resource Info -->
                    <div class="card bg-dark text-white mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">About This Resource</h5>
                        </div>
                        <div class="card-body">
                            <p><?= htmlspecialchars($resource['description']) ?></p>
                            <hr class="border-secondary">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Status:</span>
                                <span class="badge bg-<?= $resource['completed'] ? 'success' : 'warning' ?>">
                                    <?= $resource['completed'] ? 'Completed' : 'In Progress' ?>
                                </span>
                            </div>
                            <?php if ($resource['last_accessed']) { ?>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Last Accessed:</span>
                                    <span><?= date('M d, Y', strtotime($resource['last_accessed'])) ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Related Resources -->
                    <div class="card bg-dark text-white mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Related Resources</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($resource['related_resources'] as $related) { ?>
                                <a href="Resource-View.php?id=<?= $related['id'] ?>" 
                                   class="list-group-item bg-dark text-white border-secondary">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($related['title']) ?></h6>
                                            <small class="text-muted"><?= $related['type'] ?></small>
                                        </div>
                                        <?php if ($related['completed']) { ?>
                                            <i class="fa fa-check-circle text-success"></i>
                                        <?php } ?>
                                    </div>
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
                            <p class="mb-3">Get support for this resource:</p>
                            <a href="Support-Services.php?resource=<?= $resource_id ?>" class="btn btn-outline-light w-100 mb-2">
                                <i class="fa fa-life-ring me-2"></i> Get Help
                            </a>
                            <a href="Discussion-Forum.php?resource=<?= $resource_id ?>" class="btn btn-outline-light w-100">
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