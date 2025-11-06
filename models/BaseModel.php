<?php
abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = ['password_hash'];
    
    public function __construct() {
        $this->db = db();
    }
    
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    public function all() {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function create($data) {
        // Only allow fillable fields
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        // Add timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert($this->table, $data);
    }
    
    public function update($id, $data) {
        // Only allow fillable fields
        $data = array_intersect_key($data, array_flip($this->fillable));
        
        // Add updated timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update($this->table, $data, "{$this->primaryKey} = :id", ['id' => $id]);
    }
    
    public function delete($id) {
        return $this->db->delete($this->table, "{$this->primaryKey} = :id", ['id' => $id]);
    }
    
    public function where($column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value";
        return $this->db->fetchAll($sql, ['value' => $value]);
    }
    
    public function whereFirst($column, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1";
        return $this->db->fetch($sql, ['value' => $value]);
    }
    
    public function paginate($page = 1, $perPage = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $total = $this->db->fetch($countSql)['total'];
        
        // Get paginated data
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $data = $this->db->fetchAll($sql, ['limit' => $perPage, 'offset' => $offset]);
        
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
    
    public function search($query, $columns = []) {
        if (empty($columns)) {
            $columns = $this->fillable;
        }
        
        $whereClause = [];
        $params = [];
        
        foreach ($columns as $column) {
            $whereClause[] = "{$column} LIKE :{$column}";
            $params[$column] = "%{$query}%";
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' OR ', $whereClause) . " ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getDb() {
        return $this->db;
    }
}
?> 