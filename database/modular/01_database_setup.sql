-- Website Monitoring System - Database Structure
-- Created: September 10, 2025
-- Author: Expert Software Engineer
-- Purpose: Core database creation and configuration

-- Create database with proper charset
CREATE DATABASE IF NOT EXISTS website_monitoring 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE website_monitoring;

-- Set session variables for optimal performance
SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';
SET SESSION time_zone = '+00:00';

-- Enable query cache if available
SET SESSION query_cache_type = ON;
