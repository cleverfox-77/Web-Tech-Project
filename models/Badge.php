<?php
require_once __DIR__ . '/../config/Database.php';

class Badge {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Get all badges with award counts
    public function getAllBadges() {
        $sql = "SELECT b.id, b.name, b.description, b.icon, b.threshold_type, b.threshold_value,
                       COUNT(ub.id) AS award_count
                FROM badges b
                LEFT JOIN user_badges ub ON b.id = ub.badge_id
                GROUP BY b.id, b.name, b.description, b.icon, b.threshold_type, b.threshold_value
                ORDER BY b.id ASC";
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

    // Add a new badge
    public function addBadge($name, $description, $icon, $threshold_type, $threshold_value) {
        $sql = "INSERT INTO badges (name, description, icon, threshold_type, threshold_value)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $description, $icon, $threshold_type, $threshold_value);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Manually award a badge to a user
    public function awardBadge($user_id, $badge_id) {
        // Prevent duplicate awards
        $sql = "SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $badge_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $stmt->close();
            return false; // already awarded
        }
        $stmt->close();

        $sql2 = "INSERT INTO user_badges (user_id, badge_id, awarded_at) VALUES (?, ?, NOW())";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("ii", $user_id, $badge_id);
        $result2 = $stmt2->execute();
        $stmt2->close();
        return $result2;
    }

    // Get badge by ID
    public function getBadgeById($id) {
        $sql = "SELECT * FROM badges WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $badge = $result->fetch_assoc();
        $stmt->close();
        return $badge;
    }

    // Get badges awarded to a specific user
    public function getUserBadges($user_id) {
        $sql = "SELECT b.name, b.description, b.icon, ub.awarded_at
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
}
?>
