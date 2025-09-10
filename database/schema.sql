-- Website Monitoring & Blocking System Database Schema
-- Created: September 10, 2025
-- Author: Expert Software Engineer

-- Create database
CREATE DATABASE IF NOT EXISTS website_monitoring CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE website_monitoring;

-- Table to store blocked websites with reasons
CREATE TABLE blocked_sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(255) NOT NULL UNIQUE,
    reason VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_site_name (site_name)
) ENGINE=InnoDB;

-- Table to store user website visits
CREATE TABLE sites_visited (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    site_name VARCHAR(255) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('allowed', 'blocked') NOT NULL DEFAULT 'allowed',
    user_agent VARCHAR(500),
    ip_address VARCHAR(45),
    INDEX idx_user_id (user_id),
    INDEX idx_site_name (site_name),
    INDEX idx_timestamp (timestamp),
    INDEX idx_status (status),
    INDEX idx_user_timestamp (user_id, timestamp)
) ENGINE=InnoDB;

-- Sample data for blocked sites
INSERT INTO blocked_sites (site_name, reason) VALUES
('facebook.com', 'Social media blocked during work hours'),
('twitter.com', 'Social media - productivity policy'),
('instagram.com', 'Social media - company policy'),
('youtube.com', 'Video streaming - bandwidth conservation'),
('netflix.com', 'Streaming service - workplace policy'),
('gambling-site.com', 'Gambling content - company ethics policy'),
('adult-content.com', 'Adult content - inappropriate for workplace'),
('malicious-site.com', 'Known malware distributor - security threat'),
('phishing-site.com', 'Phishing attempts - security policy'),
('torrent-site.com', 'P2P file sharing - legal compliance'),
('gaming-site.com', 'Online gaming - productivity policy'),
('dating-site.com', 'Dating platforms - workplace appropriateness'),
('crypto-gambling.com', 'Cryptocurrency gambling - financial policy'),
('darkweb-market.com', 'Dark web marketplace - security and legal concerns'),
('time-waster.com', 'Identified time-wasting site - productivity optimization');

-- Create a view for quick statistics
CREATE VIEW user_blocking_stats AS
SELECT 
    user_id,
    COUNT(*) as total_visits,
    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_visits,
    COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_visits,
    ROUND(COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as block_percentage
FROM sites_visited 
GROUP BY user_id;

-- Create a view for daily trends
CREATE VIEW daily_browsing_trends AS
SELECT 
    DATE(timestamp) as visit_date,
    COUNT(*) as total_visits,
    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_visits,
    COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_visits
FROM sites_visited 
GROUP BY DATE(timestamp)
ORDER BY visit_date DESC;
