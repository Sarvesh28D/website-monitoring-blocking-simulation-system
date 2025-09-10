<?php
/**
 * Website Monitoring Dashboard
 * 
 * Professional dashboard for monitoring website visits and blocking statistics
 * with advanced filtering, charting, and responsive design.
 * 
 * @author Expert Software Engineer
 * @version 1.0
 * @created September 10, 2025
 */

require_once 'db_connect.php';

// Initialize variables
$error_message = '';
$success_message = '';
$users = [];
$statistics = [];
$chart_data = [];
$date_range = '';
$selected_user = '';

// Get filter parameters
$date_from = sanitizeInput($_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days')), 'string');
$date_to = sanitizeInput($_GET['date_to'] ?? date('Y-m-d'), 'string');
$selected_user = sanitizeInput($_GET['user_id'] ?? '', 'int');

try {
    // Get list of users for filter dropdown
    $users_sql = "
        SELECT DISTINCT user_id, 
               COUNT(*) as total_visits,
               MAX(timestamp) as last_visit
        FROM sites_visited 
        GROUP BY user_id 
        ORDER BY user_id ASC
    ";
    $users = queryAll($users_sql);
    
    // Build WHERE conditions for filtering
    $where_conditions = ["DATE(sv.timestamp) BETWEEN ? AND ?"];
    $params = [$date_from, $date_to];
    
    if ($selected_user) {
        $where_conditions[] = "sv.user_id = ?";
        $params[] = $selected_user;
    }
    
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    
    // Get weekly statistics per user
    $weekly_stats_sql = "
        SELECT 
            sv.user_id,
            COUNT(*) as total_visits,
            COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) as blocked_visits,
            COUNT(CASE WHEN sv.status = 'allowed' THEN 1 END) as allowed_visits,
            ROUND(COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as block_percentage,
            MIN(sv.timestamp) as first_visit,
            MAX(sv.timestamp) as last_visit,
            COUNT(DISTINCT sv.site_name) as unique_sites
        FROM sites_visited sv
        {$where_clause}
        GROUP BY sv.user_id
        ORDER BY sv.user_id ASC
    ";
    $statistics = queryAll($weekly_stats_sql, $params);
    
    // Get top 5 visited sites for chart
    $chart_sql = "
        SELECT 
            sv.site_name,
            COUNT(*) as visit_count,
            COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) as blocked_count,
            COUNT(CASE WHEN sv.status = 'allowed' THEN 1 END) as allowed_count
        FROM sites_visited sv
        {$where_clause}
        GROUP BY sv.site_name
        ORDER BY visit_count DESC
        LIMIT 5
    ";
    $chart_data = queryAll($chart_sql, $params);
    
    // Get daily trends for the period
    $daily_trends_sql = "
        SELECT 
            DATE(sv.timestamp) as visit_date,
            COUNT(*) as total_visits,
            COUNT(CASE WHEN sv.status = 'blocked' THEN 1 END) as blocked_visits,
            COUNT(CASE WHEN sv.status = 'allowed' THEN 1 END) as allowed_visits
        FROM sites_visited sv
        {$where_clause}
        GROUP BY DATE(sv.timestamp)
        ORDER BY visit_date ASC
    ";
    $daily_trends = queryAll($daily_trends_sql, $params);
    
    // Get most blocked sites
    $blocked_sites_sql = "
        SELECT 
            sv.site_name,
            COUNT(*) as block_count,
            COUNT(DISTINCT sv.user_id) as affected_users,
            bs.reason
        FROM sites_visited sv
        INNER JOIN blocked_sites bs ON sv.site_name = bs.site_name
        {$where_clause} AND sv.status = 'blocked'
        GROUP BY sv.site_name, bs.reason
        ORDER BY block_count DESC
        LIMIT 10
    ";
    $blocked_sites = queryAll($blocked_sites_sql, $params);
    
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
    error_log("Dashboard error: " . $e->getMessage());
}

