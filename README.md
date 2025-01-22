At first create database table in phpmyadmin with this code:

-- Users table
CREATE TABLE collab_connect_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reset_token VARCHAR(64),
    token_expiry DATETIME,
    reputation INT DEFAULT 0,
    profile_picture VARCHAR(255),
    bio TEXT,
    skills TEXT
);

-- Announcements table
CREATE TABLE collab_connect_announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_pinned TINYINT(1) DEFAULT 0,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES collab_connect_users(id)
);

-- Teams table
CREATE TABLE collab_connect_teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    members_count INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES collab_connect_users(id)
);

-- Team Members table
CREATE TABLE collab_connect_team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    user_id INT,
    FOREIGN KEY (team_id) REFERENCES collab_connect_teams(id),
    FOREIGN KEY (user_id) REFERENCES collab_connect_users(id)
);

-- Team Tags table
CREATE TABLE collab_connect_team_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    tag_id INT,
    FOREIGN KEY (team_id) REFERENCES collab_connect_teams(id),
    FOREIGN KEY (tag_id) REFERENCES collab_connect_tags(id)
);

-- Tags table
CREATE TABLE collab_connect_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

-- Discussions table
CREATE TABLE collab_connect_discussions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES collab_connect_users(id)
);

-- Replies table
CREATE TABLE collab_connect_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discussion_id INT,
    user_id INT,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (discussion_id) REFERENCES collab_connect_discussions(id),
    FOREIGN KEY (user_id) REFERENCES collab_connect_users(id)
);

-- Questions table
CREATE TABLE collab_connect_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    upvotes INT DEFAULT 0,
    downvotes INT DEFAULT 0,
    FOREIGN KEY (created_by) REFERENCES collab_connect_users(id)
);

-- Answers table
CREATE TABLE collab_connect_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT,
    user_id INT,
    answer TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    upvotes INT DEFAULT 0,
    downvotes INT DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES collab_connect_questions(id),
    FOREIGN KEY (user_id) REFERENCES collab_connect_users(id)
);

-- Votes table
CREATE TABLE collab_connect_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT,
    user_id INT,
    vote TINYINT(4),
    FOREIGN KEY (question_id) REFERENCES collab_connect_questions(id),
    FOREIGN KEY (user_id) REFERENCES collab_connect_users(id)
);

-- Answer Votes table
CREATE TABLE collab_connect_answer_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    answer_id INT,
    user_id INT,
    vote INT,
    FOREIGN KEY (answer_id) REFERENCES collab_connect_answers(id),
    FOREIGN KEY (user_id) REFERENCES collab_connect_users(id)
);

-- Notifications table
CREATE TABLE collab_connect_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    sender_id INT,
    action_type ENUM('reply', 'answer', 'upvote', 'downvote', 'join_team', 'message', 'other'),
    related_id INT,
    message TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES collab_connect_users(id),
    FOREIGN KEY (sender_id) REFERENCES collab_connect_users(id)
);

-- Jobs table
CREATE TABLE collab_connect_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    company VARCHAR(255),
    location VARCHAR(100),
    skills TEXT
);

-- Job Applications table
CREATE TABLE collab_connect_job_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT,
    applicant_name VARCHAR(255),
    applicant_email VARCHAR(255),
    resume VARCHAR(255),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES collab_connect_jobs(id)
);

-- Messages table
CREATE TABLE collab_connect_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES collab_connect_users(id),
    FOREIGN KEY (receiver_id) REFERENCES collab_connect_users(id)
);

-- Question Tags table
CREATE TABLE collab_connect_question_tags (
    question_id INT,
    tag_id INT,
    PRIMARY KEY (question_id, tag_id),
    FOREIGN KEY (question_id) REFERENCES collab_connect_questions(id),
    FOREIGN KEY (tag_id) REFERENCES collab_connect_tags(id)
);

-- Job Applications table
CREATE TABLE collab_connect_job_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT,
    applicant_name VARCHAR(255),
    applicant_email VARCHAR(255),
    resume VARCHAR(255),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES collab_connect_jobs(id)
);
