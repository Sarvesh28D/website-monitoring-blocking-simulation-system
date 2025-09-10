-- Website Monitoring System - Analytical Queries
-- Created: September 10, 2025
-- Author: Expert Software Engineer
-- Purpose: Advanced analytical queries for reporting and insights

USE website_monitoring;

-- ============================================================================
-- QUERY SET 1: User Behavior Analysis
-- ============================================================================

-- Top 10 most visited sites per user in the last week
SELECT 
    user_id,
    site_name,
    COUNT(*) as visit_count,
    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_count,
    COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_count,
    ROUND(COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as block_percentage,
    AVG(processing_time_ms) as avg_processing_time,
    SUM(bytes_transferred) as total_bytes
FROM sites_visited 
WHERE timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
GROUP BY user_id, site_name
ORDER BY user_id, visit_count DESC;

-- User productivity risk assessment
SELECT 
    user_id,
    COUNT(*) as total_attempts,
    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_attempts,
    COUNT(CASE WHEN status IN ('blocked', 'warned') THEN 1 END) as risk_attempts,
    ROUND(COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as block_rate,
    ROUND(COUNT(CASE WHEN status IN ('blocked', 'warned') THEN 1 END) * 100.0 / COUNT(*), 2) as risk_rate,
    COUNT(DISTINCT site_name) as unique_sites_visited,
    COUNT(DISTINCT DATE(timestamp)) as active_days,
    CASE 
        WHEN COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*) > 30 THEN 'HIGH_RISK'
        WHEN COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*) > 15 THEN 'MEDIUM_RISK'
        WHEN COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*) > 5 THEN 'LOW_RISK'
        ELSE 'COMPLIANT'
    END as risk_category
FROM sites_visited
WHERE timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
GROUP BY user_id
ORDER BY block_rate DESC;

-- ============================================================================
-- QUERY SET 2: Site Blocking Effectiveness Analysis  
-- ============================================================================

-- Most frequently blocked sites with impact analysis
SELECT 
    bs.site_name,
    bs.category,
    bs.severity,
    bs.reason,
    COUNT(sv.id) as total_block_attempts,
    COUNT(DISTINCT sv.user_id) as affected_users,
    COUNT(DISTINCT DATE(sv.timestamp)) as blocked_days,
    ROUND(AVG(sv.processing_time_ms), 2) as avg_processing_time,
    MIN(sv.timestamp) as first_block_attempt,
    MAX(sv.timestamp) as last_block_attempt,
    ROUND(COUNT(sv.id) * 100.0 / (
        SELECT COUNT(*) FROM sites_visited WHERE status = 'blocked' 
        AND timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
    ), 2) as percentage_of_total_blocks
FROM blocked_sites bs
INNER JOIN sites_visited sv ON bs.site_name = sv.site_name 
WHERE sv.status = 'blocked' 
AND sv.timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
GROUP BY bs.site_name, bs.category, bs.severity, bs.reason
ORDER BY total_block_attempts DESC;

-- Blocking effectiveness by category
SELECT 
    bs.category,
    COUNT(DISTINCT bs.site_name) as sites_in_category,
    COUNT(sv.id) as total_block_attempts,
    COUNT(DISTINCT sv.user_id) as users_affected,
    ROUND(AVG(sv.processing_time_ms), 2) as avg_processing_time,
    ROUND(COUNT(sv.id) / COUNT(DISTINCT bs.site_name), 2) as avg_blocks_per_site,
    ROUND(COUNT(sv.id) / COUNT(DISTINCT sv.user_id), 2) as avg_blocks_per_user
FROM blocked_sites bs
LEFT JOIN sites_visited sv ON bs.site_name = sv.site_name AND sv.status = 'blocked'
WHERE sv.timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) OR sv.id IS NULL
GROUP BY bs.category
ORDER BY total_block_attempts DESC;

-- ============================================================================
-- QUERY SET 3: Time-based Trend Analysis
-- ============================================================================

