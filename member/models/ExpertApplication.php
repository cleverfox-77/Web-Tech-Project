<?php
require_once __DIR__ . '/../config/Database.php';

class ExpertApplication {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Submit a new application
    public function create($user_id, $domain, $credentials, $motivation) {
        $sql = "INSERT INTO expert_applications (user_id, domain, credentials, motivation, status, submitted_at)
                VALUES (?, ?, ?, ?, 'pending', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $domain, $credentials, $motivation);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Get application status for a user
    public function getByUser($user_id) {
        $sql = "SELECT id, domain, status, submitted_at FROM expert_applications
                WHERE user_id = ? ORDER BY submitted_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $app = $result->fetch_assoc();
        $stmt->close();
        return $app;
    }

    // Check if user already has a pending application
    public function hasPending($user_id) {
        $sql = "SELECT id FROM expert_applications WHERE user_id = ? AND status = 'pending'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }
}
?>
