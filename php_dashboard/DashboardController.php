<?php
/**
 * Dashboard Controller for Website Monitoring System
 * 
 * Handles all data queries and business logic for the monitoring dashboard.
 * Separates data processing from presentation layer for better maintainability.
 * 
 * @author Expert Software Engineer
 * @version 1.0
 * @created September 10, 2025
 */

require_once 'db_connect.php';

class DashboardController 
{
    private $db;
    private $error_message = '';
    
    public function __construct() 
    {
        $this->db = DatabaseConnection::getInstance();
    }
    
    /**
     * Get sanitized filter parameters from request
     * 
     * @return array Associative array with filter parameters
     */
    public function getFilterParameters(): array 
    {
        return [
            'date_from' => sanitizeInput($_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days')), 'string'),
            'date_to' => sanitizeInput($_GET['date_to'] ?? date('Y-m-d'), 'string'),
            'selected_user' => sanitizeInput($_GET['user_id'] ?? '', 'int')
        ];
    }
    
    /**
     * Get list of users for filter dropdown
     * 
     * @return array List of users with statistics
     */
    public function getUsers(): array 
    {
        try {
            $sql = "
                SELECT DISTINCT user_id, 
                       COUNT(*) as total_visits,
                       MAX(visit_timestamp) as last_visit
                FROM sites_visited 
                GROUP BY user_id 
                ORDER BY user_id ASC
            ";
            return queryAll($sql);
        } catch (Exception $e) {
            $this->error_message = "Failed to get users: " . $e->getMessage();
            error_log("Dashboard error getting users: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Build WHERE clause and parameters for filtering
     * 
     * @param array $filters Filter parameters
     * @return array Array with 'clause' and 'params' keys
     */
    private function buildWhereClause(array $filters): array 
    {
        $where_conditions = ["DATE(sv.visit_timestamp) BETWEEN ? AND ?"];
        $params = [$filters['date_from'], $filters['date_to']];
        
        if (!empty($filters['selected_user'])) {
            $where_conditions[] = "sv.user_id = ?";
            $params[] = $filters['selected_user'];
        }
        
        return [
            'clause' => "WHERE " . implode(" AND ", $where_conditions),
            'params' => $params
        ];
    }
    
    /**
     * Get weekly statistics per user
     * 
     * @param array $filters Filter parameters
     * @return array User statistics
     */
    public function getUserStatistics(array $filters): array 
    {
        try {
            $where_info = $this->buildWhereClause($filters);
            
            $sql = "
                SELECT 
                    sv.user_id,
                    COUNT(*) as total_visits,
                    COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) as blocked_visits,
                    COUNT(CASE WHEN sv.status = 'allowed' THEN 1 END) as allowed_visits,
                    ROUND(COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as block_percentage,
                    MIN(sv.visit_timestamp) as first_visit,
                    MAX(sv.visit_timestamp) as last_visit,
                    COUNT(DISTINCT sv.url) as unique_sites
                FROM sites_visited sv
                {$where_info['clause']}
                GROUP BY sv.user_id
                ORDER BY sv.user_id ASC
            ";
            
            return queryAll($sql, $where_info['params']);
        } catch (Exception $e) {
            $this->error_message = "Failed to get user statistics: " . $e->getMessage();
            error_log("Dashboard error getting user stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get top visited sites for charts
     * 
     * @param array $filters Filter parameters
     * @param int $limit Number of sites to return
     * @return array Top visited sites data
     */
    public function getTopSites(array $filters, int $limit = 5): array 
    {
        try {
            $where_info = $this->buildWhereClause($filters);
            
            $sql = "
                SELECT 
                    sv.url,
                    COUNT(*) as visit_count,
                    COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) as blocked_count,
                    COUNT(CASE WHEN sv.status = 'allowed' THEN 1 END) as allowed_count
                FROM sites_visited sv
                {$where_info['clause']}
                GROUP BY sv.url
                ORDER BY visit_count DESC
                LIMIT ?
            ";
            
            $params = array_merge($where_info['params'], [$limit]);
            return queryAll($sql, $params);
        } catch (Exception $e) {
            $this->error_message = "Failed to get top sites: " . $e->getMessage();
            error_log("Dashboard error getting top sites: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get daily trends for the specified period
     * 
     * @param array $filters Filter parameters
     * @return array Daily trends data
     */
    public function getDailyTrends(array $filters): array 
    {
        try {
            $where_info = $this->buildWhereClause($filters);
            
            $sql = "
                SELECT 
                    DATE(sv.visit_timestamp) as visit_date,
                    COUNT(*) as total_visits,
                    COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) as blocked_visits,
                    COUNT(CASE WHEN sv.status = 'allowed' THEN 1 END) as allowed_visits
                FROM sites_visited sv
                {$where_info['clause']}
                GROUP BY DATE(sv.visit_timestamp)
                ORDER BY visit_date ASC
            ";
            
            return queryAll($sql, $where_info['params']);
        } catch (Exception $e) {
            $this->error_message = "Failed to get daily trends: " . $e->getMessage();
            error_log("Dashboard error getting daily trends: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get most blocked sites
     * 
     * @param array $filters Filter parameters
     * @param int $limit Number of sites to return
     * @return array Most blocked sites data
     */
    public function getBlockedSites(array $filters, int $limit = 10): array 
    {
        try {
            $where_info = $this->buildWhereClause($filters);
            
            $sql = "
                SELECT 
                    sv.url,
                    COUNT(*) as block_count,
                    COUNT(DISTINCT sv.user_id) as affected_users,
                    bs.reason
                FROM sites_visited sv
                INNER JOIN blocked_sites bs ON sv.url = bs.site_name
                {$where_info['clause']} AND sv.status = 'blocked'
                GROUP BY sv.url, bs.reason
                ORDER BY block_count DESC
                LIMIT ?
            ";
            
            $params = array_merge($where_info['params'], [$limit]);
            return queryAll($sql, $params);
        } catch (Exception $e) {
            $this->error_message = "Failed to get blocked sites: " . $e->getMessage();
            error_log("Dashboard error getting blocked sites: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get overall summary statistics
     * 
     * @param array $filters Filter parameters
     * @return array Summary statistics
     */
    public function getSummaryStatistics(array $filters): array 
    {
        try {
            $where_info = $this->buildWhereClause($filters);
            
            $sql = "
                SELECT 
                    COUNT(*) as total_visits,
                    COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) as total_blocked,
                    COUNT(CASE WHEN sv.status = 'allowed' THEN 1 END) as total_allowed,
                    COUNT(DISTINCT sv.user_id) as active_users,
                    COUNT(DISTINCT sv.url) as unique_sites,
                    ROUND(COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as overall_block_rate
                FROM sites_visited sv
                {$where_info['clause']}
            ";
            
            $result = queryOne($sql, $where_info['params']);
            return $result ?: [];
        } catch (Exception $e) {
            $this->error_message = "Failed to get summary statistics: " . $e->getMessage();
            error_log("Dashboard error getting summary stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all dashboard data in one call
     * 
     * @param array $filters Filter parameters
     * @return array Complete dashboard data
     */
    public function getAllDashboardData(array $filters): array 
    {
        return [
            'users' => $this->getUsers(),
            'statistics' => $this->getUserStatistics($filters),
            'chart_data' => $this->getTopSites($filters),
            'daily_trends' => $this->getDailyTrends($filters),
            'blocked_sites' => $this->getBlockedSites($filters),
            'summary' => $this->getSummaryStatistics($filters),
            'error_message' => $this->error_message,
            'date_range' => $this->formatDateRange($filters['date_from'], $filters['date_to'])
        ];
    }
    
    /**
     * Format date range for display
     * 
     * @param string $date_from Start date
     * @param string $date_to End date
     * @return string Formatted date range
     */
    private function formatDateRange(string $date_from, string $date_to): string 
    {
        return date('M j, Y', strtotime($date_from)) . ' - ' . date('M j, Y', strtotime($date_to));
    }
    
    /**
     * Get error message if any occurred
     * 
     * @return string Error message
     */
    public function getErrorMessage(): string 
    {
        return $this->error_message;
    }
}
