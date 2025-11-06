<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['teacher_id'])) {
    include "../Controller/Teacher/LearningPath.php";
    
    if (!isset($_GET['path_id'])) {
        $em = "Invalid request";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    $path_id = $_GET['path_id'];
    $learning_path = getLearningPathDetails($path_id);
    
    if (!$learning_path || $learning_path['teacher_id'] != $_SESSION['teacher_id']) {
        $em = "Learning path not found or you don't have access";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    $modules = getLearningPathModules($path_id);
    
    # Header
    $title = "EduPulse - Manage Modules";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2">Manage Modules</h2>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2"><?= htmlspecialchars($learning_path['title']) ?></span>
                        <span class="badge bg-info me-2"><?= htmlspecialchars($learning_path['subject']) ?></span>
                        <span class="text-muted">
                            <i class="fa fa-users me-1"></i> <?= $learning_path['enrolled_students'] ?> students
                        </span>
                    </div>
                </div>
                <div>
                    <a href="Learning-Paths.php" class="btn btn-secondary me-2">
                        <i class="fa fa-arrow-left"></i> Back to Learning Paths
                    </a>
                    <a href="Add-Module.php?path_id=<?= $path_id ?>" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add New Module
                    </a>
                </div>
            </div>

            <!-- Modules List -->
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Resources</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modules as $index => $module) { ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-book me-2 text-primary"></i>
                                                <?= htmlspecialchars($module['title']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= htmlspecialchars(substr($module['description'], 0, 100)) ?>...
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= $module['total_resources'] ?> resources
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fa fa-clock-o me-1"></i>
                                            <?= $module['duration'] ?> hours
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $module['status'] == 'active' ? 'success' : 
                                                                  ($module['status'] == 'draft' ? 'warning' : 'secondary') ?>">
                                                <?= ucfirst($module['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="Edit-Module.php?id=<?= $module['id'] ?>" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="Edit Module">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="Manage-Resources.php?module_id=<?= $module['id'] ?>" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Manage Resources">
                                                    <i class="fa fa-tasks"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="confirmDelete(<?= $module['id'] ?>)" 
                                                        title="Delete Module">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Module Ordering -->
            <div class="card bg-dark text-white mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Module Order</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Drag and drop modules to reorder them. The order will be reflected in how students see the modules.
                    </p>
                    <div id="module-order" class="list-group">
                        <?php foreach ($modules as $module) { ?>
                            <div class="list-group-item bg-dark text-white border-secondary" 
                                 data-module-id="<?= $module['id'] ?>">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-grip-vertical me-3 text-muted"></i>
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($module['title']) ?></h6>
                                        <small class="text-muted">
                                            <?= $module['total_resources'] ?> resources
                                            <span class="mx-2">|</span>
                                            <?= $module['duration'] ?> hours
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this module? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="Action/delete-module.php" method="POST" class="d-inline">
                    <input type="hidden" name="module_id" id="deleteModuleId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
// Delete confirmation
function confirmDelete(moduleId) {
    document.getElementById('deleteModuleId').value = moduleId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Module ordering
new Sortable(document.getElementById('module-order'), {
    animation: 150,
    handle: '.fa-grip-vertical',
    onEnd: function(evt) {
        const moduleIds = Array.from(evt.to.children).map(item => item.dataset.moduleId);
        
        // Send new order to server
        fetch('Action/update-module-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                path_id: <?= $path_id ?>,
                module_ids: moduleIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const toast = new bootstrap.Toast(document.createElement('div'));
                toast.show();
            }
        });
    }
});
</script>

<?php include "inc/Footer.php"; ?>
<?php
} else { 
    $em = "First login ";
    Util::redirect("../login.php", "error", $em);
}
?> 