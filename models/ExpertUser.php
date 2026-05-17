<?php
require_once __DIR__ . '/../config/Database.php';

class ExpertUser {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Login: username + password, must be expert role
    public function login($username, $password_hash) {
        $sql = "SELECT id, name, username, role, is_active, expert_domain, bio, reputation
                FROM users WHERE username = ? AND password_hash = ? AND role = 'expert'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password_hash);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    // Get full profile
    public function getById($id) {
        $sql = "SELECT id, name, username, email, bio, profile_pic, role, reputation,
                       expert_domain, is_active, created_at
                FROM users WHERE id = ? AND role = 'expert'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    // Update expert profile: bio, expert_domain
    public function updateProfile($id, $bio, $expert_domain) {
        $sql = "UPDATE users SET bio = ?, expert_domain = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $bio, $expert_domain, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

class Tag {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Get all tags (for dropdowns)
    public function getAll() {
        $sql = "SELECT id, name FROM tags ORDER BY name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $tags = array();
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
        $stmt->close();
        return $tags;
    }

    // Find tag by name
    public function findByName($name) {
        $sql = "SELECT id, name FROM tags WHERE name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $tag = $result->fetch_assoc();
        $stmt->close();
        return $tag;
    }
}
?>
