<?php 
session_start();
include "../Utils/Util.php";
if (isset($_SESSION['username']) && isset($_SESSION['admin_id'])) {
    include "../Controller/Admin/SupportRequest.php";
    
    $admin_id = $_SESSION['admin_id'];
    $support_requests = getAllSupportRequests(0, 10);
    $total_requests = getSupportRequestCount();
    
    # Header
    $title = "EduPulse - Support Requests";
    include "inc/Header.php";
?>

<div class="wrapper">
    <?php include "inc/NavBar.php"; ?>
    
    <div class="main-content p-4">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-white mb-2">Support Requests</h2>
                    <p class="text-muted mb-0">Manage and respond to user support requests</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                </div>
            </div>

            <!-- Support Requests List -->
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <?php if ($support_requests) { ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($support_requests as $request) { ?>
                                        <tr>
                                            <td>#<?= $request['request_id'] ?></td>
                                            <td>
                                                <?php
                                                if ($request['user_type'] == 'student') {
                                                    $student = getStudentById($request['user_id']);
                                                    echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
                                                } else {
                                                    $instructor = getInstructorById($request['user_id']);
                                                    echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']);
                                                }
                                                ?>
                                            </td>
                                            <td><?= htmlspecialchars($request['subject']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $request['status'] == 'Open' ? 'success' : 
                                                                      ($request['status'] == 'In Progress' ? 'warning' : 'secondary') ?>">
                                                    <?= $request['status'] ?>
                                                </span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($request['created_at'])) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" 
                                                        onclick="viewRequest(<?= $request['request_id'] ?>)">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <?php if ($request['status'] == 'Open') { ?>
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="updateStatus(<?= $request['request_id'] ?>, 'In Progress')">
                                                        <i class="fa fa-clock"></i>
                                                    </button>
                                                <?php } ?>
                                                <?php if ($request['status'] == 'In Progress') { ?>
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="updateStatus(<?= $request['request_id'] ?>, 'Resolved')">
                                                        <i class="fa fa-check"></i>
                                                    </button>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="text-center text-muted">
                            <i class="fa fa-inbox fa-3x mb-3"></i>
                            <p>No support requests found</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Support Request Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="requestDetails"></div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Filter Requests</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select bg-dark text-white" name="status">
                            <option value="">All</option>
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">User Type</label>
                        <select class="form-select bg-dark text-white" name="user_type">
                            <option value="">All</option>
                            <option value="student">Students</option>
                            <option value="instructor">Instructors</option>
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
function viewRequest(requestId) {
    fetch(`../Controller/Admin/SupportRequest.php?action=view&id=${requestId}`)
        .then(response => response.json())
        .then(data => {
            const details = document.getElementById('requestDetails');
            details.innerHTML = `
                <div class="mb-3">
                    <h6>Subject</h6>
                    <p>${data.subject}</p>
                </div>
                <div class="mb-3">
                    <h6>Message</h6>
                    <p>${data.message}</p>
                </div>
                <div class="mb-3">
                    <h6>Status</h6>
                    <p><span class="badge bg-${data.status == 'Open' ? 'success' : 
                                           (data.status == 'In Progress' ? 'warning' : 'secondary')}">
                        ${data.status}
                    </span></p>
                </div>
                <div class="mb-3">
                    <h6>Created</h6>
                    <p>${new Date(data.created_at).toLocaleString()}</p>
                </div>
                ${data.responses ? `
                    <div class="mb-3">
                        <h6>Responses</h6>
                        ${data.responses.map(response => `
                            <div class="card bg-dark mb-2">
                                <div class="card-body">
                                    <p class="mb-1">${response.message}</p>
                                    <small class="text-muted">
                                        By ${response.responder_name} on ${new Date(response.created_at).toLocaleString()}
                                    </small>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                ` : ''}
                <div class="mb-3">
                    <h6>Add Response</h6>
                    <textarea class="form-control bg-dark text-white" id="responseMessage" rows="3"></textarea>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('viewRequestModal')).show();
        });
}

function updateStatus(requestId, status) {
    if (confirm(`Are you sure you want to mark this request as ${status}?`)) {
        fetch(`../Controller/Admin/SupportRequest.php?action=update_status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                request_id: requestId,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update status');
            }
        });
    }
}

function applyFilter() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = `Support-Requests.php?${params.toString()}`;
}
</script>

<?php include "inc/Footer.php"; ?>
<?php
} else { 
    $em = "First login ";
    Util::redirect("../login.php", "error", $em);
}
?> 