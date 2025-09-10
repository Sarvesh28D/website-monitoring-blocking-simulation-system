<?php
/**
 * Chart Data Manager for Website Monitoring Dashboard
 * 
 * Handles formatting and preparation of data for Chart.js visualizations.
 * Converts database results into chart-ready JSON formats.
 * 
 * @author Expert Software Engineer
 * @version 1.0
 * @created September 10, 2025
 */

class ChartDataManager 
{
    /**
     * Prepare top sites data for Chart.js bar chart
     * 
     * @param array $sites_data Raw sites data from database
     * @return string JSON formatted data for Chart.js
     */
    public function prepareTopSitesChart(array $sites_data): string 
    {
        $labels = [];
        $allowed_data = [];
        $blocked_data = [];
        
        foreach ($sites_data as $site) {
            $labels[] = $site['url'];
            $allowed_data[] = (int)$site['allowed_count'];
            $blocked_data[] = (int)$site['blocked_count'];
        }
        
        $chart_data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Allowed Visits',
                    'data' => $allowed_data,
                    'backgroundColor' => 'rgba(39, 174, 96, 0.8)',
                    'borderColor' => 'rgba(39, 174, 96, 1)',
                    'borderWidth' => 1,
                    'borderRadius' => 4
                ],
                [
                    'label' => 'Blocked Visits',
                    'data' => $blocked_data,
                    'backgroundColor' => 'rgba(231, 76, 60, 0.8)',
                    'borderColor' => 'rgba(231, 76, 60, 1)',
                    'borderWidth' => 1,
                    'borderRadius' => 4
                ]
            ]
        ];
        
        return json_encode($chart_data);
    }
    
    /**
     * Prepare daily trends data for Chart.js line chart
     * 
     * @param array $trends_data Raw daily trends data from database
     * @return string JSON formatted data for Chart.js
     */
    public function prepareDailyTrendsChart(array $trends_data): string 
    {
        $labels = [];
        $total_data = [];
        $allowed_data = [];
        $blocked_data = [];
        
        foreach ($trends_data as $day) {
            $labels[] = date('M j', strtotime($day['visit_date']));
            $total_data[] = (int)$day['total_visits'];
            $allowed_data[] = (int)$day['allowed_visits'];
            $blocked_data[] = (int)$day['blocked_visits'];
        }
        
        $chart_data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Visits',
                    'data' => $total_data,
                    'borderColor' => 'rgba(52, 152, 219, 1)',
                    'backgroundColor' => 'rgba(52, 152, 219, 0.1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4
                ],
                [
                    'label' => 'Allowed Visits',
                    'data' => $allowed_data,
                    'borderColor' => 'rgba(39, 174, 96, 1)',
                    'backgroundColor' => 'rgba(39, 174, 96, 0.1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.4
                ],
                [
                    'label' => 'Blocked Visits',
                    'data' => $blocked_data,
                    'borderColor' => 'rgba(231, 76, 60, 1)',
                    'backgroundColor' => 'rgba(231, 76, 60, 0.1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.4
                ]
            ]
        ];
        
        return json_encode($chart_data);
    }
    
    /**
     * Prepare user statistics data for Chart.js doughnut chart
     * 
     * @param array $user_stats Raw user statistics data
     * @return string JSON formatted data for Chart.js
     */
    public function prepareUserStatsChart(array $user_stats): string 
    {
        $total_allowed = 0;
        $total_blocked = 0;
        
        foreach ($user_stats as $user) {
            $total_allowed += (int)$user['allowed_visits'];
            $total_blocked += (int)$user['blocked_visits'];
        }
        
        $chart_data = [
            'labels' => ['Allowed Visits', 'Blocked Visits'],
            'datasets' => [
                [
                    'data' => [$total_allowed, $total_blocked],
                    'backgroundColor' => [
                        'rgba(39, 174, 96, 0.8)',
                        'rgba(231, 76, 60, 0.8)'
                    ],
                    'borderColor' => [
                        'rgba(39, 174, 96, 1)',
                        'rgba(231, 76, 60, 1)'
                    ],
                    'borderWidth' => 2
                ]
            ]
        ];
        
        return json_encode($chart_data);
    }
    
    /**
     * Prepare blocked sites data for Chart.js horizontal bar chart
     * 
     * @param array $blocked_sites Raw blocked sites data
     * @return string JSON formatted data for Chart.js
     */
    public function prepareBlockedSitesChart(array $blocked_sites): string 
    {
        $labels = [];
        $data = [];
        $colors = [];
        
        // Color palette for different sites
        $color_palette = [
            'rgba(231, 76, 60, 0.8)',   // Red
            'rgba(243, 156, 18, 0.8)',  // Orange
            'rgba(155, 89, 182, 0.8)',  // Purple
            'rgba(52, 152, 219, 0.8)',  // Blue
            'rgba(26, 188, 156, 0.8)',  // Turquoise
            'rgba(230, 126, 34, 0.8)',  // Carrot
            'rgba(46, 204, 113, 0.8)',  // Emerald
            'rgba(241, 196, 15, 0.8)',  // Sunflower
            'rgba(231, 76, 60, 0.8)',   // Alizarin
            'rgba(142, 68, 173, 0.8)'   // Wisteria
        ];
        
        foreach ($blocked_sites as $index => $site) {
            $labels[] = $site['url'];
            $data[] = (int)$site['block_count'];
            $colors[] = $color_palette[$index % count($color_palette)];
        }
        
        $chart_data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Block Count',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => array_map(function($color) {
                        return str_replace('0.8', '1', $color);
                    }, $colors),
                    'borderWidth' => 1
                ]
            ]
        ];
        
        return json_encode($chart_data);
    }
    
    /**
     * Get Chart.js configuration for responsive charts
     * 
     * @param string $chart_type Type of chart (bar, line, doughnut, etc.)
     * @return string JSON formatted Chart.js options
     */
    public function getChartOptions(string $chart_type): string 
    {
        $base_options = [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ]
            ]
        ];
        
        switch ($chart_type) {
            case 'bar':
                $base_options['scales'] = [
                    'y' => [
                        'beginAtZero' => true,
                        'grid' => ['color' => 'rgba(0,0,0,0.1)']
                    ],
                    'x' => [
                        'grid' => ['display' => false]
                    ]
                ];
                $base_options['plugins']['tooltip']['callbacks'] = [
                    'title' => 'function(context) { return context[0].label; }',
                    'label' => 'function(context) { return context.dataset.label + \': \' + context.parsed.y + \' visits\'; }'
                ];
                break;
                
            case 'line':
                $base_options['scales'] = [
                    'y' => [
                        'beginAtZero' => true,
                        'grid' => ['color' => 'rgba(0,0,0,0.1)']
                    ],
                    'x' => [
                        'grid' => ['display' => false]
                    ]
                ];
                $base_options['interaction'] = [
                    'mode' => 'nearest',
                    'axis' => 'x',
                    'intersect' => false
                ];
                break;
                
            case 'doughnut':
                $base_options['cutout'] = '50%';
                $base_options['plugins']['tooltip']['callbacks'] = [
                    'label' => 'function(context) { 
                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                        var percentage = ((context.parsed * 100) / total).toFixed(1);
                        return context.label + \': \' + context.parsed + \' (\' + percentage + \'%)\';
                    }'
                ];
                break;
                
            case 'horizontalBar':
                $base_options['indexAxis'] = 'y';
                $base_options['scales'] = [
                    'x' => [
                        'beginAtZero' => true,
                        'grid' => ['color' => 'rgba(0,0,0,0.1)']
                    ],
                    'y' => [
                        'grid' => ['display' => false]
                    ]
                ];
                break;
        }
        
        return json_encode($base_options);
    }
    
    /**
     * Generate summary statistics for dashboard cards
     * 
     * @param array $summary_data Raw summary data from database
     * @return array Formatted summary statistics
     */
    public function prepareSummaryCards(array $summary_data): array 
    {
        if (empty($summary_data)) {
            return [
                'total_visits' => 0,
                'block_rate' => 0,
                'active_users' => 0,
                'unique_sites' => 0
            ];
        }
        
        return [
            'total_visits' => number_format((int)$summary_data['total_visits']),
            'total_blocked' => number_format((int)$summary_data['total_blocked']),
            'total_allowed' => number_format((int)$summary_data['total_allowed']),
            'block_rate' => number_format((float)$summary_data['overall_block_rate'], 1) . '%',
            'active_users' => (int)$summary_data['active_users'],
            'unique_sites' => (int)$summary_data['unique_sites']
        ];
    }
}
