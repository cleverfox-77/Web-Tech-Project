<?php
require_once __DIR__ . '/../config/Database.php';

class KbArticle {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Get all KB articles by this expert
    public function getByExpert($expert_id) {
        $sql = "SELECT k.id, k.title, k.body, k.view_count, k.created_at, k.updated_at,
                       t.name AS tag_name
                FROM knowledge_base_articles k
                LEFT JOIN tags t ON k.tag_id = t.id
                WHERE k.expert_id = ?
                ORDER BY k.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $expert_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $articles = array();
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
        $stmt->close();
        return $articles;
    }

    // Get single article
    public function getById($id) {
        $sql = "SELECT k.*, t.name AS tag_name FROM knowledge_base_articles k
                LEFT JOIN tags t ON k.tag_id = t.id
                WHERE k.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $article = $result->fetch_assoc();
        $stmt->close();
        return $article;
    }

    // Create a new KB article
    public function create($expert_id, $tag_id, $title, $body) {
        $sql = "INSERT INTO knowledge_base_articles (expert_id, title, body, tag_id, view_count, created_at, updated_at)
                VALUES (?, ?, ?, ?, 0, NOW(), NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issi", $expert_id, $title, $body, $tag_id);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    // Edit own article
    public function edit($id, $expert_id, $title, $body, $tag_id) {
        $sql = "UPDATE knowledge_base_articles SET title = ?, body = ?, tag_id = ?, updated_at = NOW()
                WHERE id = ? AND expert_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssiii", $title, $body, $tag_id, $id, $expert_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Delete own article
    public function delete($id, $expert_id) {
        $sql = "DELETE FROM knowledge_base_articles WHERE id = ? AND expert_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $expert_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Increment view count
    public function incrementViews($id) {
        $sql = "UPDATE knowledge_base_articles SET view_count = view_count + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    // Total views across all articles for this expert
    public function getTotalViews($expert_id) {
        $sql = "SELECT COALESCE(SUM(view_count), 0) AS total FROM knowledge_base_articles WHERE expert_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $expert_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row['total'];
    }
}
?>
