<div id="dashboard-section" class="content-section">
    <h2 class="mb-4">
        <i class="fas fa-tachometer-alt me-2"></i>
        Admin Dashboard
    </h2>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stats-card card-custom">
                <div class="card-body">
                    <div class="stats-number stats-primary"><?php echo $stats['total_users']; ?></div>
                    <div class="stats-label">Total Users</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stats-card card-custom">
                <div class="card-body">
                    <div class="stats-number stats-success"><?php echo $stats['total_books']; ?></div>
                    <div class="stats-label">System Books</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stats-card card-custom">
                <div class="card-body">
                    <div class="stats-number stats-warning"><?php echo $stats['total_categories']; ?></div>
                    <div class="stats-label">Grade Categories</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stats-card card-custom">
                <div class="card-body">
                    <div class="stats-number stats-danger"><?php echo $stats['total_activity']; ?></div>
                    <div class="stats-label">Recent Activities</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card-custom">
                <div class="card-body">
                    <h5 class="card-title mb-4">Quick Actions</h5>
                    <div class="action-buttons">
                        <button class="btn btn-success" onclick="showAddUserModal()">
                            <i class="fas fa-user-plus me-2"></i>
                            Add User
                        </button>
                        <button class="btn btn-primary" onclick="showAddCategoryModal()">
                            <i class="fas fa-layer-group me-2"></i>
                            Add Category
                        </button>
                        <button class="btn btn-success" onclick="showAddBookModal()">
                            <i class="fas fa-book me-2"></i>
                            Add Book
                        </button>
                        <button class="btn btn-danger" onclick="showSystemSettings()">
                            <i class="fas fa-cog me-2"></i>
                            System Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>