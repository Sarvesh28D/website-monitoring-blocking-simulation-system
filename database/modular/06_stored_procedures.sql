-- Website Monitoring System - Stored Procedures and Functions
-- Created: September 10, 2025
-- Author: Expert Software Engineer
-- Purpose: Reusable stored procedures and functions for common operations

USE website_monitoring;

DELIMITER $$

-- ============================================================================
-- STORED PROCEDURES
-- ============================================================================

-- Procedure to get user activity summary for a specific time period
CREATE PROCEDURE GetUserActivitySummary(
    IN p_user_id INT,
    IN p_start_date DATE,
    IN p_end_date DATE
)
BEGIN
    SELECT 
        p_user_id as user_id,
        COUNT(*) as total_visits,
        COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_visits,
        COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_visits,
        COUNT(CASE WHEN status = 'warned' THEN 1 END) as warned_visits,
        COUNT(DISTINCT site_name) as unique_sites_visited,
        COUNT(DISTINCT DATE(timestamp)) as active_days,
        ROUND(COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as block_percentage,
        ROUND(AVG(processing_time_ms), 2) as avg_processing_time_ms,
        SUM(bytes_transferred) as total_bytes_transferred,
        MIN(timestamp) as first_visit,
        MAX(timestamp) as last_visit
    FROM sites_visited 
    WHERE user_id = p_user_id 
    AND DATE(timestamp) BETWEEN p_start_date AND p_end_date;
END$$

-- Procedure to add a new blocked site
CREATE PROCEDURE AddBlockedSite(
    IN p_site_name VARCHAR(255),
    IN p_reason VARCHAR(500),
    IN p_category ENUM('social_media', 'streaming', 'gambling', 'adult_content', 'malware', 'phishing', 'p2p', 'gaming', 'dating', 'crypto', 'darkweb', 'productivity'),
    IN p_severity ENUM('low', 'medium', 'high', 'critical'),
    IN p_created_by VARCHAR(100)
)
BEGIN
    DECLARE site_exists INT DEFAULT 0;
    
    -- Check if site already exists
    SELECT COUNT(*) INTO site_exists 
    FROM blocked_sites 
    WHERE site_name = p_site_name;
    
    IF site_exists = 0 THEN
        INSERT INTO blocked_sites (site_name, reason, category, severity, created_by)
        VALUES (p_site_name, p_reason, p_category, p_severity, p_created_by);
        
        SELECT 'Site added successfully' as message, LAST_INSERT_ID() as site_id;
    ELSE
        SELECT 'Site already exists in blocked list' as message, -1 as site_id;
    END IF;
END$$

-- Procedure to update blocked site status
CREATE PROCEDURE UpdateBlockedSiteStatus(
    IN p_site_name VARCHAR(255),
    IN p_is_active BOOLEAN,
    IN p_updated_by VARCHAR(100)
)
BEGIN
    DECLARE site_exists INT DEFAULT 0;
    
    SELECT COUNT(*) INTO site_exists 
    FROM blocked_sites 
    WHERE site_name = p_site_name;
    
    IF site_exists > 0 THEN
        UPDATE blocked_sites 
        SET is_active = p_is_active, 
            updated_at = CURRENT_TIMESTAMP,
            created_by = p_updated_by
        WHERE site_name = p_site_name;
        
        SELECT 'Site status updated successfully' as message, ROW_COUNT() as affected_rows;
    ELSE
        SELECT 'Site not found in blocked list' as message, 0 as affected_rows;
    END IF;
END$$

-- Procedure to generate compliance report
CREATE PROCEDURE GenerateComplianceReport(
    IN p_start_date DATE,
    IN p_end_date DATE
)
BEGIN
    -- Summary statistics
    SELECT 
        'SUMMARY_STATISTICS' as report_section,
        COUNT(DISTINCT user_id) as total_users,
        COUNT(*) as total_visits,
        COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_visits,
        COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_visits,
        ROUND(COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as overall_block_rate,
        COUNT(DISTINCT site_name) as unique_sites_accessed
    FROM sites_visited 
    WHERE DATE(timestamp) BETWEEN p_start_date AND p_end_date
    
    UNION ALL
    
    -- Security incidents
    SELECT 
        'SECURITY_INCIDENTS' as report_section,
        COUNT(DISTINCT sv.user_id) as users_with_incidents,
        COUNT(*) as total_security_blocks,
        0 as unused_field1,
        0 as unused_field2,
        ROUND(COUNT(*) * 100.0 / (
            SELECT COUNT(*) FROM sites_visited 
            WHERE DATE(timestamp) BETWEEN p_start_date AND p_end_date
        ), 2) as security_incident_rate,
        COUNT(DISTINCT sv.site_name) as unique_security_threats
    FROM sites_visited sv
    INNER JOIN blocked_sites bs ON sv.site_name = bs.site_name
    WHERE sv.status = 'blocked' 
    AND bs.severity IN ('high', 'critical')
    AND DATE(sv.timestamp) BETWEEN p_start_date AND p_end_date;
END$$

-- Procedure to cleanup old log entries
CREATE PROCEDURE CleanupOldLogs(
    IN p_retention_days INT
)
BEGIN
    DECLARE deleted_count INT DEFAULT 0;
    
    DELETE FROM sites_visited 
    WHERE timestamp < DATE_SUB(CURRENT_DATE, INTERVAL p_retention_days DAY);
    
    SET deleted_count = ROW_COUNT();
    
    SELECT 
        'Log cleanup completed' as message,
        deleted_count as records_deleted,
        p_retention_days as retention_period_days;
END$$

-- ============================================================================
-- STORED FUNCTIONS
-- ============================================================================

-- Function to calculate user risk score (0-100)
CREATE FUNCTION CalculateUserRiskScore(p_user_id INT, p_days_back INT) 
RETURNS DECIMAL(5,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE risk_score DECIMAL(5,2) DEFAULT 0.0;
    DECLARE total_visits INT DEFAULT 0;
    DECLARE blocked_visits INT DEFAULT 0;
    DECLARE high_risk_blocks INT DEFAULT 0;
    DECLARE unique_blocked_sites INT DEFAULT 0;
    
    -- Get visit statistics
    SELECT 
        COUNT(*),
        COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END),
        COUNT(CASE WHEN sv.status = 'blocked' AND bs.severity IN ('high', 'critical') THEN 1 END),
        COUNT(DISTINCT CASE WHEN sv.status = 'blocked' THEN sv.site_name END)
    INTO total_visits, blocked_visits, high_risk_blocks, unique_blocked_sites
    FROM sites_visited sv
    LEFT JOIN blocked_sites bs ON sv.site_name = bs.site_name
    WHERE sv.user_id = p_user_id 
    AND sv.timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL p_days_back DAY);
    
    -- Calculate risk score based on various factors
    IF total_visits > 0 THEN
        -- Base risk from block percentage (0-40 points)
        SET risk_score = (blocked_visits * 100.0 / total_visits) * 0.4;
        
        -- Additional risk from high-severity blocks (0-30 points)
        SET risk_score = risk_score + (high_risk_blocks * 10);
        
        -- Additional risk from variety of blocked sites (0-30 points)
        SET risk_score = risk_score + (unique_blocked_sites * 2);
        
        -- Cap at 100
        IF risk_score > 100 THEN
            SET risk_score = 100;
        END IF;
    END IF;
    
    RETURN risk_score;
END$$

-- Function to get block rate for a specific time period
CREATE FUNCTION GetBlockRate(p_start_date DATE, p_end_date DATE) 
RETURNS DECIMAL(5,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE block_rate DECIMAL(5,2) DEFAULT 0.0;
    DECLARE total_visits INT DEFAULT 0;
    DECLARE blocked_visits INT DEFAULT 0;
    
    SELECT 
        COUNT(*),
        COUNT(CASE WHEN status = 'blocked' THEN 1 END)
    INTO total_visits, blocked_visits
    FROM sites_visited 
    WHERE DATE(timestamp) BETWEEN p_start_date AND p_end_date;
    
    IF total_visits > 0 THEN
        SET block_rate = blocked_visits * 100.0 / total_visits;
    END IF;
    
    RETURN block_rate;
END$$

-- Function to check if a site should be blocked
CREATE FUNCTION IsSiteBlocked(p_site_name VARCHAR(255)) 
RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE site_blocked BOOLEAN DEFAULT FALSE;
    DECLARE site_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO site_count
    FROM blocked_sites 
    WHERE site_name = p_site_name AND is_active = TRUE;
    
    IF site_count > 0 THEN
        SET site_blocked = TRUE;
    END IF;
    
    RETURN site_blocked;
END$$

-- Function to get site category
CREATE FUNCTION GetSiteCategory(p_site_name VARCHAR(255)) 
RETURNS VARCHAR(50)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE site_category VARCHAR(50) DEFAULT 'unknown';
    
    SELECT category INTO site_category
    FROM blocked_sites 
    WHERE site_name = p_site_name
    LIMIT 1;
    
    IF site_category IS NULL THEN
        SET site_category = 'allowed';
    END IF;
    
    RETURN site_category;
END$$

DELIMITER ;

-- ============================================================================
-- EXAMPLE USAGE QUERIES
-- ============================================================================

-- Examples of how to use the stored procedures and functions:

-- Get user activity summary
-- CALL GetUserActivitySummary(1, '2025-09-01', '2025-09-10');

-- Add a new blocked site
-- CALL AddBlockedSite('new-malware-site.com', 'Newly discovered malware distributor', 'malware', 'critical', 'security_admin');

-- Update blocked site status  
-- CALL UpdateBlockedSiteStatus('facebook.com', FALSE, 'admin');

-- Generate compliance report
-- CALL GenerateComplianceReport('2025-09-01', '2025-09-10');

-- Calculate user risk score
-- SELECT user_id, CalculateUserRiskScore(user_id, 30) as risk_score FROM (SELECT DISTINCT user_id FROM sites_visited) AS users;

-- Get overall block rate
-- SELECT GetBlockRate('2025-09-01', '2025-09-10') as block_rate;

-- Check if site is blocked
-- SELECT 'facebook.com' as site, IsSiteBlocked('facebook.com') as is_blocked;

-- Get site category
-- SELECT 'facebook.com' as site, GetSiteCategory('facebook.com') as category;
