<?php
require_once __DIR__ . '/../config/Database.php';

class AuditLog {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Write an entry to the audit log
    public function log($admin_id, $action_type, $target_type, $target_id, $reason = '') {
        $sql = "INSERT INTO audit_log (admin_id, action_type, target_type, target_id, reason, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issis", $admin_id, $action_type, $target_type, $target_id, $reason);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Retrieve full audit log with admin names
    public function getAll() {
        $sql = "SELECT al.id, al.action_type, al.target_type, al.target_id,
                       al.reason, al.created_at,
                       u.name AS admin_name, u.username AS admin_username
                FROM audit_log al
                JOIN users u ON al.admin_id = u.id
                ORDER BY al.created_at DESC
                LIMIT 200";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    // Count total log entries
    public function getCount() {
        $sql = "SELECT COUNT(*) AS cnt FROM audit_log";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }
}
?>
