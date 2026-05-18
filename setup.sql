-- Run in phpMyAdmin before using the Expert module
-- Adds tables required by the Verified Expert role

CREATE TABLE IF NOT EXISTS session_questions (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    session_id    INT NOT NULL,
    submitter_id  INT NOT NULL,
    question_text TEXT NOT NULL,
    answer_text   TEXT DEFAULT NULL,
    is_answered   TINYINT(1) DEFAULT 0,
    submitted_at  DATETIME DEFAULT NOW()
);

-- Sample expert account (password: expert123)
-- Must first exist as member, then be approved by admin.
-- Shortcut: insert directly as expert for testing.
INSERT INTO users (name, username, email, password_hash, role, reputation, is_active, expert_domain, bio)
VALUES (
    'Test Expert',
    'expert1',
    'expert@forum.com',
    MD5('expert123'),
    'expert',
    100,
    1,
    'Web Development',
    'Experienced web developer specializing in PHP and MySQL.'
);
