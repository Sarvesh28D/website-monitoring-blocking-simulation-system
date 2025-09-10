-- Website Monitoring System - Master Installation Script
-- Created: September 10, 2025
-- Author: Expert Software Engineer
-- Purpose: Execute all modular SQL files in the correct order

-- This script orchestrates the installation of the entire database schema
-- Run this file to set up the complete website monitoring database

-- Step 1: Database Setup
-- Execute: 01_database_setup.sql
SOURCE ./modular/01_database_setup.sql;

-- Step 2: Create Core Tables
-- Execute: 02_create_tables.sql  
SOURCE ./modular/02_create_tables.sql;

-- Step 3: Create Views
-- Execute: 03_create_views.sql
SOURCE ./modular/03_create_views.sql;

-- Step 4: Insert Sample Data
-- Execute: 04_sample_data.sql
SOURCE ./modular/04_sample_data.sql;

-- Step 5: Create Stored Procedures and Functions
-- Execute: 06_stored_procedures.sql
SOURCE ./modular/06_stored_procedures.sql;

-- Installation completed
SELECT 'Website Monitoring Database Installation Completed Successfully!' as status;

-- Verify installation
SELECT 
    'Tables Created' as component,
    COUNT(*) as count
FROM information_schema.tables 
WHERE table_schema = 'website_monitoring'

UNION ALL

SELECT 
    'Views Created' as component,
    COUNT(*) as count
FROM information_schema.views 
WHERE table_schema = 'website_monitoring'

UNION ALL

SELECT 
    'Stored Procedures' as component,
    COUNT(*) as count
FROM information_schema.routines 
WHERE routine_schema = 'website_monitoring' 
AND routine_type = 'PROCEDURE'

UNION ALL

SELECT 
    'Stored Functions' as component,
    COUNT(*) as count
FROM information_schema.routines 
WHERE routine_schema = 'website_monitoring' 
AND routine_type = 'FUNCTION'

UNION ALL

SELECT 
    'Blocked Sites' as component,
    COUNT(*) as count
FROM website_monitoring.blocked_sites;
