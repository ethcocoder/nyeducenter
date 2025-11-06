<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['teacher_id'])) {
    include "../Controller/Teacher/LearningPath.php";
    
    if (!isset($_GET['module_id'])) {
        $em = "Invalid request";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    $module_id = $_GET['module_id'];
    $module = getModuleDetails($module_id);
    
    if (!$module || $module['teacher_id'] != $_SESSION['teacher_id']) {
        $em = "Module not found or you don't have access";
        Util::redirect("Learning-Paths.php", "error", $em);
    }
    
    $resources = getModuleResources($module_id);
    
    # Header
    $title = "EduPulse - Manage Resources";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2">Manage Resources</h2>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2"><?= htmlspecialchars($module['title']) ?></span>
                        <span class="badge bg-info me-2"><?= htmlspecialchars($module['path_title']) ?></span>
                        <span class="text-muted">
                            <i class="fa fa-clock me-1"></i> <?= $module['duration'] ?> hours
                        </span>
                    </div>
                </div>
                <div>
                    <a href="Manage-Modules.php?path_id=<?= $module['path_id'] ?>" class="btn btn-secondary me-2">
                        <i class="fa fa-arrow-left"></i> Back to Modules
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-plus"></i> Add Resource
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li>
                                <a class="dropdown-item" href="Add-Resource.php?module_id=<?= $module_id ?>&type=video">
                                    <i class="fa fa-video-camera text-danger me-2"></i> Video
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="Add-Resource.php?module_id=<?= $module_id ?>&type=document">
                                    <i class="fa fa-file-text text-primary me-2"></i> Document
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="Add-Resource.php?module_id=<?= $module_id ?>&type=quiz">
                                    <i class="fa fa-question-circle text-warning me-2"></i> Quiz
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="Add-Resource.php?module_id=<?= $module_id ?>&type=assignment">
                                    <i class="fa fa-tasks text-info me-2"></i> Assignment
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Resources List -->
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resources as $index => $resource) { ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
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
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $resource['type'] == 'Video' ? 'danger' : 
                                                                  ($resource['type'] == 'Document' ? 'primary' : 
                                                                  ($resource['type'] == 'Quiz' ? 'warning' : 'info')) ?>">
                                                <?= $resource['type'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= htmlspecialchars(substr($resource['description'], 0, 100)) ?>...
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($resource['duration']) { ?>
                                                <i class="fa fa-clock-o me-1"></i>
                                                <?= $resource['duration'] ?> min
                                            <?php } else { ?>
                                                -
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $resource['status'] == 'active' ? 'success' : 
                                                                  ($resource['status'] == 'draft' ? 'warning' : 'secondary') ?>">
                                                <?= ucfirst($resource['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="Edit-Resource.php?id=<?= $resource['id'] ?>" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="Edit Resource">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <?php if ($resource['type'] == 'Quiz') { ?>
                                                    <a href="Manage-Questions.php?resource_id=<?= $resource['id'] ?>" 
                                                       class="btn btn-sm btn-info" 
                                                       title="Manage Questions">
                                                        <i class="fa fa-list"></i>
                                                    </a>
                                                <?php } ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="confirmDelete(<?= $resource['id'] ?>)" 
                                                        title="Delete Resource">
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

            <!-- Resource Ordering -->
            <div class="card bg-dark text-white mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Resource Order</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Drag and drop resources to reorder them. The order will be reflected in how students see the resources.
                    </p>
                    <div id="resource-order" class="list-group">
                        <?php foreach ($resources as $resource) { ?>
                            <div class="list-group-item bg-dark text-white border-secondary" 
                                 data-resource-id="<?= $resource['id'] ?>">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-grip-vertical me-3 text-muted"></i>
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
                                        <small class="text-muted">
                                            <?= $resource['type'] ?>
                                            <?php if ($resource['duration']) { ?>
                                                <span class="mx-2">|</span>
                                                <?= $resource['duration'] ?> min
                                            <?php } ?>
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
                Are you sure you want to delete this resource? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="Action/delete-resource.php" method="POST" class="d-inline">
                    <input type="hidden" name="resource_id" id="deleteResourceId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
// Delete confirmation
function confirmDelete(resourceId) {
    document.getElementById('deleteResourceId').value = resourceId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Resource ordering
new Sortable(document.getElementById('resource-order'), {
    animation: 150,
    handle: '.fa-grip-vertical',
    onEnd: function(evt) {
        const resourceIds = Array.from(evt.to.children).map(item => item.dataset.resourceId);
        
        // Send new order to server
        fetch('Action/update-resource-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                module_id: <?= $module_id ?>,
                resource_ids: resourceIds
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