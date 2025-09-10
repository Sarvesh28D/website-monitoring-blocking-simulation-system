# Website Monitoring System - Modular Database Structure

## Overview
This directory contains the modularized SQL structure for the Website Monitoring & Blocking Simulation System. The database has been broken down into logical, focused modules for better maintainability, version control, and deployment.

## File Structure

### Core SQL Files (Original)
- `schema.sql` - Original monolithic schema file (3.0 KB, 67 lines) ⚠️ **Legacy**
- `queries.sql` - Original analytical queries file (6.5 KB, 169 lines) ⚠️ **Legacy**

### Modular Structure (/modular directory)
- `01_database_setup.sql` - Database creation and configuration (15 lines)
- `02_create_tables.sql` - Core table definitions with indexing (79 lines)  
- `03_create_views.sql` - Optimized views for reporting (108 lines)
- `04_sample_data.sql` - Sample/seed data for testing (53 lines)
- `05_analytical_queries.sql` - Advanced analytical queries (213 lines)
- `06_stored_procedures.sql` - Reusable procedures and functions (236 lines)
- `install_all.sql` - Master installation script (57 lines)

## Benefits of Modularization

### 🎯 **Separation of Concerns**
- **Database Structure**: Tables, indexes, and constraints
- **Business Logic**: Views and stored procedures  
- **Data**: Sample and seed data
- **Analytics**: Reporting and analytical queries
- **Configuration**: Database setup and initialization

### 🔧 **Maintainability**
- Easy to locate specific functionality
- Individual files can be updated independently
- Clear dependencies between modules
- Version control friendly (smaller, focused commits)

### 🚀 **Deployment Flexibility**
- Install only required components
- Selective updates without full schema rebuild
- Environment-specific configurations
- Easy rollback of individual modules

### 👥 **Team Collaboration**
- Multiple developers can work on different modules
- Reduced merge conflicts
- Specialized expertise can be applied to specific areas
- Clear ownership and responsibility boundaries

## Installation Instructions

### Option 1: Complete Installation
```bash
mysql -u root -p < modular/install_all.sql
```

### Option 2: Manual Step-by-Step Installation
```bash
# 1. Database setup
mysql -u root -p < modular/01_database_setup.sql

# 2. Core tables
mysql -u root -p < modular/02_create_tables.sql

# 3. Views
mysql -u root -p < modular/03_create_views.sql

# 4. Sample data
mysql -u root -p < modular/04_sample_data.sql

# 5. Stored procedures and functions
mysql -u root -p < modular/06_stored_procedures.sql
```

### Option 3: Selective Installation
Install only the components you need:
```bash
# Minimal installation (tables only)
mysql -u root -p < modular/01_database_setup.sql
mysql -u root -p < modular/02_create_tables.sql

# Add views for dashboard
mysql -u root -p < modular/03_create_views.sql

# Add sample data for testing
mysql -u root -p < modular/04_sample_data.sql
```

## Module Dependencies

```
01_database_setup.sql (Required first)
    ↓
02_create_tables.sql (Required for all others)  
    ↓
┌─── 03_create_views.sql (Optional, for dashboard)
├─── 04_sample_data.sql (Optional, for testing)
├─── 05_analytical_queries.sql (Reference only)
└─── 06_stored_procedures.sql (Optional, for advanced features)
```

## Enhanced Features in Modular Version

### 🛡️ **Security Enhancements**
- Enhanced site categorization (12 categories)
- Severity levels (low, medium, high, critical)
- User session tracking
- Security incident reporting

### 📊 **Advanced Analytics**
- User risk scoring algorithms
- Time-based trend analysis  
- Performance monitoring
- Compliance reporting

### ⚡ **Performance Optimizations**
- Composite indexes for complex queries
- Optimized view definitions
- Query performance monitoring
- Efficient data cleanup procedures

### 🔧 **Management Features**
- Stored procedures for common operations
- Functions for calculations and validations
- Automated cleanup and maintenance
- Comprehensive reporting capabilities

## Comparison: Monolithic vs Modular

| Aspect | Monolithic (Original) | Modular (New) |
|--------|---------------------|---------------|
| **File Count** | 2 files | 7 focused files |
| **Total Size** | 9.5 KB | Enhanced functionality |
| **Maintainability** | ⚠️ Mixed concerns | ✅ Clear separation |
| **Version Control** | ⚠️ Large diffs | ✅ Focused commits |
| **Deployment** | ⚠️ All-or-nothing | ✅ Selective deployment |
| **Team Work** | ⚠️ Merge conflicts | ✅ Parallel development |
| **Testing** | ⚠️ Hard to isolate | ✅ Module-specific tests |

## Migration from Legacy

To migrate from the original monolithic structure:

1. **Backup existing data**:
   ```bash
   mysqldump -u root -p website_monitoring > backup_$(date +%Y%m%d).sql
   ```

2. **Drop existing database**:
   ```sql
   DROP DATABASE IF EXISTS website_monitoring;
   ```

3. **Install modular version**:
   ```bash
   mysql -u root -p < modular/install_all.sql
   ```

4. **Restore data** (if needed):
   ```bash
   mysql -u root -p website_monitoring < backup_YYYYMMDD.sql
   ```

## Usage Examples

### Using Views
```sql
-- Get user blocking statistics
SELECT * FROM user_blocking_stats WHERE user_id = 1;

-- Check daily trends
SELECT * FROM daily_browsing_trends 
WHERE visit_date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY);
```

### Using Stored Procedures
```sql
-- Get user activity summary
CALL GetUserActivitySummary(1, '2025-09-01', '2025-09-10');

-- Add new blocked site
CALL AddBlockedSite('example.com', 'Policy violation', 'productivity', 'medium', 'admin');
```

### Using Functions
```sql
-- Calculate user risk score
SELECT user_id, CalculateUserRiskScore(user_id, 30) as risk_score 
FROM (SELECT DISTINCT user_id FROM sites_visited) AS users;
```

## Best Practices

1. **Always run setup first**: Execute `01_database_setup.sql` before any other module
2. **Test in development**: Use sample data module for development and testing
3. **Monitor performance**: Use analytical queries to optimize system performance
4. **Regular maintenance**: Use stored procedures for cleanup and maintenance
5. **Version control**: Track changes to individual modules for better change management

## Troubleshooting

### Common Issues:
- **Foreign key errors**: Ensure tables are created before views and procedures
- **Permission errors**: Ensure MySQL user has CREATE, INSERT, UPDATE, DELETE privileges
- **Charset issues**: Database is configured for utf8mb4 - ensure client compatibility

### Support:
- Check MySQL error log for detailed error messages
- Verify MySQL version compatibility (5.7+ recommended)
- Ensure sufficient disk space for database creation
