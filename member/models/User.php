<?php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Register a new member
    public function register($name, $username, $email, $password_hash) {
        $sql = "INSERT INTO users (name, username, email, password_hash, role, reputation, is_active, created_at)
                VALUES (?, ?, ?, ?, 'member', 0, 1, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $username, $email, $password_hash);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Check if username already exists (for live AJAX check)
    public function usernameExists($username) {
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    // Login: find user by username and password
    public function login($username, $password_hash) {
        $sql = "SELECT id, name, username, role, is_active FROM users
                WHERE username = ? AND password_hash = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password_hash);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    // Get full profile by ID
    public function getById($id) {
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

    // Update profile (bio and profile_pic)
    public function updateProfile($id, $bio, $profile_pic) {
        $sql = "UPDATE users SET bio = ?, profile_pic = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $bio, $profile_pic, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Update password
    public function updatePassword($id, $password_hash) {
        $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $password_hash, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Get activity stats: questions asked, answers posted, votes received
    public function getActivityStats($user_id) {
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

        $sql = "SELECT COALESCE(SUM(v.value), 0) AS total
                FROM votes v
                JOIN questions q ON v.entity_type = 'question' AND v.entity_id = q.id
                WHERE q.author_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $q_votes = $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        $sql = "SELECT COALESCE(SUM(v.value), 0) AS total
                FROM votes v
                JOIN answers a ON v.entity_type = 'answer' AND v.entity_id = a.id
                WHERE a.author_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $a_votes = $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        $stats['votes_received'] = $q_votes + $a_votes;
        return $stats;
    }

    // Get reputation history for this user
    public function getReputationHistory($user_id) {
        $sql = "SELECT v.value, v.entity_type, v.created_at
                FROM votes v
                JOIN questions q ON v.entity_type = 'question' AND v.entity_id = q.id AND q.author_id = ?
                UNION ALL
                SELECT v.value, v.entity_type, v.created_at
                FROM votes v
                JOIN answers a ON v.entity_type = 'answer' AND v.entity_id = a.id AND a.author_id = ?
                ORDER BY created_at DESC
                LIMIT 30";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $history = array();
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
        return $history;
    }
}
?>
