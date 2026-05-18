

<?php

require_once __DIR__ . '/../config/Database.php';

class ExpertApplication {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Get all pending applications
    public function getPendingApplications() {
        $sql = "SELECT ea.id, ea.domain, ea.credentials, ea.motivation, ea.status, ea.submitted_at,
                       u.name AS applicant_name, u.username AS applicant_username, u.email AS applicant_email
                FROM expert_applications ea
                JOIN users u ON ea.user_id = u.id
                WHERE ea.status = 'pending'
                ORDER BY ea.submitted_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $apps = array();
        while ($row = $result->fetch_assoc()) {
            $apps[] = $row;
        }
        $stmt->close();
        return $apps;
    }

    // Get all applications (all statuses)
    public function getAllApplications() {
        $sql = "SELECT ea.id, ea.domain, ea.credentials, ea.motivation, ea.status, ea.submitted_at,
                       u.name AS applicant_name, u.username AS applicant_username
                FROM expert_applications ea
                JOIN users u ON ea.user_id = u.id
                ORDER BY ea.submitted_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $apps = array();
        while ($row = $result->fetch_assoc()) {
            $apps[] = $row;
        }
        $stmt->close();
        return $apps;
    }

    // Get a single application by ID
    public function getApplicationById($id) {
        $sql = "SELECT ea.*, u.name AS applicant_name, u.username AS applicant_username,
                       u.email AS applicant_email, u.id AS user_id
                FROM expert_applications ea
                JOIN users u ON ea.user_id = u.id
                WHERE ea.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $app = $result->fetch_assoc();
        $stmt->close();
        return $app;
    }

    // Approve an application and promote user to expert
    public function approveApplication($app_id, $reviewed_by) {
        // Update application status
        $sql = "UPDATE expert_applications SET status = 'approved', reviewed_by = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $reviewed_by, $app_id);
        $stmt->execute();
        $stmt->close();

        // Get the user_id for this application
        $sql2 = "SELECT user_id FROM expert_applications WHERE id = ?";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bind_param("i", $app_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $row = $result2->fetch_assoc();
        $stmt2->close();

        if (!$row) {
            return false;
        }

        // Promote user role to expert
        $sql3 = "UPDATE users SET role = 'expert' WHERE id = ?";
        $stmt3 = $this->conn->prepare($sql3);
        $stmt3->bind_param("i", $row['user_id']);
        $result = $stmt3->execute();
        $stmt3->close();
        return $result;
    }

    // Reject an application with a reason
    public function rejectApplication($app_id, $reviewed_by, $reason = '') {
        $sql = "UPDATE expert_applications SET status = 'rejected', reviewed_by = ?, rejection_reason = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $reviewed_by, $reason, $app_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Count pending applications
    public function countPending() {
        $sql = "SELECT COUNT(*) AS cnt FROM expert_applications WHERE status = 'pending'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }
}
?>
