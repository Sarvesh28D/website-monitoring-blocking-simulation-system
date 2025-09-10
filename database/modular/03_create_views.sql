-- Website Monitoring System - Database Views
-- Created: September 10, 2025
-- Author: Expert Software Engineer  
-- Purpose: Create optimized views for common queries and reporting

USE website_monitoring;

-- View for user blocking statistics with enhanced metrics
CREATE OR REPLACE VIEW user_blocking_stats AS
SELECT 
    sv.user_id,
    COUNT(*) as total_visits,
    COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) as blocked_visits,
    COUNT(CASE WHEN sv.status = 'allowed' THEN 1 END) as allowed_visits,
    COUNT(CASE WHEN sv.status = 'warned' THEN 1 END) as warned_visits,
    COUNT(CASE WHEN sv.status = 'monitored' THEN 1 END) as monitored_visits,
    ROUND(COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as block_percentage,
    ROUND(COUNT(CASE WHEN sv.status IN ('blocked', 'warned') THEN 1 END) * 100.0 / COUNT(*), 2) as risk_percentage,
    COUNT(DISTINCT sv.site_name) as unique_sites,
    COUNT(DISTINCT DATE(sv.timestamp)) as active_days,
    MIN(sv.timestamp) as first_visit,
    MAX(sv.timestamp) as last_visit,
    ROUND(AVG(sv.processing_time_ms), 2) as avg_processing_time_ms,
    SUM(sv.bytes_transferred) as total_bytes_transferred
FROM sites_visited sv
GROUP BY sv.user_id;

-- View for daily browsing trends with comprehensive metrics  
CREATE OR REPLACE VIEW daily_browsing_trends AS
SELECT 
    DATE(sv.timestamp) as visit_date,
    DAYNAME(sv.timestamp) as day_name,
    COUNT(*) as total_visits,
    COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) as blocked_visits,
    COUNT(CASE WHEN sv.status = 'allowed' THEN 1 END) as allowed_visits,
    COUNT(CASE WHEN sv.status = 'warned' THEN 1 END) as warned_visits,
    COUNT(CASE WHEN sv.status = 'monitored' THEN 1 END) as monitored_visits,
    COUNT(DISTINCT sv.user_id) as active_users,
    COUNT(DISTINCT sv.site_name) as unique_sites,
    ROUND(AVG(sv.processing_time_ms), 2) as avg_processing_time_ms,
    SUM(sv.bytes_transferred) as total_bytes_transferred,
    ROUND(COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as daily_block_rate
FROM sites_visited sv
GROUP BY DATE(sv.timestamp)
ORDER BY visit_date DESC;

-- View for hourly activity patterns
CREATE OR REPLACE VIEW hourly_activity_patterns AS
SELECT 
    HOUR(sv.timestamp) as visit_hour,
    COUNT(*) as total_visits,
    COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) as blocked_visits,
    COUNT(CASE WHEN sv.status = 'allowed' THEN 1 END) as allowed_visits,
    COUNT(DISTINCT sv.user_id) as active_users,
    ROUND(COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as hourly_block_rate,
    ROUND(AVG(sv.processing_time_ms), 2) as avg_processing_time_ms
FROM sites_visited sv
GROUP BY HOUR(sv.timestamp)
ORDER BY visit_hour;

-- View for site blocking analysis
CREATE OR REPLACE VIEW site_blocking_analysis AS
SELECT 
    bs.site_name,
    bs.category,
    bs.severity,
    bs.reason,
    bs.is_active,
    COUNT(sv.id) as total_block_attempts,
    COUNT(DISTINCT sv.user_id) as affected_users,
    COUNT(DISTINCT DATE(sv.timestamp)) as blocked_days,
    MIN(sv.timestamp) as first_block_attempt,
    MAX(sv.timestamp) as last_block_attempt,
    ROUND(AVG(sv.processing_time_ms), 2) as avg_block_processing_time_ms
FROM blocked_sites bs
LEFT JOIN sites_visited sv ON bs.site_name = sv.site_name AND sv.status = 'blocked'
GROUP BY bs.id, bs.site_name, bs.category, bs.severity, bs.reason, bs.is_active
ORDER BY total_block_attempts DESC;

-- View for user session summary
CREATE OR REPLACE VIEW user_session_summary AS
SELECT 
    us.user_id,
    COUNT(DISTINCT us.session_id) as total_sessions,
    COUNT(DISTINCT us.ip_address) as unique_ip_addresses,
    SUM(us.total_visits) as total_visits_across_sessions,
    SUM(us.blocked_attempts) as total_blocked_attempts,
    ROUND(AVG(TIMESTAMPDIFF(MINUTE, us.start_time, COALESCE(us.end_time, us.last_activity))), 2) as avg_session_duration_minutes,
    MAX(us.last_activity) as last_activity_time,
    COUNT(CASE WHEN us.is_active = TRUE THEN 1 END) as active_sessions
FROM user_sessions us
GROUP BY us.user_id;

-- View for top blocked sites by category
CREATE OR REPLACE VIEW blocked_sites_by_category AS
SELECT 
    bs.category,
    COUNT(DISTINCT bs.site_name) as sites_in_category,
    COUNT(sv.id) as total_block_attempts,
    COUNT(DISTINCT sv.user_id) as affected_users,
    ROUND(AVG(sv.processing_time_ms), 2) as avg_processing_time_ms,
    bs.severity,
    COUNT(CASE WHEN bs.severity = 'critical' THEN 1 END) as critical_sites,
    COUNT(CASE WHEN bs.severity = 'high' THEN 1 END) as high_risk_sites,
    COUNT(CASE WHEN bs.severity = 'medium' THEN 1 END) as medium_risk_sites,
    COUNT(CASE WHEN bs.severity = 'low' THEN 1 END) as low_risk_sites
FROM blocked_sites bs
LEFT JOIN sites_visited sv ON bs.site_name = sv.site_name AND sv.status = 'blocked'
GROUP BY bs.category, bs.severity
ORDER BY total_block_attempts DESC;

-- View for recent activity dashboard
CREATE OR REPLACE VIEW recent_activity_dashboard AS
SELECT 
    sv.timestamp,
    sv.user_id,
    sv.site_name,
    sv.status,
    sv.ip_address,
    sv.user_agent,
    bs.category,
    bs.severity,
    bs.reason,
    sv.processing_time_ms,
    sv.bytes_transferred
FROM sites_visited sv
LEFT JOIN blocked_sites bs ON sv.site_name = bs.site_name
WHERE sv.timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY sv.timestamp DESC
LIMIT 1000;
