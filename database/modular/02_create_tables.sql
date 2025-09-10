-- Website Monitoring System - Core Tables
-- Created: September 10, 2025
-- Author: Expert Software Engineer
-- Purpose: Define core database tables with proper indexing

USE website_monitoring;

-- Table to store blocked websites with reasons and metadata
CREATE TABLE blocked_sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(255) NOT NULL UNIQUE,
    reason VARCHAR(500) NOT NULL,
    category ENUM('social_media', 'streaming', 'gambling', 'adult_content', 'malware', 'phishing', 'p2p', 'gaming', 'dating', 'crypto', 'darkweb', 'productivity') DEFAULT 'productivity',
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100) DEFAULT 'system',
    
    -- Indexes for performance
    INDEX idx_site_name (site_name),
    INDEX idx_category (category),
    INDEX idx_severity (severity),
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at),
    
    -- Full-text search index for site names and reasons
    FULLTEXT idx_search (site_name, reason)
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Stores blocked websites with categorization and metadata';

-- Table to store user website visits with enhanced tracking
CREATE TABLE sites_visited (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    site_name VARCHAR(255) NOT NULL,
    url_path VARCHAR(1000) DEFAULT '/',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('allowed', 'blocked', 'warned', 'monitored') NOT NULL DEFAULT 'allowed',
    user_agent VARCHAR(500),
    ip_address VARCHAR(45),
    session_id VARCHAR(100),
    request_method ENUM('GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS') DEFAULT 'GET',
    response_code INT DEFAULT 200,
    bytes_transferred BIGINT DEFAULT 0,
    processing_time_ms INT DEFAULT 0,
    referrer VARCHAR(500),
    
    -- Indexes for optimal query performance
    PRIMARY KEY (id),
    INDEX idx_user_id (user_id),
    INDEX idx_site_name (site_name),
    INDEX idx_timestamp (timestamp),
    INDEX idx_status (status),
    INDEX idx_user_timestamp (user_id, timestamp),
    INDEX idx_site_timestamp (site_name, timestamp),
    INDEX idx_session_id (session_id),
    INDEX idx_ip_address (ip_address),
    INDEX idx_composite_analysis (user_id, site_name, status, timestamp),
    
    -- Foreign key constraint
    CONSTRAINT fk_blocked_site FOREIGN KEY (site_name) 
        REFERENCES blocked_sites(site_name) 
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
        
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Stores all user website visit attempts with detailed tracking';

-- Table for user sessions and activity tracking
CREATE TABLE user_sessions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(100) NOT NULL UNIQUE,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500),
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    total_visits INT DEFAULT 0,
    blocked_attempts INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_start_time (start_time),
    INDEX idx_is_active (is_active),
    INDEX idx_user_activity (user_id, last_activity)
    
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Tracks user sessions and activity patterns';
