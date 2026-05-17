<?php
require_once __DIR__ . '/../config/Database.php';

class Tag {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllTagsWithStats() {
        $sql = "SELECT t.id, t.name, t.description, t.question_count,
                       COALESCE(SUM(q.view_count), 0) AS total_views
                FROM tags t
                LEFT JOIN question_tags qt ON t.id = qt.tag_id
                LEFT JOIN questions q ON qt.question_id = q.id
                GROUP BY t.id, t.name, t.description, t.question_count
                ORDER BY t.question_count DESC";
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

    public function addTag($name, $description, $created_by) {
        $sql = "INSERT INTO tags (name, description, question_count, created_by) VALUES (?, ?, 0, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $description, $created_by);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function renameTag($id, $name, $description) {
        $sql = "UPDATE tags SET name = ?, description = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $description, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function mergeTags($source_id, $dest_id) {
        // Retag all questions from source to dest, avoid duplicates
        $sql = "UPDATE question_tags SET tag_id = ?
                WHERE tag_id = ?
                AND question_id NOT IN (
                    SELECT question_id FROM (SELECT question_id FROM question_tags WHERE tag_id = ?) AS tmp
                )";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $dest_id, $source_id, $dest_id);
        $stmt->execute();
        $stmt->close();

        // Remove leftover duplicate rows for source
        $sql2 = "DELETE FROM question_tags WHERE tag_id = ?";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("i", $source_id);
        $stmt2->execute();
        $stmt2->close();

        // Update destination question_count
        $sql3 = "UPDATE tags SET question_count = (SELECT COUNT(*) FROM question_tags WHERE tag_id = ?) WHERE id = ?";
        $stmt3 = $this->conn->prepare($sql3);
        $stmt3->bind_param("ii", $dest_id, $dest_id);
        $stmt3->execute();
        $stmt3->close();

        // Delete source tag
        $sql4 = "DELETE FROM tags WHERE id = ?";
        $stmt4 = $this->conn->prepare($sql4);
        $stmt4->bind_param("i", $source_id);
        $result = $stmt4->execute();
        $stmt4->close();

        return $result;
    }

    public function deleteTag($id) {
        $sql = "DELETE FROM tags WHERE id = ? AND question_count = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected > 0;
    }

    public function getTagById($id) {
        $sql = "SELECT * FROM tags WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tag = $result->fetch_assoc();
        $stmt->close();
        return $tag;
    }
}
?>
