<?php
require_once __DIR__ . '/../config/Database.php';

class Report {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Submit a report
    public function create($reporter_id, $entity_type, $entity_id, $reason) {
        $sql = "INSERT INTO reports (reporter_id, entity_type, entity_id, reason, status, created_at)
                VALUES (?, ?, ?, ?, 'pending', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isis", $reporter_id, $entity_type, $entity_id, $reason);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

class Badge {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Get badges earned by a user
    public function getUserBadges($user_id) {
        $sql = "SELECT b.id, b.name, b.description, b.icon, b.threshold_type, b.threshold_value, ub.awarded_at
                FROM user_badges ub
                JOIN badges b ON ub.badge_id = b.id
                WHERE ub.user_id = ?
                ORDER BY ub.awarded_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $badges = array();
        while ($row = $result->fetch_assoc()) {
            $badges[] = $row;
        }
        $stmt->close();
        return $badges;
    }

    // Get all badges (for progress display)
    public function getAll() {
        $sql = "SELECT id, name, description, icon, threshold_type, threshold_value FROM badges ORDER BY threshold_value ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $badges = array();
        while ($row = $result->fetch_assoc()) {
            $badges[] = $row;
        }
        $stmt->close();
        return $badges;
    }
}
?>
