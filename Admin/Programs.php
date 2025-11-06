<?php
include "inc/Header.php";
requireRole(['admin']);

// Get database connection
$conn = getDBConnection();

// Include models
require_once "../Models/NanodegreeProgram.php";
require_once "../Models/IndustryPartner.php";

$program = new NanodegreeProgram($conn);
$partner = new IndustryPartner($conn);

// Get all programs
$programs = $program->getAll();
$partners = $partner->getAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Nanodegree Programs</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addProgramModal">
                            Add New Program
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Partner</th>
                                <th>Duration</th>
                                <th>Level</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($programs as $prog): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prog['title']); ?></td>
                                <td><?php echo htmlspecialchars($prog['partner_name'] ?? 'None'); ?></td>
                                <td><?php echo $prog['duration_weeks']; ?> weeks</td>
                                <td><?php echo htmlspecialchars($prog['level']); ?></td>
                                <td>$<?php echo number_format($prog['price'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $prog['status'] === 'Active' ? 'success' : 'danger'; ?>">
                                        <?php echo htmlspecialchars($prog['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="Program-View.php?id=<?php echo $prog['program_id']; ?>" 
                                       class="btn btn-info btn-sm">View</a>
                                    <button type="button" class="btn btn-primary btn-sm" 
                                            onclick="editProgram(<?php echo $prog['program_id']; ?>)">Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            onclick="deleteProgram(<?php echo $prog['program_id']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Program Modal -->
<div class="modal fade" id="addProgramModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Program</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addProgramForm" action="Action/Program-Action.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Duration (weeks)</label>
                        <input type="number" class="form-control" name="duration_weeks" required>
                    </div>
                    <div class="form-group">
                        <label>Level</label>
                        <select class="form-control" name="level" required>
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <input type="number" class="form-control" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Industry Partner</label>
                        <select class="form-control" name="partner_id">
                            <option value="">None</option>
                            <?php foreach ($partners as $p): ?>
                            <option value="<?php echo $p['partner_id']; ?>">
                                <?php echo htmlspecialchars($p['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="add_program">Add Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editProgram(programId) {
    // Implement edit functionality
    window.location.href = 'Program-Edit.php?id=' + programId;
}

function deleteProgram(programId) {
    if (confirm('Are you sure you want to delete this program?')) {
        window.location.href = 'Action/Program-Action.php?delete=' + programId;
    }
}
</script>

<?php include "inc/Footer.php"; ?> 