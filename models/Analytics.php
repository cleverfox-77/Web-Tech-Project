<?php
require_once __DIR__ . '/../config/Database.php';

class Analytics {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // Questions and answers per day for the last 7 days
    public function getDailyActivity() {
        $sql = "SELECT DATE(created_at) AS day, COUNT(*) AS question_count
                FROM questions
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY day ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = array();
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
        $stmt->close();

        $sql2 = "SELECT DATE(created_at) AS day, COUNT(*) AS answer_count
                 FROM answers
                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY day ASC";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $answers = array();
        while ($row = $result2->fetch_assoc()) {
            $answers[] = $row;
        }
        $stmt2->close();

        return array('questions' => $questions, 'answers' => $answers);
    }

    // User growth by role over the last 7 days
    public function getUserGrowth() {
        $sql = "SELECT DATE(created_at) AS day, role, COUNT(*) AS cnt
                FROM users
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at), role
                ORDER BY day ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $growth = array();
        while ($row = $result->fetch_assoc()) {
            $growth[] = $row;
        }
        $stmt->close();
        return $growth;
    }

    // Tag popularity: top 10 tags by question count
    public function getTagPopularity() {
        $sql = "SELECT name, question_count
                FROM tags
                ORDER BY question_count DESC
                LIMIT 10";
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

    // Top 20 reputation leaderboard
    public function getReputationLeaderboard() {
        $sql = "SELECT id, name, username, role, reputation
                FROM users
                ORDER BY reputation DESC
                LIMIT 20";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $leaderboard = array();
        while ($row = $result->fetch_assoc()) {
            $leaderboard[] = $row;
        }
        $stmt->close();
        return $leaderboard;
    }

    // Most active experts by answer count
    public function getMostActiveExperts() {
        $sql = "SELECT u.id, u.name, u.username, u.expert_domain,
                       COUNT(a.id) AS answer_count
                FROM users u
                LEFT JOIN answers a ON u.id = a.author_id
                WHERE u.role = 'expert'
                GROUP BY u.id, u.name, u.username, u.expert_domain
                ORDER BY answer_count DESC
                LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $experts = array();
        while ($row = $result->fetch_assoc()) {
            $experts[] = $row;
        }
        $stmt->close();
        return $experts;
    }

    // Most viewed questions
    public function getMostViewedQuestions() {
        $sql = "SELECT q.id, q.title, q.view_count, q.vote_score, q.answer_count,
                       u.username AS author_username
                FROM questions q
                JOIN users u ON q.author_id = u.id
                ORDER BY q.view_count DESC
                LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = array();
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
        $stmt->close();
        return $questions;
    }

    // Expert contribution report: FAQs, KB articles, sessions per expert
    public function getExpertContributions() {
        $sql = "SELECT u.id, u.name, u.username, u.expert_domain,
                       COUNT(DISTINCT f.id)   AS faq_count,
                       COUNT(DISTINCT k.id)   AS kb_count,
                       COUNT(DISTINCT qs.id)  AS session_count,
                       COALESCE(SUM(f.view_count), 0) + COALESCE(SUM(k.view_count), 0) AS total_views
                FROM users u
                LEFT JOIN faqs f ON u.id = f.expert_id
                LEFT JOIN knowledge_base_articles k ON u.id = k.expert_id
                LEFT JOIN qa_sessions qs ON u.id = qs.expert_id
                WHERE u.role = 'expert'
                GROUP BY u.id, u.name, u.username, u.expert_domain
                ORDER BY total_views DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $contributions = array();
        while ($row = $result->fetch_assoc()) {
            $contributions[] = $row;
        }
        $stmt->close();
        return $contributions;
    }

    // Count active Q&A sessions
    public function getActiveSessionCount() {
        $sql = "SELECT COUNT(*) AS cnt FROM qa_sessions WHERE status = 'active'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['cnt'];
    }
}
?>
