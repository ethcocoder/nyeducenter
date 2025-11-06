<?php
require_once 'BaseModel.php';

class User extends BaseModel {
    protected $table = 'users';
    protected $fillable = [
        'name', 'email', 'password_hash', 'role', 'organization', 
        'bio', 'phone', 'location', 'profile_image', 'is_verified', 'is_active'
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    public function findByEmail($email) {
        return $this->whereFirst('email', $email);
    }
    
    public function createUser($data) {
        // Hash password
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        
        return $this->create($data);
    }
    
    public function updateUser($id, $data) {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        
        return $this->update($id, $data);
    }
    
    public function verifyPassword($email, $password) {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }
    
    public function getInnovators() {
        $sql = "SELECT * FROM {$this->table} WHERE role = 'innovator' AND is_active = 1 ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function getSponsors() {
        $sql = "SELECT * FROM {$this->table} WHERE role = 'sponsor' AND is_active = 1 ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function getAdmins() {
        $sql = "SELECT * FROM {$this->table} WHERE role = 'admin' AND is_active = 1 ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function getInnovations($userId) {
        $sql = "SELECT i.*, c.name as category_name 
                FROM innovations i 
                JOIN categories c ON i.category_id = c.id 
                WHERE i.user_id = :user_id 
                ORDER BY i.created_at DESC";
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }
    
    public function getFavorites($userId) {
        $sql = "SELECT i.*, c.name as category_name, u.name as innovator_name
                FROM favorites f
                JOIN innovations i ON f.innovation_id = i.id
                JOIN categories c ON i.category_id = c.id
                JOIN users u ON i.user_id = u.id
                WHERE f.user_id = :user_id
                ORDER BY f.created_at DESC";
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }
    
    public function getMessages($userId) {
        $sql = "SELECT m.*, 
                       s.name as sender_name, 
                       r.name as receiver_name,
                       i.title as innovation_title
                FROM messages m
                JOIN users s ON m.sender_id = s.id
                JOIN users r ON m.receiver_id = r.id
                LEFT JOIN innovations i ON m.innovation_id = i.id
                WHERE m.sender_id = :user_id OR m.receiver_id = :user_id
                ORDER BY m.sent_at DESC";
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }
    
    public function getUnreadMessageCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = :user_id AND is_read = 0";
        $result = $this->db->fetch($sql, ['user_id' => $userId]);
        return $result['count'];
    }
    
    public function isAdmin($userId) {
        $user = $this->find($userId);
        return $user && $user['role'] === 'admin';
    }
    
    public function isInnovator($userId) {
        $user = $this->find($userId);
        return $user && $user['role'] === 'innovator';
    }
    
    public function isSponsor($userId) {
        $user = $this->find($userId);
        return $user && $user['role'] === 'sponsor';
    }
    
    public function countAll() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->fetch($sql);
        return $result['total'];
    }
    
    public function getAllWithFilters($search = '', $role = '', $status = '', $page = 1) {
        $where = [];
        $params = [];
        if ($search) {
            $where[] = "(name LIKE :search OR email LIKE :search)";
            $params['search'] = "%$search%";
        }
        if ($role) {
            $where[] = "role = :role";
            $params['role'] = $role;
        }
        if ($status) {
            $where[] = "is_active = :is_active";
            $params['is_active'] = $status === 'active' ? 1 : 0;
        }
        $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} $whereClause";
        $total = $this->db->fetch($countSql, $params)['total'];
        $sql = "SELECT * FROM {$this->table} $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
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
    
    public function getAll() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }
}
?> 