// Set date range description
$date_range = date('M j, Y', strtotime($date_from)) . ' - ' . date('M j, Y', strtotime($date_to));
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
    
    <!-- Custom CSS -->
    <style>
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
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            border-radius: 10px;
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--success-color), #2ecc71);
            color: white;
        }
        
        .stat-card.blocked {
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
        }
        
        .stat-card.total {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
        }
        
        .stat-card.users {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .badge-status {
            font-size: 0.8em;
            padding: 0.4em 0.8em;
        }
        
        .progress {
            height: 8px;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
        }
        
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        @media (max-width: 768px) {
            .chart-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shield-alt me-2"></i>
                Website Monitoring Dashboard
            </a>
            <div class="navbar-text ms-auto">
                <i class="fas fa-clock me-1"></i>
                <?php echo date('M j, Y g:i A'); ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        
        <!-- Error/Success Messages -->
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filters Section -->
        <div class="filter-section">
            <h5 class="mb-3">
                <i class="fas fa-filter me-2"></i>
                Filters & Date Range
            </h5>
            
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="user_id" class="form-label">User</label>
                    <select class="form-select" id="user_id" name="user_id">
                        <option value="">All Users</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['user_id']; ?>" 
                                    <?php echo $selected_user == $user['user_id'] ? 'selected' : ''; ?>>
                                User <?php echo $user['user_id']; ?> 
                                (<?php echo number_format($user['total_visits']); ?> visits)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </form>
            
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Showing data for: <strong><?php echo $date_range; ?></strong>
                    <?php if ($selected_user): ?>
                        | User: <strong><?php echo $selected_user; ?></strong>
                    <?php endif; ?>
                </small>
            </div>
        </div>

        <!-- Summary Statistics Cards -->
        <div class="row mb-4">
            <?php
            $total_visits = array_sum(array_column($statistics, 'total_visits'));
            $total_blocked = array_sum(array_column($statistics, 'blocked_visits'));
            $total_allowed = array_sum(array_column($statistics, 'allowed_visits'));
            $unique_users = count($statistics);
            ?>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card total">
                    <div class="card-body text-center">
                        <i class="fas fa-globe fa-2x mb-2"></i>
                        <h3><?php echo number_format($total_visits); ?></h3>
                        <p class="mb-0">Total Visits</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h3><?php echo number_format($total_allowed); ?></h3>
                        <p class="mb-0">Allowed</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card blocked">
                    <div class="card-body text-center">
                        <i class="fas fa-ban fa-2x mb-2"></i>
                        <h3><?php echo number_format($total_blocked); ?></h3>
                        <p class="mb-0">Blocked</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card stat-card users">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h3><?php echo number_format($unique_users); ?></h3>
                        <p class="mb-0">Active Users</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- Top Sites Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar me-2"></i>
                        Top 5 Visited Sites
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="topSitesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Daily Trends Chart -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-line me-2"></i>
                        Daily Browsing Trends
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="dailyTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Statistics Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-table me-2"></i>
                        User Activity Statistics
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>User ID</th>
                                        <th>Total Visits</th>
                                        <th>Allowed</th>
                                        <th>Blocked</th>
                                        <th>Block Rate</th>
                                        <th>Unique Sites</th>
                                        <th>Activity Period</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($statistics as $stat): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">
                                                    User <?php echo $stat['user_id']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($stat['total_visits']); ?></td>
                                            <td>
                                                <span class="text-success">
                                                    <?php echo number_format($stat['allowed_visits']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-danger">
                                                    <?php echo number_format($stat['blocked_visits']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 60px;">
                                                        <div class="progress-bar bg-danger" 
                                                             style="width: <?php echo $stat['block_percentage']; ?>%"></div>
                                                    </div>
                                                    <small><?php echo $stat['block_percentage']; ?>%</small>
                                                </div>
                                            </td>
                                            <td><?php echo number_format($stat['unique_sites']); ?></td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo formatDate($stat['first_visit'], 'M j'); ?> - 
                                                    <?php echo formatDate($stat['last_visit'], 'M j'); ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Most Blocked Sites -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-ban me-2"></i>
                        Most Frequently Blocked Sites
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Site Name</th>
                                        <th>Block Attempts</th>
                                        <th>Affected Users</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($blocked_sites as $site): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-danger">
                                                    <?php echo htmlspecialchars($site['site_name']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($site['block_count']); ?></td>
                                            <td><?php echo number_format($site['affected_users']); ?></td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($site['reason']); ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Charts JavaScript -->
    <script>
        // Top Sites Chart
        const topSitesCtx = document.getElementById('topSitesChart').getContext('2d');
        const topSitesChart = new Chart(topSitesCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($chart_data, 'site_name')); ?>,
                datasets: [
                    {
                        label: 'Allowed',
                        data: <?php echo json_encode(array_column($chart_data, 'allowed_count')); ?>,
                        backgroundColor: 'rgba(39, 174, 96, 0.8)',
                        borderColor: 'rgba(39, 174, 96, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Blocked',
                        data: <?php echo json_encode(array_column($chart_data, 'blocked_count')); ?>,
                        backgroundColor: 'rgba(231, 76, 60, 0.8)',
                        borderColor: 'rgba(231, 76, 60, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Website Visit Statistics'
                    }
                }
            }
        });

        // Daily Trends Chart
        const dailyTrendsCtx = document.getElementById('dailyTrendsChart').getContext('2d');
        const dailyTrendsChart = new Chart(dailyTrendsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($daily_trends, 'visit_date')); ?>,
                datasets: [
                    {
                        label: 'Total Visits',
                        data: <?php echo json_encode(array_column($daily_trends, 'total_visits')); ?>,
                        borderColor: 'rgba(52, 152, 219, 1)',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Blocked Visits',
                        data: <?php echo json_encode(array_column($daily_trends, 'blocked_visits')); ?>,
                        borderColor: 'rgba(231, 76, 60, 1)',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Daily Activity Trends'
                    }
                }
            }
        });

        // Auto-refresh page every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
