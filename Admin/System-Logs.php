<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['admin_id'])) {
    include "../Controller/Admin/SystemLog.php";
    
    $admin_id = $_SESSION['admin_id'];
    $logs = getRecentLogs(0, 50);
    
    # Header
    $title = "EduPulse - System Logs";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2">System Logs</h2>
                    <p class="text-muted mb-0">View and monitor system activities</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <button type="button" class="btn btn-success ms-2" onclick="exportLogs()">
                        <i class="fa fa-download"></i> Export
                    </button>
                </div>
            </div>

            <!-- System Logs -->
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <?php if ($logs) { ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Action</th>
                                        <th>Description</th>
                                        <th>User</th>
                                        <th>User Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log) { ?>
                                        <tr>
                                            <td><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= getActionColor($log['action']) ?>">
                                                    <?= htmlspecialchars($log['action']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($log['description']) ?></td>
                                            <td>
                                                <?php
                                                if ($log['user_id']) {
                                                    if ($log['user_type'] == 'student') {
                                                        $student = getStudentById($log['user_id']);
                                                        echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
                                                    } else if ($log['user_type'] == 'instructor') {
                                                        $instructor = getInstructorById($log['user_id']);
                                                        echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']);
                                                    } else if ($log['user_type'] == 'admin') {
                                                        $admin = getAdminById($log['user_id']);
                                                        echo htmlspecialchars($admin['username']);
                                                    }
                                                } else {
                                                    echo '<span class="text-muted">System</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($log['user_type']) { ?>
                                                    <span class="badge bg-<?= getUserTypeColor($log['user_type']) ?>">
                                                        <?= ucfirst($log['user_type']) ?>
                                                    </span>
                                                <?php } else { ?>
                                                    <span class="text-muted">-</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="text-center text-muted">
                            <i class="fa fa-history fa-3x mb-3"></i>
                            <p>No system logs found</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Filter Logs</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="mb-3">
                        <label class="form-label">Action Type</label>
                        <select class="form-select bg-dark text-white" name="action">
                            <option value="">All</option>
                            <option value="Login">Login</option>
                            <option value="Logout">Logout</option>
                            <option value="Create">Create</option>
                            <option value="Update">Update</option>
                            <option value="Delete">Delete</option>
                            <option value="Enroll">Enroll</option>
                            <option value="Complete">Complete</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">User Type</label>
                        <select class="form-select bg-dark text-white" name="user_type">
                            <option value="">All</option>
                            <option value="student">Students</option>
                            <option value="instructor">Instructors</option>
                            <option value="admin">Admins</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date Range</label>
                        <div class="input-group">
                            <input type="date" class="form-control bg-dark text-white" name="start_date">
                            <span class="input-group-text">to</span>
                            <input type="date" class="form-control bg-dark text-white" name="end_date">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="applyFilter()">Apply Filter</button>
            </div>
        </div>
    </div>
</div>

<script>
function getActionColor(action) {
    const colors = {
        'Login': 'success',
        'Logout': 'secondary',
        'Create': 'primary',
        'Update': 'warning',
        'Delete': 'danger',
        'Enroll': 'info',
        'Complete': 'success'
    };
    return colors[action] || 'secondary';
}

function getUserTypeColor(type) {
    const colors = {
        'student': 'primary',
        'instructor': 'success',
        'admin': 'danger'
    };
    return colors[type] || 'secondary';
}

function applyFilter() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = `System-Logs.php?${params.toString()}`;
}

function exportLogs() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = `../Controller/Admin/SystemLog.php?action=export&${params.toString()}`;
}
</script>

<?php include "inc/Footer.php"; ?>
<?php
} else { 
    $em = "First login ";
    Util::redirect("../login.php", "error", $em);
}
?> 