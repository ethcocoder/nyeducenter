<div id="settings-section" class="content-section settings-section container-fluid">
    <h2 class="mb-4">
        <i class="fas fa-cog me-2"></i>
        System Settings
    </h2>
    <div class="card-custom">
        <div class="card-header">
            General Settings
        </div>
        <div class="card-body">
            <form id="settingsForm">
                <div class="mb-3">
                    <label for="appName" class="form-label">Application Name</label>
                    <input type="text" class="form-control" id="appName" name="app_name" required>
                </div>
                <div class="mb-3">
                    <label for="itemsPerPage" class="form-label">Items Per Page</label>
                    <input type="number" class="form-control" id="itemsPerPage" name="items_per_page" required>
                </div>
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>

    <div class="card-custom mt-4">
        <div class="card-header">
            Database Management
        </div>
        <div class="card-body">
            <button class="btn btn-secondary me-2" onclick="backupDatabase()">
                <i class="fas fa-database me-2"></i>Backup Database
            </button>
            <button class="btn btn-danger" onclick="clearActivityLog()">
                <i class="fas fa-eraser me-2"></i>Clear Activity Log
            </button>
        </div>
    </div>
</div>