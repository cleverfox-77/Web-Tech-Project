<?php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Search users by name or username
    public function searchUsers($keyword) {
        $sql = "SELECT id, name, username, email, role, reputation, is_active, created_at
                FROM users
                WHERE name LIKE ? OR username LIKE ?
                ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $like = '%' . $keyword . '%';
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = array();
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        return $users;
    }

    // Get all users
    public function getAllUsers() {
        $sql = "SELECT id, name, username, email, role, reputation, is_active, created_at
                FROM users
                ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = array();
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        return $users;
    }

    // Get a single user by ID
    public function getUserById($id) {
        $sql = "SELECT id, name, username, email, bio, role, reputation, is_active, expert_domain, created_at
                FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    // Update user role (promote / demote)
    public function updateRole($user_id, $role) {
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $role, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Activate user (lift suspension)
    public function activateUser($user_id) {
        $sql = "UPDATE users SET is_active = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Deactivate user
    public function deactivateUser($user_id) {
        $sql = "UPDATE users SET is_active = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Count users grouped by role
    public function countByRole() {
        $sql = "SELECT role, COUNT(*) AS cnt FROM users GROUP BY role";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $counts = array();
        while ($row = $result->fetch_assoc()) {
            $counts[$row['role']] = $row['cnt'];
        }
        $stmt->close();
        return $counts;
    }

    // Count total users
    public function getTotalUsers() {
        $sql = "SELECT COUNT(*) AS cnt FROM users";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }

    // Count questions posted today
    public function getQuestionsToday() {
        $sql = "SELECT COUNT(*) AS cnt FROM questions WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }

    // Count total questions
    public function getTotalQuestions() {
        $sql = "SELECT COUNT(*) AS cnt FROM questions";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }

    // Count total answers
    public function getTotalAnswers() {
        $sql = "SELECT COUNT(*) AS cnt FROM answers";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }

    // Get reputation history for a user (votes received)
    public function getReputationHistory($user_id) {
        $sql = "SELECT v.value, v.entity_type, v.created_at
                FROM votes v
                JOIN questions q ON v.entity_type = 'question' AND v.entity_id = q.id AND q.author_id = ?
                UNION ALL
                SELECT v.value, v.entity_type, v.created_at
                FROM votes v
                JOIN answers a ON v.entity_type = 'answer' AND v.entity_id = a.id AND a.author_id = ?
                ORDER BY created_at DESC
                LIMIT 20";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $history = array();
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
        return $history;
    }
}
?>
