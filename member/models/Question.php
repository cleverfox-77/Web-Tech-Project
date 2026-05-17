<?php
require_once __DIR__ . '/../config/Database.php';

class Question {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Get questions by feed type: newest, unanswered, most_voted, trending
    public function getFeed($type, $user_id = 0) {
        if ($type == 'newest') {
            $sql = "SELECT q.id, q.title, q.body, q.vote_score, q.view_count, q.answer_count,
                           q.status, q.created_at, u.name AS author_name, u.username AS author_username,
                           u.reputation AS author_reputation
                    FROM questions q
                    JOIN users u ON q.author_id = u.id
                    ORDER BY q.created_at DESC LIMIT 20";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

        } elseif ($type == 'unanswered') {
            $sql = "SELECT q.id, q.title, q.body, q.vote_score, q.view_count, q.answer_count,
                           q.status, q.created_at, u.name AS author_name, u.username AS author_username,
                           u.reputation AS author_reputation
                    FROM questions q
                    JOIN users u ON q.author_id = u.id
                    WHERE q.answer_count = 0 AND q.status = 'open'
                    ORDER BY q.created_at DESC LIMIT 20";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

        } elseif ($type == 'most_voted') {
            $sql = "SELECT q.id, q.title, q.body, q.vote_score, q.view_count, q.answer_count,
                           q.status, q.created_at, u.name AS author_name, u.username AS author_username,
                           u.reputation AS author_reputation
                    FROM questions q
                    JOIN users u ON q.author_id = u.id
                    ORDER BY q.vote_score DESC LIMIT 20";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

        } elseif ($type == 'trending') {
            // Most activity in last 24 hours
            $sql = "SELECT q.id, q.title, q.body, q.vote_score, q.view_count, q.answer_count,
                           q.status, q.created_at, u.name AS author_name, u.username AS author_username,
                           u.reputation AS author_reputation
                    FROM questions q
                    JOIN users u ON q.author_id = u.id
                    WHERE q.updated_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    ORDER BY q.answer_count DESC, q.vote_score DESC LIMIT 20";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

        } else {
            return array();
        }

        $result = $stmt->get_result();
        $questions = array();
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
        $stmt->close();
        return $questions;
    }

    // Search questions by keyword with optional tag and status filters
    public function search($keyword, $tag = '', $status = '', $from_date = '', $to_date = '') {
        $sql = "SELECT q.id, q.title, q.vote_score, q.answer_count, q.status, q.created_at,
                       u.username AS author_username
                FROM questions q
                JOIN users u ON q.author_id = u.id
                WHERE (q.title LIKE ? OR q.body LIKE ?)";
        $params = array('%' . $keyword . '%', '%' . $keyword . '%');
        $types  = "ss";

        if (!empty($tag)) {
            $sql .= " AND q.id IN (SELECT qt.question_id FROM question_tags qt
                                   JOIN tags t ON qt.tag_id = t.id WHERE t.name = ?)";
            $params[] = $tag;
            $types   .= "s";
        }
        if (!empty($status)) {
            $sql .= " AND q.status = ?";
            $params[] = $status;
            $types   .= "s";
        }
        if (!empty($from_date)) {
            $sql .= " AND q.created_at >= ?";
            $params[] = $from_date;
            $types   .= "s";
        }
        if (!empty($to_date)) {
            $sql .= " AND q.created_at <= DATE_ADD(?, INTERVAL 1 DAY)";
            $params[] = $to_date;
            $types   .= "s";
        }
        $sql .= " ORDER BY q.created_at DESC LIMIT 30";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = array();
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
        $stmt->close();
        return $questions;
    }

    // Get single question by ID and increment view count
    public function getById($id) {
        $sql = "UPDATE questions SET view_count = view_count + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $sql = "SELECT q.*, u.name AS author_name, u.username AS author_username, u.reputation AS author_reputation
                FROM questions q JOIN users u ON q.author_id = u.id WHERE q.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $q = $result->fetch_assoc();
        $stmt->close();
        return $q;
    }

    // Get tags for a question
    public function getTags($question_id) {
        $sql = "SELECT t.id, t.name FROM tags t
                JOIN question_tags qt ON t.id = qt.tag_id
                WHERE qt.question_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $tags = array();
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
        $stmt->close();
        return $tags;
    }

    // Create a new question
    public function create($author_id, $title, $body) {
        $sql = "INSERT INTO questions (author_id, title, body, vote_score, view_count, answer_count,
                                      status, created_at, updated_at)
                VALUES (?, ?, ?, 0, 0, 0, 'open', NOW(), NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $author_id, $title, $body);
        $stmt->execute();
        $new_id = $stmt->insert_id;
        $stmt->close();
        return $new_id;
    }

    // Attach a tag to a question
    public function attachTag($question_id, $tag_id) {
        $sql = "INSERT IGNORE INTO question_tags (question_id, tag_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $question_id, $tag_id);
        $stmt->execute();
        $stmt->close();

        // Increment tag question count
        $sql2 = "UPDATE tags SET question_count = question_count + 1 WHERE id = ?";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("i", $tag_id);
        $stmt2->execute();
        $stmt2->close();
    }

    // Edit own question (blocked if answers exist)
    public function edit($id, $author_id, $title, $body) {
        $sql = "UPDATE questions SET title = ?, body = ?, updated_at = NOW()
                WHERE id = ? AND author_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssii", $title, $body, $id, $author_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Delete own question (blocked if answers exist)
    public function delete($id, $author_id) {
        // Check for answers
        $sql = "SELECT COUNT(*) AS cnt FROM answers WHERE question_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row['cnt'] > 0) {
            return false;
        }
        $sql = "DELETE FROM questions WHERE id = ? AND author_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $author_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Get questions by a specific author
    public function getByAuthor($author_id) {
        $sql = "SELECT id, title, vote_score, answer_count, status, created_at
                FROM questions WHERE author_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $author_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = array();
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
        $stmt->close();
        return $questions;
    }
}
?>
