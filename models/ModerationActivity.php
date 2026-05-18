<?php
require_once __DIR__ . '/../config/Database.php';

class ModerationActivity {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // All warnings issued, with moderator info
    public function getAllWarnings() {
        $sql = "SELECT w.id, w.reason, w.created_at,
                       u.id AS user_id, u.name AS user_name, u.username AS user_username,
                       m.id AS mod_id, m.name AS mod_name, m.username AS mod_username
                FROM warnings w
                JOIN users u ON w.user_id = u.id
                JOIN users m ON w.issued_by = m.id
                ORDER BY w.created_at DESC";
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

    // All suspended (inactive) users with who last issued them a warning
    public function getSuspendedUsers() {
        $sql = "SELECT u.id, u.name, u.username, u.email, u.is_active,
                       (SELECT m.username FROM warnings w2
                        JOIN users m ON w2.issued_by = m.id
                        WHERE w2.user_id = u.id
                        ORDER BY w2.created_at DESC LIMIT 1) AS last_mod_username,
                       (SELECT w2.created_at FROM warnings w2
                        WHERE w2.user_id = u.id
                        ORDER BY w2.created_at DESC LIMIT 1) AS last_warning_at
                FROM users u
                WHERE u.is_active = 0
                ORDER BY u.id DESC";
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

    // All resolved/dismissed reports (content actions) with reporter info
    public function getContentActions() {
        $sql = "SELECT r.id, r.entity_type, r.entity_id, r.reason,
                       r.status, r.moderator_note, r.created_at,
                       u.username AS reporter_username
                FROM reports r
                JOIN users u ON r.reporter_id = u.id
                WHERE r.status != 'pending'
                ORDER BY r.created_at DESC
                LIMIT 100";
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

    // Lift a suspension (admin override)
    public function liftSuspension($user_id, $admin_id, $reason) {
        // Re-activate the user
        $sql = "UPDATE users SET is_active = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Log this override action
        $sql2 = "INSERT INTO audit_log (admin_id, action_type, target_type, target_id, reason, created_at)
                 VALUES (?, 'lift_suspension', 'user', ?, ?, NOW())";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("iis", $admin_id, $user_id, $reason);
        $result = $stmt2->execute();
        $stmt2->close();
        return $result;
    }

    // Reinstate deleted content (mark its report as pending again for review)
    public function reinstateContent($report_id, $admin_id, $reason) {
        $sql = "UPDATE reports SET status = 'pending', moderator_note = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $reason, $report_id);
        $stmt->execute();
        $stmt->close();

        // Log the override
        $sql2 = "INSERT INTO audit_log (admin_id, action_type, target_type, target_id, reason, created_at)
                 VALUES (?, 'reinstate_content', 'report', ?, ?, NOW())";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("iis", $admin_id, $report_id, $reason);
        $result = $stmt2->execute();
        $stmt2->close();
        return $result;
    }
}
?>
