<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/Analytics.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/Database.php';

class ReportExportController {
    private $analyticsModel;
    private $userModel;
    private $conn;

    public function __construct() {
        AuthMiddleware::checkAdmin();
        $this->analyticsModel = new Analytics();
        $this->userModel      = new User();
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Return compiled report data for the view
    public function index() {
        $data = array();
        $data['report'] = null;
        $data['month']  = '';
        $data['year']   = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $month = isset($_POST['month']) ? (int)$_POST['month'] : 0;
            $year  = isset($_POST['year'])  ? (int)$_POST['year']  : 0;

            if ($month < 1 || $month > 12 || $year < 2000) {
                $_SESSION['error'] = "Please enter a valid month (1-12) and year.";
            } else {
                $data['month']  = $month;
                $data['year']   = $year;
                $data['report'] = $this->compileReport($month, $year);
            }
        }

        return $data;
    }

    private function compileReport($month, $year) {
        $report = array();

        $from = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $to   = date('Y-m-t', strtotime($from)); // last day of month

        // New users this month
        $sql = "SELECT COUNT(*) AS cnt FROM users
                WHERE created_at BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from, $to);
        $stmt->execute();
        $report['new_users'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Questions this month
        $sql = "SELECT COUNT(*) AS cnt FROM questions
                WHERE created_at BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from, $to);
        $stmt->execute();
        $report['questions'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Answers this month
        $sql = "SELECT COUNT(*) AS cnt FROM answers
                WHERE created_at BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from, $to);
        $stmt->execute();
        $report['answers'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Reports processed this month
        $sql = "SELECT COUNT(*) AS cnt FROM reports
                WHERE status != 'pending' AND created_at BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from, $to);
        $stmt->execute();
        $report['reports_processed'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Warnings issued this month
        $sql = "SELECT COUNT(*) AS cnt FROM warnings
                WHERE created_at BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from, $to);
        $stmt->execute();
        $report['warnings'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Expert applications submitted this month
        $sql = "SELECT COUNT(*) AS cnt FROM expert_applications
                WHERE submitted_at BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from, $to);
        $stmt->execute();
        $report['applications'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Approved applications this month
        $sql = "SELECT COUNT(*) AS cnt FROM expert_applications
                WHERE status = 'approved' AND submitted_at BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $from, $to);
        $stmt->execute();
        $report['approved_apps'] = $stmt->get_result()->fetch_assoc()['cnt'];
        $stmt->close();

        // Top 5 most active users this month by questions + answers
        $sql = "SELECT u.name, u.username, u.role,
                       (SELECT COUNT(*) FROM questions q WHERE q.author_id = u.id AND q.created_at BETWEEN ? AND ?) +
                       (SELECT COUNT(*) FROM answers  a WHERE a.author_id = u.id AND a.created_at BETWEEN ? AND ?) AS activity_count
                FROM users u
                ORDER BY activity_count DESC
                LIMIT 5";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $from, $to, $from, $to);
        $stmt->execute();
        $result = $stmt->get_result();
        $report['top_users'] = array();
        while ($row = $result->fetch_assoc()) {
            $report['top_users'][] = $row;
        }
        $stmt->close();

        // Expert contributions: leaderboard from analytics
        $report['expert_contributions'] = $this->analyticsModel->getExpertContributions();

        return $report;
    }
}
?>
