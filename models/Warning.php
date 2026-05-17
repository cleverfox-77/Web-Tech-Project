<?php
require_once __DIR__ . '/../config/Database.php';

class Warning {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function issueWarning($user_id, $issued_by, $reason) {
        $sql = "INSERT INTO warnings (user_id, issued_by, reason, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $user_id, $issued_by, $reason);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getAllWarnings() {
        $sql = "SELECT w.id, w.reason, w.created_at,
                       u.username AS warned_username, u.name AS warned_name,
                       m.username AS mod_username
                FROM warnings w
                JOIN users u ON w.user_id = u.id
                JOIN users m ON w.issued_by = m.id
                ORDER BY w.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $warnings = array();
        while ($row = $result->fetch_assoc()) {
            $warnings[] = $row;
        }
        $stmt->close();
        return $warnings;
    }

    public function getWarningsByModerator($mod_id) {
        $sql = "SELECT w.id, w.reason, w.created_at,
                       u.username AS warned_username, u.name AS warned_name
                FROM warnings w
                JOIN users u ON w.user_id = u.id
                WHERE w.issued_by = ?
                ORDER BY w.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $mod_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $warnings = array();
        while ($row = $result->fetch_assoc()) {
            $warnings[] = $row;
        }
        $stmt->close();
        return $warnings;
    }

    public function getWarningsThisWeek() {
        $sql = "SELECT COUNT(*) AS cnt FROM warnings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }

    public function getWarningsByUser($user_id) {
        $sql = "SELECT w.id, w.reason, w.created_at, m.username AS mod_username
                FROM warnings w
                JOIN users m ON w.issued_by = m.id
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $warnings = array();
        while ($row = $result->fetch_assoc()) {
            $warnings[] = $row;
        }
        $stmt->close();
        return $warnings;
    }
}
?>
