<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config/Database.php';

class ReportGeneratorController {
    private $conn;

    public function __construct() {
        AuthMiddleware::checkModerator();
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function index() {
        $data = array();
        $data['report'] = null;
        $data['from_date'] = '';
        $data['to_date'] = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $from_date = isset($_POST['from_date']) ? trim($_POST['from_date']) : '';
            $to_date   = isset($_POST['to_date'])   ? trim($_POST['to_date'])   : '';

            if (empty($from_date) || empty($to_date)) {
                $_SESSION['error'] = "Both start and end dates are required.";
            } elseif ($from_date > $to_date) {
                $_SESSION['error'] = "Start date cannot be after end date.";
            } else {
                $data['from_date'] = $from_date;
                $data['to_date']   = $to_date;
                $data['report']    = $this->generateReport($from_date, $to_date);
            }
        }

        return $data;
    }

    private function generateReport($from_date, $to_date) {
        $report = array();

        // Total reports processed
        $sql = "SELECT COUNT(*) AS cnt FROM reports
                WHERE status != 'pending' AND created_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from_date, $to_date);
        $stmt->execute();
        $report['total_processed'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Dismissed reports
        $sql = "SELECT COUNT(*) AS cnt FROM reports
                WHERE status = 'dismissed' AND created_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from_date, $to_date);
        $stmt->execute();
        $report['dismissed'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Resolved reports
        $sql = "SELECT COUNT(*) AS cnt FROM reports
                WHERE status = 'resolved' AND created_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from_date, $to_date);
        $stmt->execute();
        $report['resolved'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Warnings issued in period
        $sql = "SELECT COUNT(*) AS cnt FROM warnings
                WHERE created_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from_date, $to_date);
        $stmt->execute();
        $report['warnings_issued'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Content deleted — tracked via moderator_note containing [deleted]
        $sql = "SELECT COUNT(*) AS cnt FROM reports
                WHERE status = 'resolved' AND moderator_note LIKE '%[deleted]%'
                AND created_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from_date, $to_date);
        $stmt->execute();
        $report['content_deleted'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Content edited — tracked via edit_history table
        $sql = "SELECT COUNT(*) AS cnt FROM edit_history
                WHERE edited_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from_date, $to_date);
        $stmt->execute();
        $report['content_edited'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Users suspended — tracked via suspension_log table
        $sql = "SELECT COUNT(DISTINCT user_id) AS cnt FROM suspension_log
                WHERE suspended_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from_date, $to_date);
        $stmt->execute();
        $report['users_suspended'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Actions taken breakdown by entity type
        $sql = "SELECT entity_type, COUNT(*) AS cnt FROM reports
                WHERE status = 'resolved' AND created_at BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)
                GROUP BY entity_type";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from_date, $to_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $report['actions_by_type'] = array();
        while ($row = $result->fetch_assoc()) {
            $report['actions_by_type'][$row['entity_type']] = $row['cnt'];
        }
        $stmt->close();

        return $report;
    }
}
?>