-- Daily browsing patterns with detailed breakdown
SELECT 
    DATE(timestamp) as visit_date,
    DAYNAME(timestamp) as day_name,
    COUNT(*) as total_visits,
    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_visits,
    COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_visits,
    COUNT(CASE WHEN status = 'warned' THEN 1 END) as warned_visits,
    COUNT(DISTINCT user_id) as active_users,
    COUNT(DISTINCT site_name) as unique_sites,
    ROUND(AVG(processing_time_ms), 2) as avg_processing_time,
    SUM(bytes_transferred) as total_bandwidth_bytes,
    ROUND(COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as daily_block_rate
FROM sites_visited
WHERE timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
GROUP BY DATE(timestamp)
ORDER BY visit_date DESC;

-- Hourly activity patterns to identify peak usage times
SELECT 
    HOUR(timestamp) as hour_of_day,
    COUNT(*) as total_visits,
    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_visits,
    COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_visits,
    COUNT(DISTINCT user_id) as active_users,
    ROUND(AVG(processing_time_ms), 2) as avg_processing_time,
    ROUND(COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as hourly_block_rate,
    CASE 
        WHEN HOUR(timestamp) BETWEEN 9 AND 17 THEN 'Business Hours'
        WHEN HOUR(timestamp) BETWEEN 18 AND 22 THEN 'Evening'
        WHEN HOUR(timestamp) BETWEEN 23 AND 6 THEN 'Night'
        ELSE 'Early Morning'
    END as time_category
FROM sites_visited
WHERE timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
GROUP BY HOUR(timestamp)
ORDER BY hour_of_day;

-- ============================================================================
-- QUERY SET 4: Security and Compliance Reporting
-- ============================================================================

-- Critical security incidents (high/critical severity blocks)
SELECT 
    sv.timestamp,
    sv.user_id,
    sv.site_name,
    bs.category,
    bs.severity,
    bs.reason,
    sv.ip_address,
    sv.user_agent,
    sv.session_id
FROM sites_visited sv
INNER JOIN blocked_sites bs ON sv.site_name = bs.site_name
WHERE sv.status = 'blocked' 
AND bs.severity IN ('high', 'critical')
AND sv.timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
ORDER BY bs.severity DESC, sv.timestamp DESC;

-- Compliance summary report
SELECT 
    'Total Users' as metric,
    COUNT(DISTINCT user_id) as value,
    '' as details
FROM sites_visited
WHERE timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)

UNION ALL

SELECT 
    'Total Visits' as metric,
    COUNT(*) as value,
    CONCAT(COUNT(CASE WHEN status = 'allowed' THEN 1 END), ' allowed, ', 
           COUNT(CASE WHEN status = 'blocked' THEN 1 END), ' blocked') as details
FROM sites_visited
WHERE timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)

UNION ALL

SELECT 
    'Overall Block Rate' as metric,
    ROUND(COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as value,
    'Percentage of blocked requests' as details
FROM sites_visited
WHERE timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)

UNION ALL

SELECT 
    'Critical Security Incidents' as metric,
    COUNT(*) as value,
    'High/Critical severity blocks' as details
FROM sites_visited sv
INNER JOIN blocked_sites bs ON sv.site_name = bs.site_name
WHERE sv.status = 'blocked' 
AND bs.severity IN ('high', 'critical')
AND sv.timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY);

-- ============================================================================
-- QUERY SET 5: Performance and System Health
-- ============================================================================

-- System performance metrics
SELECT 
    DATE(timestamp) as report_date,
    COUNT(*) as total_requests,
    ROUND(AVG(processing_time_ms), 2) as avg_response_time_ms,
    MAX(processing_time_ms) as max_response_time_ms,
    MIN(processing_time_ms) as min_response_time_ms,
    ROUND(STDDEV(processing_time_ms), 2) as response_time_stddev,
    COUNT(CASE WHEN processing_time_ms > 1000 THEN 1 END) as slow_requests,
    ROUND(COUNT(CASE WHEN processing_time_ms > 1000 THEN 1 END) * 100.0 / COUNT(*), 2) as slow_request_percentage,
    ROUND(SUM(bytes_transferred) / 1024 / 1024, 2) as total_mb_transferred
FROM sites_visited
WHERE timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
GROUP BY DATE(timestamp)
ORDER BY report_date DESC;
