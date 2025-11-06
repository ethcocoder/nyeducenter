<div id="users-section" class="content-section users-section">
    <h2 class="mb-4">
        <i class="fas fa-users me-2"></i>
        User Management
    </h2>
    <div class="card-custom">
        <div class="card-header d-flex justify-content-between align-items-center">
            All Users
            <button class="btn btn-primary btn-sm" onclick="showAddUserModal()">
                <i class="fas fa-plus me-2"></i> Add User
            </button>
        </div>
        <div class="card-body">
            
            <div class="overflow-auto">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <!-- User data will be loaded here -->
                    </tbody>
                </table>
                </div>
            </div>
            <div id="usersPagination" class="mt-3"></div>
        </div>
    </div>
</div>