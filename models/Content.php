<?php
require_once __DIR__ . '/../config/Database.php';

class Content {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getContentByReport($entity_type, $entity_id) {
        $content = null;
        if ($entity_type == 'question') {
            $sql = "SELECT q.id, q.title, q.body, q.status, q.author_id, u.username AS author_username
                    FROM questions q JOIN users u ON q.author_id = u.id WHERE q.id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $entity_id);
            $stmt->execute();
            $content = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } elseif ($entity_type == 'answer') {
            $sql = "SELECT a.id, a.body, a.author_id, a.question_id, u.username AS author_username
                    FROM answers a JOIN users u ON a.author_id = u.id WHERE a.id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $entity_id);
            $stmt->execute();
            $content = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } elseif ($entity_type == 'comment') {
            $sql = "SELECT c.id, c.body, c.author_id, c.entity_type, c.entity_id, u.username AS author_username
                    FROM comments c JOIN users u ON c.author_id = u.id WHERE c.id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $entity_id);
            $stmt->execute();
            $content = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
        return $content;
    }

    public function editQuestion($id, $title, $body, $edited_by) {
        // Record edit history before updating
        $this->recordEditHistory('question', $id, $edited_by);
        $sql = "UPDATE questions SET title = ?, body = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $body, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function editAnswer($id, $body, $edited_by) {
        // Record edit history before updating
        $this->recordEditHistory('answer', $id, $edited_by);
        $sql = "UPDATE answers SET body = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $body, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function editComment($id, $body, $edited_by) {
        $this->recordEditHistory('comment', $id, $edited_by);
        $sql = "UPDATE comments SET body = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $body, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Record that a moderator edited this entity
    private function recordEditHistory($entity_type, $entity_id, $edited_by) {
        $sql = "INSERT INTO edit_history (entity_type, entity_id, edited_by, edited_at)
                VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $entity_type, $entity_id, $edited_by);
        $stmt->execute();
        $stmt->close();
    }

    // Get edit history for a specific entity
    public function getEditHistory($entity_type, $entity_id) {
        $sql = "SELECT eh.edited_at, u.username AS editor_username
                FROM edit_history eh
                JOIN users u ON eh.edited_by = u.id
                WHERE eh.entity_type = ? AND eh.entity_id = ?
                ORDER BY eh.edited_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $entity_type, $entity_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $history = array();
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
        return $history;
    }

    public function deleteQuestion($id) {
        $sql = "SELECT accepted_answer_id FROM questions WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row && $row['accepted_answer_id'] !== null) {
            return false;
        }
        $sql2 = "DELETE FROM questions WHERE id = ?";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("i", $id);
        $result = $stmt2->execute();
        $stmt2->close();
        return $result;
    }

    public function deleteAnswer($id) {
        $sql = "DELETE FROM answers WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function deleteComment($id) {
        $sql = "DELETE FROM comments WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function closeQuestion($id, $reason) {
        $status = 'closed';
        $sql = "UPDATE questions SET status = ?, body = CONCAT(body, '\n\n[Closed: ', ?, ']'), updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $reason, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getEditsToday() {
        $sql = "SELECT COUNT(*) AS cnt FROM edit_history WHERE DATE(edited_at) = CURDATE()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }

    // Count content deleted in a date range (via resolved reports)
    public function getDeletedCount($from_date, $to_date) {
        $sql = "SELECT COUNT(*) AS cnt FROM reports
                WHERE status = 'resolved' AND moderator_note LIKE '%deleted%'
                AND created_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from_date, $to_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }
}
?>
