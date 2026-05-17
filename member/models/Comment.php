<?php
require_once __DIR__ . '/../config/Database.php';

class Comment {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Get comments for a question or answer
    public function getByEntity($entity_type, $entity_id) {
        $sql = "SELECT c.id, c.body, c.created_at, c.author_id,
                       u.name AS author_name, u.username AS author_username
                FROM comments c
                JOIN users u ON c.author_id = u.id
                WHERE c.entity_type = ? AND c.entity_id = ?
                ORDER BY c.created_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $entity_type, $entity_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $comments = array();
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
        $stmt->close();
        return $comments;
    }

    // Post a new comment
    public function create($entity_type, $entity_id, $author_id, $body) {
        $sql = "INSERT INTO comments (entity_type, entity_id, author_id, body, created_at)
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siis", $entity_type, $entity_id, $author_id, $body);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Delete own comment
    public function delete($id, $author_id) {
        $sql = "DELETE FROM comments WHERE id = ? AND author_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $author_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
?>
