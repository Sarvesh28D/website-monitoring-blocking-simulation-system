-- Website Monitoring System - Advanced Queries
-- Created: September 10, 2025

-- ============================================================================
-- QUERY 1: Weekly top 10 visited sites per user
-- ============================================================================
-- This query shows the most visited sites by each user in the past week
SELECT 
    user_id,
    site_name,
    COUNT(*) as visit_count,
    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_count,
    COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_count,
    ROUND(COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as block_percentage
FROM sites_visited 
WHERE timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
GROUP BY user_id, site_name
ORDER BY user_id, visit_count DESC
LIMIT 10;

-- Alternative query with user ranking
SELECT 
    user_id,
    site_name,
    visit_count,
    blocked_count,
    allowed_count,
    block_percentage,
    user_rank
FROM (
    SELECT 
        user_id,
        site_name,
        COUNT(*) as visit_count,
        COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_count,
        COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_count,
        ROUND(COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as block_percentage,
        ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY COUNT(*) DESC) as user_rank
    FROM sites_visited 
    WHERE timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
    GROUP BY user_id, site_name
) ranked_sites
WHERE user_rank <= 10
ORDER BY user_id, user_rank;

-- ============================================================================
-- QUERY 2: Number of blocked attempts per user
-- ============================================================================
-- Comprehensive blocked attempts analysis per user
SELECT 
    sv.user_id,
    COUNT(*) as total_blocked_attempts,
    COUNT(DISTINCT sv.site_name) as unique_blocked_sites,
    GROUP_CONCAT(DISTINCT sv.site_name ORDER BY sv.site_name SEPARATOR ', ') as blocked_sites_list,
    MIN(sv.timestamp) as first_blocked_attempt,
    MAX(sv.timestamp) as last_blocked_attempt,
    DATEDIFF(MAX(sv.timestamp), MIN(sv.timestamp)) as days_between_first_last
FROM sites_visited sv
WHERE sv.status = 'blocked'
GROUP BY sv.user_id
ORDER BY total_blocked_attempts DESC;

-- Blocked attempts with reasons
SELECT 
    sv.user_id,
    sv.site_name,
    COUNT(*) as blocked_attempts,
    bs.reason as block_reason,
    MIN(sv.timestamp) as first_attempt,
    MAX(sv.timestamp) as last_attempt
FROM sites_visited sv
INNER JOIN blocked_sites bs ON sv.site_name = bs.site_name
WHERE sv.status = 'blocked'
GROUP BY sv.user_id, sv.site_name, bs.reason
ORDER BY sv.user_id, blocked_attempts DESC;

-- ============================================================================
-- QUERY 3: Daily browsing trend analysis
-- ============================================================================
-- Comprehensive daily browsing trends with moving averages
SELECT 
    visit_date,
    total_visits,
    blocked_visits,
    allowed_visits,
    ROUND(blocked_visits * 100.0 / total_visits, 2) as daily_block_rate,
    AVG(total_visits) OVER (
        ORDER BY visit_date 
        ROWS BETWEEN 6 PRECEDING AND CURRENT ROW
    ) as weekly_avg_visits,
    AVG(blocked_visits) OVER (
        ORDER BY visit_date 
        ROWS BETWEEN 6 PRECEDING AND CURRENT ROW
    ) as weekly_avg_blocks
FROM (
    SELECT 
        DATE(timestamp) as visit_date,
        COUNT(*) as total_visits,
        COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_visits,
        COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_visits
    FROM sites_visited 
    GROUP BY DATE(timestamp)
) daily_stats
ORDER BY visit_date DESC
LIMIT 30;

-- Hourly browsing pattern analysis
SELECT 
    HOUR(timestamp) as hour_of_day,
    COUNT(*) as total_visits,
    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_visits,
    COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_visits,
    ROUND(COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as hourly_block_rate
FROM sites_visited 
WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY HOUR(timestamp)
ORDER BY hour_of_day;

-- ============================================================================
-- ADDITIONAL USEFUL QUERIES
-- ============================================================================

-- Most problematic users (highest block rates)
SELECT 
    user_id,
    COUNT(*) as total_attempts,
    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_attempts,
    ROUND(COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as block_percentage
FROM sites_visited 
GROUP BY user_id
HAVING COUNT(*) >= 10  -- Only users with significant activity
ORDER BY block_percentage DESC, blocked_attempts DESC
LIMIT 20;

-- Most frequently blocked sites
SELECT 
    bs.site_name,
    bs.reason,
    COUNT(sv.id) as block_attempts,
    COUNT(DISTINCT sv.user_id) as affected_users,
    MIN(sv.timestamp) as first_blocked,
    MAX(sv.timestamp) as last_blocked
FROM blocked_sites bs
LEFT JOIN sites_visited sv ON bs.site_name = sv.site_name AND sv.status = 'blocked'
GROUP BY bs.site_name, bs.reason
ORDER BY block_attempts DESC;

-- Weekly comparison query
SELECT 
    'This Week' as period,
    COUNT(*) as total_visits,
    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_visits,
    COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_visits
FROM sites_visited 
WHERE timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)

UNION ALL

SELECT 
    'Last Week' as period,
    COUNT(*) as total_visits,
    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_visits,
    COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_visits
FROM sites_visited 
WHERE timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 14 DAY) 
  AND timestamp < DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY);

-- Site popularity trend (allowed sites only)
SELECT 
    site_name,
    COUNT(*) as visit_count,
    COUNT(DISTINCT user_id) as unique_users,
    MIN(timestamp) as first_visit,
    MAX(timestamp) as last_visit,
    ROUND(COUNT(*) / COUNT(DISTINCT user_id), 2) as avg_visits_per_user
FROM sites_visited 
WHERE status = 'allowed'
  AND timestamp >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
GROUP BY site_name
HAVING visit_count >= 5
ORDER BY visit_count DESC
LIMIT 25;
