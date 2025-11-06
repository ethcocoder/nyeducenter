<?php
require_once 'BaseModel.php';

class Innovation extends BaseModel {
    protected $table = 'innovations';
    protected $fillable = [
        'user_id', 'title', 'description', 'category_id', 'funding_needs',
        'funding_currency', 'location', 'stage', 'status', 'featured_image',
        'video_url', 'website_url', 'contact_email', 'contact_phone'
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getAllWithDetails($page = 1, $filters = []) {
        $whereClause = "WHERE 1=1";
        $params = [];
        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $whereClause .= " AND i.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }
        if (!isset($filters['user_id']) || $filters['user_id'] === '') {
            $whereClause .= " AND i.status = 'published'";
        }
        
        // Apply filters
        if (isset($filters['category']) && $filters['category'] !== '') {
            $whereClause .= " AND i.category_id = :category_id";
            $params['category_id'] = $filters['category'];
        }
        
        if (isset($filters['search']) && $filters['search'] !== '') {
            $whereClause .= " AND (i.title LIKE :search_title OR i.description LIKE :search_desc)";
            $params['search_title'] = "%{$filters['search']}%";
            $params['search_desc'] = "%{$filters['search']}%";
        }
        
        if (isset($filters['location']) && $filters['location'] !== '') {
            $whereClause .= " AND i.location LIKE :location";
            $params['location'] = "%{$filters['location']}%";
        }
        
        if (isset($filters['stage']) && $filters['stage'] !== '') {
            $whereClause .= " AND i.stage = :stage";
            $params['stage'] = $filters['stage'];
        }
        
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countParams = $params;
        unset($countParams['limit'], $countParams['offset']);
        $countSql = "SELECT COUNT(*) as total FROM innovations i $whereClause";
        error_log('COUNT SQL: ' . $countSql);
        error_log('COUNT PARAMS: ' . print_r($countParams, true));
        $total = $this->db->fetch($countSql, $countParams)['total'];
        
        // Get paginated data
        $sql = "SELECT i.*, c.name as category_name, c.icon as category_icon,
                       u.name as innovator_name, u.organization as innovator_org
                FROM innovations i
                JOIN categories c ON i.category_id = c.id
                JOIN users u ON i.user_id = u.id
                $whereClause
                ORDER BY i.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $data = $this->db->fetchAll($sql, $params);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }
    
    public function getByIdWithDetails($id) {
        $sql = "SELECT i.*, c.name as category_name, c.icon as category_icon,
                       u.name as innovator_name, u.organization as innovator_org,
                       u.bio as innovator_bio, u.profile_image as innovator_image
                FROM innovations i
                JOIN categories c ON i.category_id = c.id
                JOIN users u ON i.user_id = u.id
                WHERE i.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    public function getByUserId($userId) {
        $sql = "SELECT i.*, c.name as category_name
                FROM innovations i
                JOIN categories c ON i.category_id = c.id
                WHERE i.user_id = :user_id
                ORDER BY i.created_at DESC";
        
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }
    
    public function incrementViews($id) {
        $sql = "UPDATE innovations SET views_count = views_count + 1 WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);
        
        // Track view details
        $viewData = [
            'innovation_id' => $id,
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        $this->db->insert('innovation_views', $viewData);
    }
    
    public function getMedia($innovationId) {
        $sql = "SELECT * FROM innovation_media WHERE innovation_id = :innovation_id ORDER BY is_primary DESC, created_at ASC";
        return $this->db->fetchAll($sql, ['innovation_id' => $innovationId]);
    }
    
    public function addMedia($innovationId, $filePath, $fileName, $fileType, $fileSize, $isPrimary = false) {
        $data = [
            'innovation_id' => $innovationId,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'is_primary' => $isPrimary
        ];
        
        return $this->db->insert('innovation_media', $data);
    }
    
    public function getFeatured() {
        $sql = "SELECT i.*, c.name as category_name, c.icon as category_icon,
                       u.name as innovator_name, u.organization as innovator_org
                FROM innovations i
                JOIN categories c ON i.category_id = c.id
                JOIN users u ON i.user_id = u.id
                WHERE i.status = 'published'
                ORDER BY i.views_count DESC, i.created_at DESC
                LIMIT 6";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getByCategory($categoryId, $limit = 12) {
        $sql = "SELECT i.*, c.name as category_name, c.icon as category_icon,
                       u.name as innovator_name, u.organization as innovator_org
                FROM innovations i
                JOIN categories c ON i.category_id = c.id
                JOIN users u ON i.user_id = u.id
                WHERE i.category_id = :category_id AND i.status = 'published'
                ORDER BY i.created_at DESC
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, ['category_id' => $categoryId, 'limit' => $limit]);
    }
    
    public function search($query, $page = 1) {
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        $searchParams = ['query' => "%{$query}%"];
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM innovations i
                     JOIN categories c ON i.category_id = c.id
                     JOIN users u ON i.user_id = u.id
                     WHERE i.status = 'published' 
                     AND (i.title LIKE :query OR i.description LIKE :query 
                          OR c.name LIKE :query OR u.name LIKE :query)";
        
        $total = $this->db->fetch($countSql, $searchParams)['total'];
        
        // Get search results
        $sql = "SELECT i.*, c.name as category_name, c.icon as category_icon,
                       u.name as innovator_name, u.organization as innovator_org
                FROM innovations i
                JOIN categories c ON i.category_id = c.id
                JOIN users u ON i.user_id = u.id
                WHERE i.status = 'published' 
                AND (i.title LIKE :query OR i.description LIKE :query 
                     OR c.name LIKE :query OR u.name LIKE :query)
                ORDER BY i.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $searchParams['limit'] = $perPage;
        $searchParams['offset'] = $offset;
        
        $data = $this->db->fetchAll($sql, $searchParams);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'query' => $query
        ];
    }
    
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_innovations,
                    COUNT(CASE WHEN status = 'published' THEN 1 END) as published,
                    COUNT(CASE WHEN status = 'funded' THEN 1 END) as funded,
                    SUM(views_count) as total_views,
                    SUM(likes_count) as total_likes
                FROM innovations";
        
        return $this->db->fetch($sql);
    }
    
    public function countAll() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->fetch($sql);
        return $result['total'];
    }

    public function getAllWithAdminFilters($search = '', $status = '', $page = 1) {
        $where = [];
        $params = [];
        if ($search) {
            $where[] = "(i.title LIKE :search OR i.description LIKE :search)";
            $params['search'] = "%$search%";
        }
        if ($status) {
            $where[] = "i.status = :status";
            $params['status'] = $status;
        }
        $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        $countSql = "SELECT COUNT(*) as total FROM innovations i $whereClause";
        $total = $this->db->fetch($countSql, $params)['total'];
        $sql = "SELECT i.*, c.name as category_name, u.name as innovator_name, u.organization as innovator_org FROM innovations i JOIN categories c ON i.category_id = c.id JOIN users u ON i.user_id = u.id $whereClause ORDER BY i.created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        $data = $this->db->fetchAll($sql, $params);
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }

    public function getFavoritesByUser($userId) {
        $sql = "SELECT i.*, c.name as category_name, c.icon as category_icon,
                       u.name as innovator_name, u.organization as innovator_org
                FROM favorites f
                JOIN innovations i ON f.innovation_id = i.id
                JOIN categories c ON i.category_id = c.id
                JOIN users u ON i.user_id = u.id
                WHERE f.user_id = :user_id";
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }

    public function getDb() {
        return $this->db;
    }
}
?> 