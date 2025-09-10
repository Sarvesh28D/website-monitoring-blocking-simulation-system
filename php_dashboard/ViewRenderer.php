<?php
/**
 * View Renderer for Website Monitoring Dashboard
 * 
 * Handles HTML rendering and template management for the dashboard.
 * Separates presentation logic from data processing.
 * 
 * @author Expert Software Engineer
 * @version 1.0
 * @created September 10, 2025
 */

class ViewRenderer 
{
    private $data;
    private $chart_manager;
    
    public function __construct(array $data, ChartDataManager $chart_manager) 
    {
        $this->data = $data;
        $this->chart_manager = $chart_manager;
    }
    
    /**
     * Render the complete dashboard HTML
     * 
     * @return string Complete HTML output
     */
    public function render(): string 
    {
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Monitoring Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        <?php echo $this->renderCustomCSS(); ?>
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php echo $this->renderHeader(); ?>
        <?php echo $this->renderAlerts(); ?>
        <?php echo $this->renderFilters(); ?>
        <?php echo $this->renderSummaryCards(); ?>
        <?php echo $this->renderCharts(); ?>
        <?php echo $this->renderDataTables(); ?>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        <?php echo $this->renderJavaScript(); ?>
    </script>
</body>
</html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render custom CSS styles
     * 
     * @return string CSS styles
     */
    private function renderCustomCSS(): string 
    {
        return "
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--secondary-color);
            transition: transform 0.2s ease-in-out;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .blocked { background-color: var(--danger-color); }
        .allowed { background-color: var(--success-color); }
        
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        ";
    }
    
    /**
     * Render page header
     * 
     * @return string Header HTML
     */
    private function renderHeader(): string 
    {
        return "
        <div class='dashboard-header'>
            <div class='container'>
                <div class='row align-items-center'>
                    <div class='col-md-8'>
                        <h1 class='display-4 mb-0'>
                            <i class='fas fa-shield-alt me-3'></i>
                            Website Monitoring Dashboard
                        </h1>
                        <p class='lead mb-0 mt-2'>Real-time monitoring and blocking statistics</p>
                    </div>
                    <div class='col-md-4 text-end'>
                        <div class='badge bg-light text-dark fs-6 p-2'>
                            <i class='fas fa-calendar-alt me-1'></i>
                            {$this->data['date_range']}
                        </div>
                    </div>
                </div>
            </div>
        </div>";
    }
    
    /**
     * Render alert messages
     * 
     * @return string Alerts HTML
     */
    private function renderAlerts(): string 
    {
        $html = '';
        
        if (!empty($this->data['error_message'])) {
            $html .= "
            <div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <i class='fas fa-exclamation-triangle me-2'></i>
                {$this->data['error_message']}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
        }
        
        return $html;
    }
    
    /**
     * Render filter form
     * 
     * @return string Filters HTML
     */
    private function renderFilters(): string 
    {
        $users_options = '';
        foreach ($this->data['users'] as $user) {
            $selected = (isset($_GET['user_id']) && $_GET['user_id'] == $user['user_id']) ? 'selected' : '';
            $users_options .= "<option value='{$user['user_id']}' {$selected}>
                User {$user['user_id']} ({$user['total_visits']} visits)
            </option>";
        }
        
        $date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
        $date_to = $_GET['date_to'] ?? date('Y-m-d');
        
        return "
        <div class='filter-card'>
            <form method='GET' class='row g-3'>
                <div class='col-md-3'>
                    <label class='form-label'><i class='fas fa-calendar me-1'></i> From Date</label>
                    <input type='date' name='date_from' class='form-control' value='{$date_from}'>
                </div>
                <div class='col-md-3'>
                    <label class='form-label'><i class='fas fa-calendar me-1'></i> To Date</label>
                    <input type='date' name='date_to' class='form-control' value='{$date_to}'>
                </div>
                <div class='col-md-3'>
                    <label class='form-label'><i class='fas fa-user me-1'></i> User Filter</label>
                    <select name='user_id' class='form-select'>
                        <option value=''>All Users</option>
                        {$users_options}
                    </select>
                </div>
                <div class='col-md-3 d-flex align-items-end'>
                    <button type='submit' class='btn btn-primary me-2'>
                        <i class='fas fa-filter me-1'></i> Apply Filter
                    </button>
                    <a href='?' class='btn btn-outline-secondary'>
                        <i class='fas fa-undo me-1'></i> Reset
                    </a>
                </div>
            </form>
        </div>";
    }
    
    /**
     * Render summary statistic cards
     * 
     * @return string Summary cards HTML
     */
    private function renderSummaryCards(): string 
    {
        $summary = $this->chart_manager->prepareSummaryCards($this->data['summary']);
        
        return "
        <div class='row'>
            <div class='col-lg-3 col-md-6'>
                <div class='stat-card'>
                    <div class='row align-items-center'>
                        <div class='col-8'>
                            <h3 class='text-primary mb-1'>{$summary['total_visits']}</h3>
                            <p class='text-muted mb-0'>Total Visits</p>
                        </div>
                        <div class='col-4 text-end'>
                            <i class='fas fa-eye stat-icon text-primary'></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-lg-3 col-md-6'>
                <div class='stat-card'>
                    <div class='row align-items-center'>
                        <div class='col-8'>
                            <h3 class='text-danger mb-1'>{$summary['block_rate']}</h3>
                            <p class='text-muted mb-0'>Block Rate</p>
                        </div>
                        <div class='col-4 text-end'>
                            <i class='fas fa-ban stat-icon text-danger'></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-lg-3 col-md-6'>
                <div class='stat-card'>
                    <div class='row align-items-center'>
                        <div class='col-8'>
                            <h3 class='text-success mb-1'>{$summary['active_users']}</h3>
                            <p class='text-muted mb-0'>Active Users</p>
                        </div>
                        <div class='col-4 text-end'>
                            <i class='fas fa-users stat-icon text-success'></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-lg-3 col-md-6'>
                <div class='stat-card'>
                    <div class='row align-items-center'>
                        <div class='col-8'>
                            <h3 class='text-info mb-1'>{$summary['unique_sites']}</h3>
                            <p class='text-muted mb-0'>Unique Sites</p>
                        </div>
                        <div class='col-4 text-end'>
                            <i class='fas fa-globe stat-icon text-info'></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
    }
    
