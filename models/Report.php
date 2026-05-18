<?php
require_once __DIR__ . '/../config/Database.php';

class Report {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getPendingReports() {
        $sql = "SELECT r.id, r.entity_type, r.entity_id, r.reason, r.created_at,
                       u.username AS reporter_username, u.name AS reporter_name,
                       r.status
                FROM reports r
                JOIN users u ON r.reporter_id = u.id
                WHERE r.status = 'pending'
                ORDER BY r.created_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $reports = array();
        while ($row = $result->fetch_assoc()) {
            $reports[] = $row;
        }
        $stmt->close();
        return $reports;
    }

    public function getReportById($id) {
        $sql = "SELECT r.*, u.username AS reporter_username, u.name AS reporter_name
                FROM reports r
                JOIN users u ON r.reporter_id = u.id
                WHERE r.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $report = $result->fetch_assoc();
        $stmt->close();
        return $report;
    }

    public function updateReportStatus($id, $status, $moderator_note) {
        $sql = "UPDATE reports SET status = ?, moderator_note = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $moderator_note, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getPendingCountByType() {
        $sql = "SELECT entity_type, COUNT(*) AS cnt
                FROM reports
                WHERE status = 'pending'
                GROUP BY entity_type";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $counts = array();
        while ($row = $result->fetch_assoc()) {
            $counts[$row['entity_type']] = $row['cnt'];
        }
        $stmt->close();
        return $counts;
    }

    public function getTotalPending() {
        $sql = "SELECT COUNT(*) AS cnt FROM reports WHERE status = 'pending'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }
}
?>
