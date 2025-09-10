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
     * Get weekly report of top 10 visited sites with comprehensive analytics
     * 
     * @param array $filters Filter parameters (defaults to last 7 days)
     * @return array Weekly top sites report data
     */
    public function getWeeklyTopSitesReport(array $filters = []): array 
    {
        try {
            // Default to last 7 days if no filters provided
            if (empty($filters['date_from']) || empty($filters['date_to'])) {
                $filters['date_from'] = date('Y-m-d', strtotime('-7 days'));
                $filters['date_to'] = date('Y-m-d');
            }
            
            $where_info = $this->buildWhereClause($filters);
            
            $sql = "
                SELECT 
                    sv.url,
                    COUNT(*) as total_visits,
                    COUNT(DISTINCT sv.user_id) as unique_users,
                    COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) as blocked_visits,
                    COUNT(CASE WHEN sv.status = 'allowed' THEN 1 END) as allowed_visits,
                    ROUND((COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) / COUNT(*)) * 100, 2) as block_rate,
                    MIN(sv.visit_timestamp) as first_visit,
                    MAX(sv.visit_timestamp) as last_visit,
                    COUNT(DISTINCT DATE(sv.visit_timestamp)) as days_active,
                    GROUP_CONCAT(DISTINCT sv.user_id ORDER BY sv.user_id SEPARATOR ',') as user_list
                FROM sites_visited sv
                {$where_info['clause']}
                GROUP BY sv.url
                ORDER BY total_visits DESC, unique_users DESC
                LIMIT 10
            ";
            
            $result = queryAll($sql, $where_info['params']);
            
            // Add weekly trend data for each site
            foreach ($result as &$site) {
                $site['daily_breakdown'] = $this->getDailyBreakdownForSite($site['url'], $filters);
                $site['peak_day'] = $this->getPeakDayForSite($site['url'], $filters);
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->error_message = "Failed to get weekly top sites report: " . $e->getMessage();
            error_log("Dashboard error getting weekly report: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get daily breakdown for a specific site
     * 
     * @param string $site_url Site URL
     * @param array $filters Date filters
     * @return array Daily visit counts
     */
    private function getDailyBreakdownForSite(string $site_url, array $filters): array 
    {
        try {
            $sql = "
                SELECT 
                    DATE(visit_timestamp) as visit_date,
                    COUNT(*) as visits,
                    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked
                FROM sites_visited 
                WHERE url = ? 
                AND DATE(visit_timestamp) BETWEEN ? AND ?
                GROUP BY DATE(visit_timestamp)
                ORDER BY visit_date
            ";
            
            return queryAll($sql, [$site_url, $filters['date_from'], $filters['date_to']]);
        } catch (Exception $e) {
            error_log("Error getting daily breakdown for site {$site_url}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get peak day for a specific site
     * 
     * @param string $site_url Site URL
     * @param array $filters Date filters
     * @return array Peak day information
     */
    private function getPeakDayForSite(string $site_url, array $filters): array 
    {
        try {
            $sql = "
                SELECT 
                    DATE(visit_timestamp) as peak_date,
                    COUNT(*) as peak_visits,
                    DAYNAME(visit_timestamp) as day_name
                FROM sites_visited 
                WHERE url = ? 
                AND DATE(visit_timestamp) BETWEEN ? AND ?
                GROUP BY DATE(visit_timestamp)
                ORDER BY peak_visits DESC
                LIMIT 1
            ";
            
            $result = queryOne($sql, [$site_url, $filters['date_from'], $filters['date_to']]);
            return $result ?: [];
        } catch (Exception $e) {
            error_log("Error getting peak day for site {$site_url}: " . $e->getMessage());
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
            'weekly_report' => $this->getWeeklyTopSitesReport($filters),
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
