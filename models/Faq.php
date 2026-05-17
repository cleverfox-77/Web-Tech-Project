<?php
require_once __DIR__ . '/../config/Database.php';

class Faq {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Get all FAQs by this expert
    public function getByExpert($expert_id) {
        $sql = "SELECT f.id, f.title, f.body, f.view_count, f.is_pinned, f.created_at, f.updated_at,
                       t.name AS tag_name
                FROM faqs f
                LEFT JOIN tags t ON f.tag_id = t.id
                WHERE f.expert_id = ?
                ORDER BY f.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $expert_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $faqs = array();
        while ($row = $result->fetch_assoc()) {
            $faqs[] = $row;
        }
        $stmt->close();
        return $faqs;
    }

    // Get a single FAQ by ID
    public function getById($id) {
        $sql = "SELECT f.*, t.name AS tag_name FROM faqs f
                LEFT JOIN tags t ON f.tag_id = t.id
                WHERE f.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $faq = $result->fetch_assoc();
        $stmt->close();
        return $faq;
    }

    // Create a new FAQ
    public function create($expert_id, $tag_id, $title, $body) {
        $sql = "INSERT INTO faqs (expert_id, tag_id, title, body, view_count, is_pinned, created_at, updated_at)
                VALUES (?, ?, ?, ?, 0, 0, NOW(), NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiss", $expert_id, $tag_id, $title, $body);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    // Edit own FAQ
    public function edit($id, $expert_id, $title, $body, $tag_id) {
        $sql = "UPDATE faqs SET title = ?, body = ?, tag_id = ?, updated_at = NOW()
                WHERE id = ? AND expert_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssiii", $title, $body, $tag_id, $id, $expert_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Delete own FAQ
    public function delete($id, $expert_id) {
        $sql = "DELETE FROM faqs WHERE id = ? AND expert_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $expert_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Increment view count
    public function incrementViews($id) {
        $sql = "UPDATE faqs SET view_count = view_count + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    // Total views across all FAQs for this expert
    public function getTotalViews($expert_id) {
        $sql = "SELECT COALESCE(SUM(view_count), 0) AS total FROM faqs WHERE expert_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $expert_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row['total'];
    }
}
?>
