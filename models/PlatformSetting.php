<?php
require_once __DIR__ . '/../config/Database.php';

class PlatformSetting {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Get a setting value by key
    public function getSetting($key) {
        $sql = "SELECT setting_value FROM platform_settings WHERE setting_key = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? $row['setting_value'] : null;
    }

    // Get all settings as key => value array
    public function getAllSettings() {
        $sql = "SELECT setting_key, setting_value FROM platform_settings";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $settings = array();
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $stmt->close();
        return $settings;
    }

    // Update or insert a setting
    public function upsertSetting($key, $value) {
        $sql = "INSERT INTO platform_settings (setting_key, setting_value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $key, $value);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // Post a system announcement stored as a setting
    public function postAnnouncement($message) {
        return $this->upsertSetting('announcement', $message);
    }

    // Get current announcement
    public function getAnnouncement() {
        return $this->getSetting('announcement');
    }

    // Get all featured content IDs (stored as CSV in settings)
    public function getFeaturedQuestions() {
        $val = $this->getSetting('featured_questions');
        return $val ? explode(',', $val) : array();
    }

    // Set featured question IDs
    public function setFeaturedQuestions($ids_csv) {
        return $this->upsertSetting('featured_questions', $ids_csv);
    }

    // Get featured FAQ IDs
    public function getFeaturedFaqs() {
        $val = $this->getSetting('featured_faqs');
        return $val ? explode(',', $val) : array();
    }

    // Set featured FAQ IDs
    public function setFeaturedFaqs($ids_csv) {
        return $this->upsertSetting('featured_faqs', $ids_csv);
    }

    // Get featured KB article IDs
    public function getFeaturedKbArticles() {
        $val = $this->getSetting('featured_kb_articles');
        return $val ? explode(',', $val) : array();
    }

    // Set featured KB article IDs
    public function setFeaturedKbArticles($ids_csv) {
        return $this->upsertSetting('featured_kb_articles', $ids_csv);
    }
}
?>
