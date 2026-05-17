<?php
require_once __DIR__ . '/../config/Database.php';

class Vote {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Get existing vote value (+1 or -1) or 0 if none
    public function getVote($user_id, $entity_type, $entity_id) {
        $sql = "SELECT value FROM votes WHERE user_id = ? AND entity_type = ? AND entity_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $user_id, $entity_type, $entity_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? $row['value'] : 0;
    }

    // Cast, toggle, or replace a vote — updates reputation accordingly
    public function castVote($user_id, $entity_type, $entity_id, $value) {
        $existing = $this->getVote($user_id, $entity_type, $entity_id);

        if ($existing == $value) {
            // Same vote — remove it (toggle off)
            $sql = "DELETE FROM votes WHERE user_id = ? AND entity_type = ? AND entity_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("isi", $user_id, $entity_type, $entity_id);
            $stmt->execute();
            $stmt->close();
            $delta = -$value;
            $rep_change = ($value == 1) ? -5 : 2; // undo the effect

        } elseif ($existing == 0) {
            // New vote
            $sql = "INSERT INTO votes (user_id, entity_type, entity_id, value, created_at)
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("isii", $user_id, $entity_type, $entity_id, $value);
            $stmt->execute();
            $stmt->close();
            $delta = $value;
            $rep_change = ($value == 1) ? 5 : -2;

        } else {
            // Opposite vote — replace
            $sql = "UPDATE votes SET value = ? WHERE user_id = ? AND entity_type = ? AND entity_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iisi", $value, $user_id, $entity_type, $entity_id);
            $stmt->execute();
            $stmt->close();
            $delta = $value - $existing; // e.g. +2 swing
            $rep_change = ($value == 1) ? 7 : -7;
        }

        // Update vote_score on the entity
        if ($entity_type == 'question') {
            $sql2 = "UPDATE questions SET vote_score = vote_score + ? WHERE id = ?";
        } else {
            $sql2 = "UPDATE answers SET vote_score = vote_score + ? WHERE id = ?";
        }
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("ii", $delta, $entity_id);
        $stmt2->execute();
        $stmt2->close();

        // Update author's reputation
        if ($entity_type == 'question') {
            $sql3 = "UPDATE users SET reputation = reputation + ? WHERE id = (SELECT author_id FROM questions WHERE id = ?)";
        } else {
            $sql3 = "UPDATE users SET reputation = reputation + ? WHERE id = (SELECT author_id FROM answers WHERE id = ?)";
        }
        $stmt3 = $this->conn->prepare($sql3);
        $stmt3->bind_param("ii", $rep_change, $entity_id);
        $stmt3->execute();
        $stmt3->close();

        return array('new_score' => $this->getNewScore($entity_type, $entity_id));
    }

    private function getNewScore($entity_type, $entity_id) {
        if ($entity_type == 'question') {
            $sql = "SELECT vote_score FROM questions WHERE id = ?";
        } else {
            $sql = "SELECT vote_score FROM answers WHERE id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $entity_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? $row['vote_score'] : 0;
    }
}
?>
