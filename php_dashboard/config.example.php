<?php
/**
 * Configuration file for Website Monitoring Dashboard
 * 
 * This file contains database and application configuration.
 * Rename this file to config.php and update the values as needed.
 */

return [
    // Database configuration
    'host' => 'localhost',
    'dbname' => 'website_monitoring',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    
    // Application settings
    'timezone' => 'UTC',
    'date_format' => 'Y-m-d H:i:s',
    'items_per_page' => 50,
    
    // Security settings
    'session_timeout' => 3600, // 1 hour
    'csrf_protection' => true,
    
    // Feature flags
    'enable_auto_refresh' => true,
    'auto_refresh_interval' => 300000, // 5 minutes in milliseconds
    'enable_real_time_updates' => false,
];
?>
