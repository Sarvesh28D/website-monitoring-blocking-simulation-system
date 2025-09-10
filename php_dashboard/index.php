<?php
/**
 * Website Monitoring Dashboard - Main Entry Point
 * 
 * Modular dashboard entry point that coordinates components
 * for monitoring website visits and blocking statistics.
 * 
 * @author Expert Software Engineer
 * @version 1.0
 * @created September 10, 2025
 */

// Include modular components
require_once 'DashboardController.php';
require_once 'ChartDataManager.php';
require_once 'ViewRenderer.php';

// Initialize components
$controller = new DashboardController();
$chart_manager = new ChartDataManager();

// Get filter parameters
$filters = $controller->getFilterParameters();

// Get all dashboard data
$dashboard_data = $controller->getAllDashboardData($filters);

// Render the dashboard
$renderer = new ViewRenderer($dashboard_data, $chart_manager);
echo $renderer->render();
?>
