<?php
require_once 'BaseModel.php';

class Category extends BaseModel {
    protected $table = 'categories';
    protected $fillable = ['name', 'slug', 'description', 'icon', 'is_active'];
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getActive() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getWithInnovationCount() {
        $sql = "SELECT c.*, COUNT(i.id) as innovation_count
                FROM categories c
                LEFT JOIN innovations i ON c.id = i.category_id AND i.status = 'published'
                WHERE c.is_active = 1
                GROUP BY c.id
                ORDER BY c.name ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function findBySlug($slug) {
        return $this->whereFirst('slug', $slug);
    }
    
    public function getInnovationsByCategory($categoryId, $page = 1) {
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM innovations WHERE category_id = :category_id AND status = 'published'";
        $total = $this->db->fetch($countSql, ['category_id' => $categoryId])['total'];
        
        // Get paginated data
        $sql = "SELECT i.*, c.name as category_name, c.icon as category_icon,
                       u.name as innovator_name, u.organization as innovator_org
                FROM innovations i
                JOIN categories c ON i.category_id = c.id
                JOIN users u ON i.user_id = u.id
                WHERE i.category_id = :category_id AND i.status = 'published'
                ORDER BY i.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $data = $this->db->fetchAll($sql, [
            'category_id' => $categoryId,
            'limit' => $perPage,
            'offset' => $offset
        ]);
        
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
}
?> 