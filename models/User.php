<?php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getUserById($id) {
        $sql = "SELECT id, name, username, email, bio, profile_pic, role, reputation, is_active, created_at
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

    public function suspendUser($user_id, $days) {
        // Mark inactive
        $sql = "UPDATE users SET is_active = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Log the suspension with duration
        $sql2 = "INSERT INTO suspension_log (user_id, days, suspended_at) VALUES (?, ?, NOW())";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("ii", $user_id, $days);
        $result = $stmt2->execute();
        $stmt2->close();
        return $result;
    }

    // Count distinct users suspended in a date range
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

    public function getNewUsersThisWeek() {
        $sql = "SELECT COUNT(*) AS cnt FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }
}
?>
