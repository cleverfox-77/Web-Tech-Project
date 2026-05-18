<?php
require_once __DIR__ . '/../config/Database.php';

class QaSession {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Get all sessions by this expert
    public function getByExpert($expert_id) {
        $sql = "SELECT s.id, s.title, s.description, s.scheduled_at, s.duration_minutes,
                       s.status, s.max_questions,
                       (SELECT COUNT(*) FROM session_questions sq WHERE sq.session_id = s.id) AS question_count,
                       (SELECT COUNT(*) FROM session_questions sq WHERE sq.session_id = s.id AND sq.is_answered = 1) AS answered_count
                FROM qa_sessions s
                WHERE s.expert_id = ?
                ORDER BY s.scheduled_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $expert_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $sessions = array();
        while ($row = $result->fetch_assoc()) {
            $sessions[] = $row;
        }
        $stmt->close();
        return $sessions;
    }

    // Get single session
    public function getById($id) {
        $sql = "SELECT s.*, (SELECT COUNT(*) FROM session_questions sq WHERE sq.session_id = s.id) AS question_count
                FROM qa_sessions s WHERE s.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $session = $result->fetch_assoc();
        $stmt->close();
        return $session;
    }

    // Create a new session
    public function create($expert_id, $title, $description, $scheduled_at, $duration, $max_questions) {
        $sql = "INSERT INTO qa_sessions (expert_id, title, description, scheduled_at, duration_minutes, status, max_questions)
                VALUES (?, ?, ?, ?, ?, 'upcoming', ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssii", $expert_id, $title, $description, $scheduled_at, $duration, $max_questions);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    // Edit session (only before it starts)
    public function edit($id, $expert_id, $title, $description, $scheduled_at, $duration, $max_questions) {
        $sql = "UPDATE qa_sessions SET title = ?, description = ?, scheduled_at = ?,
                duration_minutes = ?, max_questions = ?
                WHERE id = ? AND expert_id = ? AND status = 'upcoming'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssiiii", $title, $description, $scheduled_at, $duration, $max_questions, $id, $expert_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Cancel session
    public function cancel($id, $expert_id) {
        $sql = "DELETE FROM qa_sessions WHERE id = ? AND expert_id = ? AND status = 'upcoming'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $expert_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Activate session (change to active)
    public function activate($id, $expert_id) {
        $sql = "UPDATE qa_sessions SET status = 'active' WHERE id = ? AND expert_id = ? AND status = 'upcoming'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $expert_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Close session (change to ended)
    public function close($id, $expert_id) {
        $sql = "UPDATE qa_sessions SET status = 'ended' WHERE id = ? AND expert_id = ? AND status = 'active'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $expert_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Get session questions
    public function getQuestions($session_id) {
        $sql = "SELECT sq.id, sq.question_text, sq.answer_text, sq.is_answered, sq.submitted_at,
                       u.username AS submitter_username
                FROM session_questions sq
                JOIN users u ON sq.submitter_id = u.id
                WHERE sq.session_id = ?
                ORDER BY sq.submitted_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = array();
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
        $stmt->close();
        return $questions;
    }

    // Answer a session question — saves answer text, marks is_answered
    public function answerQuestion($sq_id, $answer_text) {
        $sql = "UPDATE session_questions SET answer_text = ?, is_answered = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $answer_text, $sq_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Get total views on session questions (approximation: answered count)
    public function getTotalViews($expert_id) {
        $sql = "SELECT COUNT(*) AS total FROM session_questions sq
                JOIN qa_sessions s ON sq.session_id = s.id
                WHERE s.expert_id = ? AND sq.is_answered = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $expert_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row['total'];
    }
}
?>
