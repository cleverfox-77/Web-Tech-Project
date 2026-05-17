<?php
require_once __DIR__ . '/../config/Database.php';

class Tag {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Search tags by prefix for autocomplete
    public function search($prefix) {
        $sql = "SELECT id, name, question_count FROM tags WHERE name LIKE ? ORDER BY question_count DESC LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        $like = $prefix . '%';
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $result = $stmt->get_result();
        $tags = array();
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
        $stmt->close();
        return $tags;
    }

    // Find tag by exact name
    public function findByName($name) {
        $sql = "SELECT id, name FROM tags WHERE name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $tag = $result->fetch_assoc();
        $stmt->close();
        return $tag;
    }

    // Get all tags (for follow page)
    public function getAll() {
        $sql = "SELECT id, name, description, question_count FROM tags ORDER BY question_count DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $tags = array();
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
        $stmt->close();
        return $tags;
    }
}
?>
