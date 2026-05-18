<?php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

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

    public function getUserById($id) {
        $sql = "SELECT id, name, username, email, bio, profile_pic, role, reputation, is_active, expert_domain, created_at
                FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    public function getUserActivityStats($user_id) {
        $stats = array();

        $sql = "SELECT COUNT(*) AS cnt FROM questions WHERE author_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stats['questions'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        $sql = "SELECT COUNT(*) AS cnt FROM answers WHERE author_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stats['answers'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        $sql = "SELECT COUNT(*) AS cnt FROM votes WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stats['votes'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        $sql = "SELECT COUNT(*) AS cnt FROM warnings WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stats['warnings'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        $sql = "SELECT COUNT(*) AS cnt FROM reports WHERE reporter_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stats['reports_made'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        return $stats;
    }

    public function updateRole($user_id, $role) {
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $role, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function suspendUser($user_id, $days) {
        $sql = "UPDATE users SET is_active = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        $sql2 = "INSERT INTO suspension_log (user_id, days, suspended_at) VALUES (?, ?, NOW())";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("ii", $user_id, $days);
        $result = $stmt2->execute();
        $stmt2->close();
        return $result;
    }

    public function getSuspendedCount($from_date, $to_date) {
        $sql = "SELECT COUNT(DISTINCT user_id) AS cnt FROM suspension_log
                WHERE suspended_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from_date, $to_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }

    public function activateUser($user_id) {
        $sql = "UPDATE users SET is_active = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function deactivateUser($user_id) {
        $sql = "UPDATE users SET is_active = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

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

    public function getTotalUsers() {
        $sql = "SELECT COUNT(*) AS cnt FROM users";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }

    public function getNewUsersThisWeek() {
        $sql = "SELECT COUNT(*) AS cnt FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }

    public function getQuestionsToday() {
        $sql = "SELECT COUNT(*) AS cnt FROM questions WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }

    public function getTotalQuestions() {
        $sql = "SELECT COUNT(*) AS cnt FROM questions";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }

    public function getTotalAnswers() {
        $sql = "SELECT COUNT(*) AS cnt FROM answers";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }

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