    /**
     * Render chart sections
     * 
     * @return string Charts HTML
     */
    private function renderCharts(): string 
    {
        return "
        <div class='row'>
            <div class='col-lg-6'>
                <div class='chart-container'>
                    <h4 class='mb-3'><i class='fas fa-chart-bar me-2'></i>Top Visited Sites</h4>
                    <canvas id='topSitesChart'></canvas>
                </div>
            </div>
            <div class='col-lg-6'>
                <div class='chart-container'>
                    <h4 class='mb-3'><i class='fas fa-chart-line me-2'></i>Daily Visit Trends</h4>
                    <canvas id='dailyTrendsChart'></canvas>
                </div>
            </div>
        </div>";
    }
    
    /**
     * Render data tables
     * 
     * @return string Tables HTML
     */
    private function renderDataTables(): string 
    {
        return "
        <div class='row'>
            <div class='col-lg-8'>
                " . $this->renderUserStatsTable() . "
            </div>
            <div class='col-lg-4'>
                " . $this->renderBlockedSitesTable() . "
            </div>
        </div>";
    }
    
    /**
     * Render user statistics table
     * 
     * @return string User stats table HTML
     */
    private function renderUserStatsTable(): string 
    {
        $rows = '';
        foreach ($this->data['statistics'] as $user) {
            $block_badge_class = $user['block_percentage'] > 50 ? 'bg-danger' : 'bg-warning';
            $rows .= "
            <tr>
                <td><strong>User {$user['user_id']}</strong></td>
                <td class='text-center'>{$user['total_visits']}</td>
                <td class='text-center text-success'>{$user['allowed_visits']}</td>
                <td class='text-center text-danger'>{$user['blocked_visits']}</td>
                <td class='text-center'>
                    <span class='badge {$block_badge_class}'>{$user['block_percentage']}%</span>
                </td>
                <td class='text-center'>{$user['unique_sites']}</td>
                <td class='text-muted small'>" . date('M j, H:i', strtotime($user['last_visit'])) . "</td>
            </tr>";
        }
        
        return "
        <div class='table-container'>
            <h4 class='mb-3'><i class='fas fa-users me-2'></i>User Statistics</h4>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead class='table-dark'>
                        <tr>
                            <th>User</th>
                            <th class='text-center'>Total</th>
                            <th class='text-center'>Allowed</th>
                            <th class='text-center'>Blocked</th>
                            <th class='text-center'>Block %</th>
                            <th class='text-center'>Sites</th>
                            <th class='text-center'>Last Visit</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$rows}
                    </tbody>
                </table>
            </div>
        </div>";
    }
    
    /**
     * Render blocked sites table
     * 
     * @return string Blocked sites table HTML
     */
    private function renderBlockedSitesTable(): string 
    {
        $rows = '';
        foreach ($this->data['blocked_sites'] as $site) {
            $rows .= "
            <tr>
                <td><strong>{$site['url']}</strong></td>
                <td class='text-center'>
                    <span class='badge bg-danger'>{$site['block_count']}</span>
                </td>
                <td class='text-center'>{$site['affected_users']}</td>
                <td class='text-muted small'>{$site['reason']}</td>
            </tr>";
        }
        
        return "
        <div class='table-container'>
            <h4 class='mb-3'><i class='fas fa-ban me-2'></i>Most Blocked Sites</h4>
            <div class='table-responsive'>
                <table class='table table-hover'>
                    <thead class='table-dark'>
                        <tr>
                            <th>Site</th>
                            <th class='text-center'>Blocks</th>
                            <th class='text-center'>Users</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$rows}
                    </tbody>
                </table>
            </div>
        </div>";
    }
    
    /**
     * Render JavaScript for charts and functionality
     * 
     * @return string JavaScript code
     */
    private function renderJavaScript(): string 
    {
        $top_sites_data = $this->chart_manager->prepareTopSitesChart($this->data['chart_data']);
        $daily_trends_data = $this->chart_manager->prepareDailyTrendsChart($this->data['daily_trends']);
        $bar_options = $this->chart_manager->getChartOptions('bar');
        $line_options = $this->chart_manager->getChartOptions('line');
        
        return "
        // Top Sites Chart
        const topSitesCtx = document.getElementById('topSitesChart').getContext('2d');
        new Chart(topSitesCtx, {
            type: 'bar',
            data: {$top_sites_data},
            options: {$bar_options}
        });
        
        // Daily Trends Chart
        const dailyTrendsCtx = document.getElementById('dailyTrendsChart').getContext('2d');
        new Chart(dailyTrendsCtx, {
            type: 'line',
            data: {$daily_trends_data},
            options: {$line_options}
        });
        
        // Auto-refresh page every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
        
        // Add loading states to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const btn = form.querySelector('button[type=\"submit\"]');
                if (btn) {
                    btn.innerHTML = '<i class=\"fas fa-spinner fa-spin me-1\"></i> Loading...';
                    btn.disabled = true;
                }
            });
        });
        ";
    }
}
