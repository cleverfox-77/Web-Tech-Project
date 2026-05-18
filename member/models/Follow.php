<?php
require_once __DIR__ . '/../config/Database.php';

class Follow {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Check if following
    public function isFollowing($follower_id, $entity_type, $entity_id) {
        $sql = "SELECT id FROM follows WHERE follower_id = ? AND entity_type = ? AND entity_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $follower_id, $entity_type, $entity_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    // Follow a user or tag
    public function follow($follower_id, $entity_type, $entity_id) {
        $sql = "INSERT IGNORE INTO follows (follower_id, entity_type, entity_id, created_at)
                VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $follower_id, $entity_type, $entity_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Unfollow
    public function unfollow($follower_id, $entity_type, $entity_id) {
        $sql = "DELETE FROM follows WHERE follower_id = ? AND entity_type = ? AND entity_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $follower_id, $entity_type, $entity_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Get tag IDs this user follows
    public function getFollowedTagIds($follower_id) {
        $sql = "SELECT entity_id FROM follows WHERE follower_id = ? AND entity_type = 'tag'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $follower_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $ids = array();
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['entity_id'];
        }
        $stmt->close();
        return $ids;
    }
}
?>
