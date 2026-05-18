<?php
require_once __DIR__ . '/../config/Database.php';

class Notification {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Get all notifications for a user
    public function getByUser($user_id) {
        $sql = "SELECT id, type, message, link, is_read, created_at
                FROM notifications
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 30";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = array();
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        $stmt->close();
        return $notifications;
    }

    // Count unread notifications
    public function countUnread($user_id) {
        $sql = "SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND is_read = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }

    // Mark one notification as read
    public function markRead($id, $user_id) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Mark all notifications as read
    public function markAllRead($user_id) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Create a notification
    public function create($user_id, $type, $message, $link) {
        $sql = "INSERT INTO notifications (user_id, type, message, link, is_read, created_at)
                VALUES (?, ?, ?, ?, 0, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $type, $message, $link);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
?>
