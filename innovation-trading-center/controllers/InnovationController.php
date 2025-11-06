<?php
require_once 'BaseController.php';

class InnovationController extends BaseController {
    
    // List all innovations with filters and pagination
    public function index() {
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'category' => $_GET['category'] ?? '',
            'search' => $_GET['search'] ?? '',
            'location' => $_GET['location'] ?? '',
            'stage' => $_GET['stage'] ?? ''
        ];
        
        $filters = array_filter($filters, function($v) { return $v !== '' && $v !== null; });
        
        $innovations = $this->innovation->getAllWithDetails($page, $filters);
        $categories = $this->category->getActive();
        
        $this->render('innovations/list', [
            'innovations' => $innovations,
            'categories' => $categories,
            'filters' => $filters,
            'currentUser' => $this->getCurrentUser()
        ]);
    }
    
    // View single innovation details
    public function show($id = null) {
        if (!$id) {
            $id = $_GET['id'] ?? null;
        }
        
        if (!$id) {
            $this->redirect('/innovations');
        }
        
        $innovation = $this->innovation->getByIdWithDetails($id);
        if (!$innovation) {
            $this->redirect('/innovations');
        }
        
        // Increment view count
        $this->innovation->incrementViews($id);
        
        // Get media files
        $media = $this->innovation->getMedia($id);
        
        // Check if current user can edit this innovation
        $canEdit = false;
        $currentUser = $this->getCurrentUser();
        if ($currentUser && $currentUser['id'] == $innovation['user_id']) {
            $canEdit = true;
        }
        
        $this->render('innovations/detail', [
            'innovation' => $innovation,
            'media' => $media,
            'canEdit' => $canEdit,
            'currentUser' => $currentUser
        ]);
    }
    
    // Show innovation creation form
    public function create() {
        $this->requireInnovator();
        
        $categories = $this->category->getActive();
        
        $this->render('innovations/form', [
            'categories' => $categories,
            'innovation' => null,
            'currentUser' => $this->getCurrentUser()
        ]);
    }
    
    // Handle innovation creation
    public function store() {
        $this->requireInnovator();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/innovations/create');
        }
        
        $data = [
            'user_id' => $_SESSION['user_id'],
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'funding_needs' => !empty($_POST['funding_needs']) ? (float)$_POST['funding_needs'] : null,
            'funding_currency' => $_POST['funding_currency'] ?? 'ETB',
            'location' => trim($_POST['location'] ?? ''),
            'stage' => $_POST['stage'] ?? 'idea',
            'status' => 'published',
            'video_url' => trim($_POST['video_url'] ?? ''),
            'website_url' => trim($_POST['website_url'] ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? ''),
            'contact_phone' => trim($_POST['contact_phone'] ?? '')
        ];
        
        $errors = $this->validate($data, [
            'title' => 'required|max:255',
            'description' => 'required|min:50',
            'category_id' => 'required'
        ]);
        
        if (empty($errors)) {
            // Handle featured image upload
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $uploadedFile = $this->uploadFile($_FILES['featured_image'], 'uploads/innovations');
                if ($uploadedFile) {
                    $data['featured_image'] = $uploadedFile;
                }
            }
            
            $innovationId = $this->innovation->create($data);
            
            // Handle additional media files
            if (isset($_FILES['media']) && is_array($_FILES['media']['name'])) {
                foreach ($_FILES['media']['name'] as $key => $name) {
                    if ($_FILES['media']['error'][$key] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['media']['name'][$key],
                            'type' => $_FILES['media']['type'][$key],
                            'tmp_name' => $_FILES['media']['tmp_name'][$key],
                            'error' => $_FILES['media']['error'][$key],
                            'size' => $_FILES['media']['size'][$key]
                        ];
                        
                        $uploadedFile = $this->uploadFile($file, 'uploads/innovations');
                        if ($uploadedFile) {
                            $fileType = pathinfo($name, PATHINFO_EXTENSION);
                            $mediaType = in_array(strtolower($fileType), ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'document';
                            
                            $this->innovation->addMedia(
                                $innovationId,
                                $uploadedFile,
                                $name,
                                $mediaType,
                                $file['size']
                            );
                        }
                    }
                }
            }
            
            $this->setFlash('success', 'Innovation created successfully!');
            $this->redirect("/innovations/{$innovationId}");
        } else {
            $categories = $this->category->getActive();
            $this->render('innovations/form', [
                'errors' => $errors,
                'data' => $data,
                'categories' => $categories,
                'innovation' => null,
                'currentUser' => $this->getCurrentUser()
            ]);
        }
    }
    
    // Show innovation edit form
    public function edit($id = null) {
        if (!$id) {
            $id = $_GET['id'] ?? null;
        }
        
        if (!$id) {
            $this->redirect('/innovations');
        }
        
        $this->requireLogin();
        
        $innovation = $this->innovation->find($id);
        if (!$innovation) {
            $this->redirect('/innovations');
        }
        
        // Check if user owns this innovation
        $currentUser = $this->getCurrentUser();
        if ($currentUser['id'] != $innovation['user_id']) {
            $this->redirect('/innovations');
        }
        
        $categories = $this->category->getActive();
        $media = $this->innovation->getMedia($id);
        
        $this->render('innovations/form', [
            'innovation' => $innovation,
            'categories' => $categories,
            'media' => $media,
            'currentUser' => $currentUser
        ]);
    }
    
    // Handle innovation update
    public function update($id = null) {
        if (!$id) {
            $id = $_GET['id'] ?? null;
        }
        
        if (!$id) {
            $this->redirect('/innovations');
        }
        
        $this->requireLogin();
        
        $innovation = $this->innovation->find($id);
        if (!$innovation) {
            $this->redirect('/innovations');
        }
        
        // Check if user owns this innovation
        $currentUser = $this->getCurrentUser();
        if ($currentUser['id'] != $innovation['user_id']) {
            $this->redirect('/innovations');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/innovations/{$id}/edit");
        }
        
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'funding_needs' => !empty($_POST['funding_needs']) ? (float)$_POST['funding_needs'] : null,
            'funding_currency' => $_POST['funding_currency'] ?? 'ETB',
            'location' => trim($_POST['location'] ?? ''),
            'stage' => $_POST['stage'] ?? 'idea',
            'video_url' => trim($_POST['video_url'] ?? ''),
            'website_url' => trim($_POST['website_url'] ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? ''),
            'contact_phone' => trim($_POST['contact_phone'] ?? '')
        ];
        
        $errors = $this->validate($data, [
            'title' => 'required|max:255',
            'description' => 'required|min:50',
            'category_id' => 'required'
        ]);
        
        if (empty($errors)) {
            // Handle featured image upload
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $uploadedFile = $this->uploadFile($_FILES['featured_image'], 'uploads/innovations');
                if ($uploadedFile) {
                    $data['featured_image'] = $uploadedFile;
                }
            }
            
            $this->innovation->update($id, $data);
            
            $this->setFlash('success', 'Innovation updated successfully!');
            $this->redirect("/innovations/{$id}");
        } else {
            $categories = $this->category->getActive();
            $media = $this->innovation->getMedia($id);
            
            $this->render('innovations/form', [
                'errors' => $errors,
                'data' => $data,
                'categories' => $categories,
                'innovation' => $innovation,
                'media' => $media,
                'currentUser' => $currentUser
            ]);
        }
    }
    
    // Handle innovation deletion
    public function delete($id = null) {
        if (!$id) {
            $id = $_GET['id'] ?? null;
        }
        
        if (!$id) {
            $this->redirect('/innovations');
        }
        
        $this->requireLogin();
        
        $innovation = $this->innovation->find($id);
        if (!$innovation) {
            $this->redirect('/innovations');
        }
        
        // Check if user owns this innovation
        $currentUser = $this->getCurrentUser();
        if ($currentUser['id'] != $innovation['user_id']) {
            $this->redirect('/innovations');
        }
        
        $this->innovation->delete($id);
        
        $this->setFlash('success', 'Innovation deleted successfully!');
        $this->redirect('/innovations');
    }
    
    // Publish/unpublish innovation
    public function toggleStatus($id = null) {
        if (!$id) {
            $id = $_GET['id'] ?? null;
        }
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'Invalid innovation ID']);
        }
        
        $this->requireLogin();
        
        $innovation = $this->innovation->find($id);
        if (!$innovation) {
            $this->json(['success' => false, 'message' => 'Innovation not found']);
        }
        
        // Check if user owns this innovation
        $currentUser = $this->getCurrentUser();
        if ($currentUser['id'] != $innovation['user_id']) {
            $this->json(['success' => false, 'message' => 'Unauthorized']);
        }
        
        $newStatus = $innovation['status'] === 'published' ? 'draft' : 'published';
        $this->innovation->update($id, ['status' => $newStatus]);
        
        $this->json(['success' => true, 'status' => $newStatus]);
    }
    
    // Add to favorites
    public function favorite($id = null) {
        if (!$id) {
            $id = $_GET['id'] ?? null;
        }
        
        if (!$id) {
            $this->json(['success' => false, 'message' => 'Invalid innovation ID']);
        }
        
        $this->requireLogin();
        
        $innovation = $this->innovation->find($id);
        if (!$innovation) {
            $this->json(['success' => false, 'message' => 'Innovation not found']);
        }
        
        $currentUser = $this->getCurrentUser();
        
        // Check if already favorited
        $sql = "SELECT COUNT(*) as count FROM favorites WHERE user_id = :user_id AND innovation_id = :innovation_id";
        $result = $this->innovation->getDb()->fetch($sql, ['user_id' => $currentUser['id'], 'innovation_id' => $id]);
        
        if ($result['count'] > 0) {
            // Remove from favorites
            $this->innovation->getDb()->delete('favorites', 'user_id = :user_id AND innovation_id = :innovation_id', 
                ['user_id' => $currentUser['id'], 'innovation_id' => $id]);
            $this->json(['success' => true, 'favorited' => false]);
        } else {
            // Add to favorites
            $this->innovation->getDb()->insert('favorites', [
                'user_id' => $currentUser['id'],
                'innovation_id' => $id
            ]);
            $this->json(['success' => true, 'favorited' => true]);
        }
    }
    
    // List only the current user's innovations
    public function myInnovations() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'user_id' => $currentUser['id']
        ];
        $innovations = $this->innovation->getAllWithDetails($page, $filters);
        $categories = $this->category->getActive();
        $this->render('innovations/list', [
            'innovations' => $innovations,
            'categories' => $categories,
            'filters' => $filters,
            'currentUser' => $currentUser
        ]);
    }
    
    public function favorites() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        $favorites = $this->innovation->getFavoritesByUser($currentUser['id']);
        $categories = $this->category->getActive();
        $this->render('innovations/list', [
            'innovations' => [
                'data' => $favorites,
                'total' => count($favorites),
                'per_page' => count($favorites),
                'current_page' => 1,
                'last_page' => 1,
                'from' => 1,
                'to' => count($favorites)
            ],
            'categories' => $categories,
            'filters' => [],
            'currentUser' => $currentUser
        ]);
    }
    
    // Show sponsor form
    public function sponsorForm($id = null) {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        if ($currentUser['role'] !== 'sponsor') {
            $this->redirect('/innovations/' . $id);
        }
        require_once __DIR__ . '/../models/Sponsorship.php';
        $sponsorship = new Sponsorship();
        if ($sponsorship->hasSponsored($currentUser['id'], $id)) {
            $this->setFlash('info', 'You have already sponsored this innovation.');
            $this->redirect('/innovations/' . $id);
        }
        $innovation = $this->innovation->getByIdWithDetails($id);
        if (!$innovation) {
            $this->redirect('/innovations');
        }
        $this->render('innovations/sponsor_form', [
            'innovation' => $innovation,
            'currentUser' => $currentUser
        ]);
    }

    // Handle sponsor POST
    public function sponsorInnovation($id = null) {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        if ($currentUser['role'] !== 'sponsor') {
            $this->redirect('/innovations/' . $id);
        }
        require_once __DIR__ . '/../models/Sponsorship.php';
        $sponsorship = new Sponsorship();
        if ($sponsorship->hasSponsored($currentUser['id'], $id)) {
            $this->setFlash('info', 'You have already sponsored this innovation.');
            $this->redirect('/innovations/' . $id);
        }
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : null;
        $sponsorship->createSponsorship($currentUser['id'], $id, $amount);
        $this->setFlash('success', 'Thank you for sponsoring this innovation!');
        $this->redirect('/innovations/' . $id);
    }

    // List all sponsorships for the current innovator
    public function mySponsorships() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        if ($currentUser['role'] !== 'innovator') {
            $this->redirect('/dashboard');
        }
        require_once __DIR__ . '/../models/Sponsorship.php';
        $sponsorship = new Sponsorship();
        $sponsorships = $sponsorship->getForInnovator($currentUser['id']);
        $this->render('dashboard/innovator_sponsorships', [
            'sponsorships' => $sponsorships,
            'currentUser' => $currentUser
        ]);
    }

    // Update sponsorship status (for innovators)
    public function updateSponsorshipStatus() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        if ($currentUser['role'] !== 'innovator') {
            $this->redirect('/dashboard');
        }
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? null;
        if (!$id || !$status) {
            $this->setFlash('error', 'Invalid request.');
            $this->redirect('/my-sponsorships');
        }
        require_once __DIR__ . '/../models/Sponsorship.php';
        $sponsorship = new Sponsorship();
        // Optional: check that this sponsorship belongs to one of the user's innovations
        $all = $sponsorship->getForInnovator($currentUser['id']);
        $ids = array_column($all, 'id');
        if (!in_array($id, $ids)) {
            $this->setFlash('error', 'Unauthorized.');
            $this->redirect('/my-sponsorships');
        }
        $sponsorship->update($id, ['status' => $status]);
        $this->setFlash('success', 'Sponsorship status updated.');
        $this->redirect('/my-sponsorships');
    }
}
?> 