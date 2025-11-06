<?php
require_once 'BaseModel.php';

class Message extends BaseModel {
    protected $table = 'messages';
    protected $fillable = ['sender_id', 'receiver_id', 'receiver_type', 'innovation_id', 'subject', 'body'];
    
    public function __construct() {
        parent::__construct();
    }
    
    public function send($data) {
        $data['sent_at'] = date('Y-m-d H:i:s');
        // Remove created_at and updated_at if present
        unset($data['created_at'], $data['updated_at']);
        return $this->create($data);
    }
    
    public function getInbox($userId, $page = 1) {
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM messages WHERE receiver_id = :user_id";
        $total = $this->db->fetch($countSql, ['user_id' => $userId])['total'];
        
        // Get paginated data
        $sql = "SELECT m.*, s.name as sender_name, s.profile_image as sender_image,
                       i.title as innovation_title
                FROM messages m
                JOIN users s ON m.sender_id = s.id
                LEFT JOIN innovations i ON m.innovation_id = i.id
                WHERE m.receiver_id = :user_id
                ORDER BY m.sent_at DESC
                LIMIT :limit OFFSET :offset";
        
        $data = $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'limit' => $perPage,
            'offset' => $offset
        ]);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
    
    public function getSent($userId, $page = 1) {
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM messages WHERE sender_id = :user_id";
        $total = $this->db->fetch($countSql, ['user_id' => $userId])['total'];
        
        // Get paginated data
        $sql = "SELECT m.*, r.name as receiver_name, r.profile_image as receiver_image,
                       i.title as innovation_title
                FROM messages m
                JOIN users r ON m.receiver_id = r.id
                LEFT JOIN innovations i ON m.innovation_id = i.id
                WHERE m.sender_id = :user_id
                ORDER BY m.sent_at DESC
                LIMIT :limit OFFSET :offset";
        
        $data = $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'limit' => $perPage,
            'offset' => $offset
        ]);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
    
    public function getConversation($userId1, $userId2) {
        $sql = "SELECT m.*, s.name as sender_name, s.profile_image as sender_image,
                       r.name as receiver_name, r.profile_image as receiver_image,
                       i.title as innovation_title
                FROM messages m
                JOIN users s ON m.sender_id = s.id
                JOIN users r ON m.receiver_id = r.id
                LEFT JOIN innovations i ON m.innovation_id = i.id
                WHERE (m.sender_id = ? AND m.receiver_id = ?)
                   OR (m.sender_id = ? AND m.receiver_id = ?)
                ORDER BY m.sent_at ASC";
        return $this->db->fetchAll($sql, [$userId1, $userId2, $userId2, $userId1]);
    }
    
    public function markAsRead($messageId) {
        $sql = "UPDATE messages SET is_read = 1 WHERE id = :id";
        return $this->db->query($sql, ['id' => $messageId]);
    }
    
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = :user_id AND is_read = 0";
        $result = $this->db->fetch($sql, ['user_id' => $userId]);
        return $result['count'];
    }
    
    public function getRecentContacts($userId) {
        $sql = "SELECT DISTINCT 
                       CASE 
                           WHEN m.sender_id = :user_id THEN m.receiver_id
                           ELSE m.sender_id
                       END as contact_id,
                       CASE 
                           WHEN m.sender_id = :user_id THEN r.name
                           ELSE s.name
                       END as contact_name,
                       CASE 
                           WHEN m.sender_id = :user_id THEN r.profile_image
                           ELSE s.profile_image
                       END as contact_image,
                       MAX(m.sent_at) as last_message_time
                FROM messages m
                JOIN users s ON m.sender_id = s.id
                JOIN users r ON m.receiver_id = r.id
                WHERE m.sender_id = :user_id OR m.receiver_id = :user_id
                GROUP BY contact_id, contact_name, contact_image
                ORDER BY last_message_time DESC
                LIMIT 10";
        
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }
    
    public function countAll() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->fetch($sql);
        return $result['total'];
    }

    public function getAllWithAdminFilters($search = '', $page = 1) {
        $where = [];
        $params = [];
        if ($search) {
            $where[] = "(subject LIKE :search OR body LIKE :search)";
            $params['search'] = "%$search%";
        }
        $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $countSql = "SELECT COUNT(*) as total FROM messages $whereClause";
        $total = $this->db->fetch($countSql, $params)['total'];
        $sql = "SELECT m.*, s.name as sender_name, r.name as receiver_name FROM messages m JOIN users s ON m.sender_id = s.id JOIN users r ON m.receiver_id = r.id $whereClause ORDER BY m.sent_at DESC LIMIT :limit OFFSET :offset";
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

    // Get all private conversations for a user (fixed for current schema)
    public function getPrivateConversations($userId) {
        $sql = "
            SELECT
                u.id AS contact_id,
                u.name AS contact_name,
                u.profile_image AS contact_image,
                m.body AS last_message,
                m.sent_at AS last_time,
                SUM(CASE WHEN m2.is_read = 0 AND m2.receiver_id = ? THEN 1 ELSE 0 END) AS unread_count
            FROM (
                SELECT
                    CASE
                        WHEN sender_id = ? THEN receiver_id
                        ELSE sender_id
                    END AS contact_id,
                    MAX(sent_at) AS last_time
                FROM messages
                WHERE sender_id = ? OR receiver_id = ?
                GROUP BY contact_id
            ) AS conv
            JOIN users u ON u.id = conv.contact_id
            JOIN messages m ON (
                ((m.sender_id = ? AND m.receiver_id = conv.contact_id) OR (m.sender_id = conv.contact_id AND m.receiver_id = ?))
                AND m.sent_at = conv.last_time
            )
            LEFT JOIN messages m2 ON (
                ((m2.sender_id = ? AND m2.receiver_id = conv.contact_id) OR (m2.sender_id = conv.contact_id AND m2.receiver_id = ?))
            )
            GROUP BY u.id, u.name, u.profile_image, m.body, m.sent_at
            ORDER BY m.sent_at DESC
        ";
        return $this->db->fetchAll($sql, [
            $userId, // unread_count
            $userId, // CASE WHEN sender_id = ?
            $userId, // WHERE sender_id = ?
            $userId, // OR receiver_id = ?
            $userId, // m.sender_id = ?
            $userId, // m.receiver_id = ?
            $userId, // m2.sender_id = ?
            $userId  // m2.receiver_id = ?
        ]);
    }

    // Get all group conversations for a user
    public function getGroupConversations($userId) {
        $sql = "
            SELECT
                g.id AS group_id,
                g.name AS group_name,
                g.image AS group_image,
                g.created_at,
                (
                    SELECT m.body FROM messages m
                    WHERE m.receiver_id = g.id AND m.receiver_type = 'group'
                    ORDER BY m.sent_at DESC LIMIT 1
                ) AS last_message,
                (
                    SELECT m.sent_at FROM messages m
                    WHERE m.receiver_id = g.id AND m.receiver_type = 'group'
                    ORDER BY m.sent_at DESC LIMIT 1
                ) AS last_time,
                0 AS unread_count -- (optional: implement unread count)
            FROM groups g
            JOIN group_members gm ON g.id = gm.group_id
            WHERE gm.user_id = :user_id
            ORDER BY last_time DESC, g.created_at DESC
        ";
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }

    public function create($data) {
        // Only allow fillable fields
        $data = array_intersect_key($data, array_flip($this->fillable));
        // Do NOT add created_at or updated_at
        return $this->db->insert($this->table, $data);
    }

    // Create a new group
    public function createGroup($name, $creatorId, $imagePath = null) {
        $sql = "INSERT INTO groups (name, image, creator_id) VALUES (:name, :image, :creator_id)";
        $this->db->query($sql, [
            'name' => $name,
            'image' => $imagePath,
            'creator_id' => $creatorId
        ]);
        return $this->db->getConnection()->lastInsertId();
    }

    // Add members to a group
    public function addGroupMembers($groupId, $memberIds) {
        $sql = "INSERT INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)";
        foreach ($memberIds as $userId) {
            $this->db->query($sql, [
                'group_id' => $groupId,
                'user_id' => $userId
            ]);
        }
    }

    // Get group info
    public function getGroup($groupId) {
        $sql = "SELECT * FROM groups WHERE id = :id";
        return $this->db->fetch($sql, ['id' => $groupId]);
    }

    // Get group members
    public function getGroupMembers($groupId) {
        $sql = "SELECT u.* FROM users u JOIN group_members gm ON u.id = gm.user_id WHERE gm.group_id = :group_id";
        return $this->db->fetchAll($sql, ['group_id' => $groupId]);
    }

    // Get all messages for a group conversation
    public function getGroupConversation($groupId) {
        $sql = "SELECT m.*, u.name as sender_name, u.profile_image as sender_image
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.receiver_id = :group_id AND m.receiver_type = 'group'
                ORDER BY m.sent_at ASC";
        return $this->db->fetchAll($sql, ['group_id' => $groupId]);
    }
}
?> 