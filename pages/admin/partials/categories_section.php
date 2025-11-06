<div id="categories-section" class="content-section categories-section">
    <h2 class="mb-4">
        <i class="fas fa-layer-group me-2"></i>
        Category Management
    </h2>
    <div class="card-custom">
        <div class="card-header d-flex justify-content-between align-items-center">
            All Categories
            <button class="btn btn-primary btn-sm" onclick="showAddCategoryModal()">
                <i class="fas fa-plus me-2"></i> Add Category
            </button>
        </div>
        <div class="card-body">
            
            <div class="overflow-auto">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Grade Name</th>
                            <th>Description</th>
                            <th>Books Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTableBody">
                        <!-- Category data will be loaded here -->
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>