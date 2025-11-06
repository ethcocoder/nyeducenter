<?php
include "inc/Header.php";
requireRole(['student']);

// Get database connection
$conn = getDBConnection();

// Include models
require_once "../Models/Course.php";
$course = new Course($conn);

// Get search query
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;

// Search courses
$courses = [];
$total_courses = 0;

if ($search_query) {
    $offset = ($page - 1) * $per_page;
    $courses = $course->search($search_query, $offset, $per_page);
    $total_courses = $course->getSearchCount($search_query);
    $total_pages = ceil($total_courses / $per_page);
}
?>

<!-- Custom CSS -->
<style>
.course-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    border-radius: 10px;
    margin-bottom: 20px;
}

.course-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.course-image {
    height: 200px;
    object-fit: cover;
    border-radius: 10px 10px 0 0;
}

.search-header {
    background: linear-gradient(45deg, #4b6cb7, #182848);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.search-form {
    max-width: 600px;
    margin: 0 auto;
}

.search-input {
    border-radius: 25px;
    padding: 12px 25px;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.search-input:focus {
    box-shadow: 0 2px 15px rgba(0,0,0,0.2);
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.empty-state i {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 20px;
}

.pagination {
    margin-top: 30px;
}

.pagination .page-link {
    border-radius: 20px;
    margin: 0 5px;
    color: #4b6cb7;
}

.pagination .page-item.active .page-link {
    background: #4b6cb7;
    border-color: #4b6cb7;
}
</style>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="search-header">
                <h2 class="mb-4">Search Courses</h2>
                <form action="Search.php" method="GET" class="search-form">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control search-input" 
                               placeholder="Search for courses..." 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                        <div class="input-group-append">
                            <button class="btn btn-light" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if ($search_query): ?>
                <h4 class="mb-4">
                    Search results for "<?php echo htmlspecialchars($search_query); ?>"
                    <small class="text-muted">(<?php echo $total_courses; ?> results)</small>
                </h4>

                <?php if (empty($courses)): ?>
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h4>No Courses Found</h4>
                        <p>Try different keywords or browse our course catalog.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($courses as $course): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card course-card">
                                    <img src="<?php echo htmlspecialchars($course['cover_img']); ?>" 
                                         class="card-img-top course-image" 
                                         alt="<?php echo htmlspecialchars($course['title']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                        <p class="card-text text-muted">
                                            <?php 
                                            $description = htmlspecialchars($course['description']);
                                            echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                            ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="far fa-clock"></i> 
                                                <?php echo date('M d, Y', strtotime($course['created_at'])); ?>
                                            </small>
                                            <a href="Course.php?course_id=<?php echo $course['course_id']; ?>" 
                                               class="btn btn-primary">
                                                View Course
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Search results pages">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page-1; ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page+1; ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h4>Search for Courses</h4>
                    <p>Enter keywords to find courses that match your interests.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "inc/Footer.php"; ?>