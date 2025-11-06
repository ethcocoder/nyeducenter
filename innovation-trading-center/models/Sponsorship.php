<?php
require_once 'BaseModel.php';

class Sponsorship extends BaseModel {
    protected $table = 'sponsorships';
    protected $fillable = ['sponsor_id', 'innovation_id', 'amount', 'status'];

    public function __construct() {
        parent::__construct();
    }

    public function createSponsorship($sponsorId, $innovationId, $amount = null) {
        $data = [
            'sponsor_id' => $sponsorId,
            'innovation_id' => $innovationId,
            'amount' => $amount,
            'status' => 'pending',
        ];
        return $this->create($data);
    }

    public function hasSponsored($sponsorId, $innovationId) {
        $sql = "SELECT COUNT(*) as count FROM sponsorships WHERE sponsor_id = :sponsor_id AND innovation_id = :innovation_id";
        $result = $this->db->fetch($sql, ['sponsor_id' => $sponsorId, 'innovation_id' => $innovationId]);
        return $result['count'] > 0;
    }

    public function countBySponsor($sponsorId) {
        $sql = "SELECT COUNT(*) as total FROM sponsorships WHERE sponsor_id = :sponsor_id";
        $result = $this->db->fetch($sql, ['sponsor_id' => $sponsorId]);
        return $result['total'];
    }

    public function getForInnovator($innovatorId) {
        $sql = "SELECT s.*, i.title as innovation_title, u.name as sponsor_name
                FROM sponsorships s
                JOIN innovations i ON s.innovation_id = i.id
                JOIN users u ON s.sponsor_id = u.id
                WHERE i.user_id = :innovator_id
                ORDER BY s.created_at DESC";
        return $this->db->fetchAll($sql, ['innovator_id' => $innovatorId]);
    }
} 