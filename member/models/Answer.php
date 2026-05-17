<?php
require_once __DIR__ . '/../config/Database.php';

class Answer {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Get all answers for a question, accepted pinned at top
    public function getByQuestion($question_id) {
        $sql = "SELECT a.id, a.body, a.vote_score, a.is_accepted, a.is_expert_answer,
                       a.created_at, a.updated_at, a.author_id,
                       u.name AS author_name, u.username AS author_username,
                       u.reputation AS author_reputation, u.role AS author_role
                FROM answers a
                JOIN users u ON a.author_id = u.id
                WHERE a.question_id = ?
                ORDER BY a.is_accepted DESC, a.vote_score DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $answers = array();
        while ($row = $result->fetch_assoc()) {
            $answers[] = $row;
        }
        $stmt->close();
        return $answers;
    }

    // Post a new answer
    public function create($question_id, $author_id, $body) {
        $sql = "INSERT INTO answers (question_id, author_id, body, vote_score, is_accepted, is_expert_answer, created_at, updated_at)
                VALUES (?, ?, ?, 0, 0, 0, NOW(), NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $question_id, $author_id, $body);
        $stmt->execute();
        $new_id = $stmt->insert_id;
        $stmt->close();

        // Increment answer count on question
        $sql2 = "UPDATE questions SET answer_count = answer_count + 1, updated_at = NOW() WHERE id = ?";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("i", $question_id);
        $stmt2->execute();
        $stmt2->close();

        return $new_id;
    }

    // Edit own answer
    public function edit($id, $author_id, $body) {
        $sql = "UPDATE answers SET body = ?, updated_at = NOW() WHERE id = ? AND author_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $body, $id, $author_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Delete own answer
    public function delete($id, $author_id) {
        // Get question_id before deleting
        $sql = "SELECT question_id FROM answers WHERE id = ? AND author_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $author_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) {
            return false;
        }
        $question_id = $row['question_id'];

        $sql2 = "DELETE FROM answers WHERE id = ? AND author_id = ?";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("ii", $id, $author_id);
        $result = $stmt2->execute();
        $stmt2->close();

        if ($result) {
            $sql3 = "UPDATE questions SET answer_count = answer_count - 1 WHERE id = ? AND answer_count > 0";
            $stmt3 = $this->conn->prepare($sql3);
            $stmt3->bind_param("i", $question_id);
            $stmt3->execute();
            $stmt3->close();
        }
        return $result;
    }

    // Accept an answer on own question — awards +15 reputation to answerer
    public function accept($answer_id, $question_id, $question_author_id) {
        // Unaccept previous accepted answer if any
        $sql = "UPDATE answers SET is_accepted = 0 WHERE question_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $stmt->close();

        // Accept this answer
        $sql2 = "UPDATE answers SET is_accepted = 1 WHERE id = ?";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("i", $answer_id);
        $stmt2->execute();
        $stmt2->close();

        // Update accepted_answer_id on question
        $sql3 = "UPDATE questions SET accepted_answer_id = ? WHERE id = ? AND author_id = ?";
        $stmt3 = $this->conn->prepare($sql3);
        $stmt3->bind_param("iii", $answer_id, $question_id, $question_author_id);
        $stmt3->execute();
        $stmt3->close();

        // Award +15 reputation to the answerer
        $sql4 = "UPDATE users u
                 JOIN answers a ON a.id = ?
                 SET u.reputation = u.reputation + 15
                 WHERE u.id = a.author_id";
        $stmt4 = $this->conn->prepare($sql4);
        $stmt4->bind_param("i", $answer_id);
        $result = $stmt4->execute();
        $stmt4->close();
        return $result;
    }

    // Get a single answer by ID
    public function getById($id) {
        $sql = "SELECT * FROM answers WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $a = $result->fetch_assoc();
        $stmt->close();
        return $a;
    }
}
?>
