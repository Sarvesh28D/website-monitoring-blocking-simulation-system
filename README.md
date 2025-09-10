# Website Monitoring & Blocking Simulation System

A professional, production-quality system for monitoring website visits and simulating content blocking using **Python**, **PHP**, and **MySQL**.

![Project Status](https://img.shields.io/badge/Status-Complete-success)
![Python](https://img.shields.io/badge/Python-3.7%2B-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)
![License](https://img.shields.io/badge/License-MIT-green)

## Features

- **Python Agent**: Multi-threaded user simulation with realistic browsing patterns
- **MySQL Database**: Secure data storage with optimized queries and proper indexing
- **PHP Dashboard**: Responsive web interface with interactive charts and analytics
- **Weekly Reports**: Comprehensive top 10 sites analysis with detailed metrics and insights
- **Real-time Analytics**: Live statistics, trends, and comprehensive reporting
- **Security Implementation**: Prepared statements, input sanitization, and secure coding practices
- **Performance Optimization**: Connection pooling, caching, and optimized database operations

## Architecture Overview

This system demonstrates enterprise-level software development practices including:

- **Professional Modular Design**: Complete separation into focused components for maintainability
- **Security Best Practices**: SQL injection prevention, input validation, and secure authentication
- **Scalable Architecture**: Connection pooling, optimized queries, and efficient resource management
- **Professional Code Quality**: Comprehensive documentation, error handling, and logging

### Modular Architecture Benefits

- **Python Agent Modules**: Separated into database management, monitoring logic, user simulation, and website generation
- **PHP Dashboard Modules**: Divided into controller logic, chart management, view rendering, and database connectivity
- **Database Modules**: Modularized SQL structure with individual components for tables, views, procedures, and triggers
- **Enhanced Maintainability**: Each module has a single responsibility, making development and debugging easier
- **Version Control Friendly**: Individual modules can be updated independently without affecting the entire system
- **Team Development**: Multiple developers can work on different modules simultaneously

### Key Metrics Tracked
- User activity patterns and browsing behavior analysis
- Content blocking effectiveness with detailed categorization
- Daily, weekly, and custom date range trend analysis
- Comprehensive security and audit trail monitoring

## Requirements

### System Requirements
- **Python**: 3.7 or higher
- **PHP**: 7.4 or higher with PDO MySQL extension  
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Web Server**: Apache, Nginx, or PHP built-in server

### Python Dependencies
```bash
mysql-connector-python>=8.1.0
python-dotenv>=1.0.0
schedule>=1.2.0
psutil>=5.9.5
```

## Installation & Setup

### 1. Database Setup

Create and configure the MySQL database using the modular structure:

```sql
mysql -u root -p < database/modular/install_all.sql
```

This installs the complete modular database system with:
- Core tables for monitoring data
- Analytics views for performance insights  
- Stored procedures for complex operations
- Optimized indexes for fast queries
- Automated triggers for data integrity

For selective installation, see `database/modular/README.md` for individual module setup.

### 2. Python Agent Configuration

```bash
cd python_agent
pip install -r requirements.txt
cp config.example.json config.json
```

Edit `config.json` with your database credentials:

```json
{
    "database": {
        "host": "localhost",
        "database": "website_monitoring",
        "user": "your_username",
        "password": "your_password"
    }
}
```

### 3. PHP Dashboard Setup

Configure the web dashboard:

```bash
cd php_dashboard
cp config.example.php config.php
```

Edit `config.php` with your database credentials and start the server:

```bash
php -S localhost:8000
```

Access the dashboard at `http://localhost:8000`

## Usage

### Running the Simulation

The Python agent offers multiple simulation modes:

```bash
cd python_agent
python agent.py
```

**Available modes:**
1. **Continuous Simulation**: Runs indefinitely with realistic user patterns
2. **Batch Mode**: Generates a specific number of visits for testing
3. **Custom Mode**: User-defined parameters for specific scenarios

### Dashboard Features

The web interface provides:

- **Real-time Statistics**: Live visitor counts and blocking rates
- **Interactive Charts**: Site popularity and trend visualizations  
- **Weekly Top Sites Report**: Comprehensive analysis of the top 10 most visited websites with:
  - Total visits and unique users per site
  - Block rate analysis with color-coded badges
  - Daily activity breakdown and peak usage days
  - Interactive charts showing visits vs. users
  - Time period analysis and user engagement metrics
- **Advanced Filtering**: Date ranges, user-specific views, and custom queries
- **Export Capabilities**: Data export for further analysis
- **Responsive Design**: Optimized for desktop and mobile devices

### Analytics Queries

Advanced analytics are available through SQL queries:

```bash
mysql -u root -p website_monitoring < database/queries.sql
```

Key analytics include:
- Weekly top visited sites per user
- Blocked access attempts analysis  
- Daily browsing trend patterns
- User behavior profiling

## Project Structure

```
website-monitoring/
├── python_agent/
│   ├── agent.py              # Main coordinator and entry point
│   ├── database_manager.py   # Database connection and operations
│   ├── monitoring_agent.py   # Core monitoring and logging logic
│   ├── user_simulator.py     # User behavior simulation
│   ├── website_generator.py  # Website URL generation
│   ├── config.example.json   # Configuration template
│   └── requirements.txt      # Python dependencies
├── database/
│   └── modular/              # Modular database structure
│       ├── install_all.sql  # Master installer for all modules
│       ├── 01_database_setup.sql      # Database and user creation
│       ├── 02_create_tables.sql       # Core table structures
│       ├── 03_create_views.sql        # Analytics views
│       ├── 04_sample_data.sql         # Sample data for testing
│       ├── 05_create_indexes.sql      # Performance indexes
│       ├── 06_stored_procedures.sql   # Database procedures
│       └── 07_create_triggers.sql     # Automated triggers
├── php_dashboard/
│   ├── index.php            # Main dashboard entry point
│   ├── DashboardController.php  # Dashboard logic controller
│   ├── ChartDataManager.php     # Chart data processing
│   ├── ViewRenderer.php         # View rendering and output
│   ├── db_connect.php          # Database connection manager
│   └── config.example.php      # Configuration template
└── README.md
```

## Security Features

This system implements comprehensive security measures:

- **SQL Injection Prevention**: All database queries use prepared statements
- **Input Validation**: Comprehensive sanitization of user inputs
- **Secure Authentication**: Proper credential handling and session management
- **Error Handling**: Secure error reporting without information disclosure
- **Connection Security**: Encrypted database connections and connection pooling

## Performance Optimizations

The system is designed for high performance:

- **Database Indexing**: Optimized indexes for fast query execution
- **Connection Pooling**: Efficient database connection management
- **Query Optimization**: Carefully crafted queries with proper WHERE clauses
- **Caching Strategy**: Result caching for frequently accessed data
- **Asynchronous Processing**: Multi-threaded simulation for scalability

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For questions, issues, or contributions:
- Create an issue on GitHub
- Review the documentation in each component directory
- Check the troubleshooting section in the setup guides

## Acknowledgments

Built with modern web technologies and following industry best practices for security, performance, and maintainability.

## 📋 Requirements

### System Requirements
- **Python**: 3.7 or higher
- **PHP**: 7.4 or higher with PDO MySQL extension
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Web Server**: Apache, Nginx, or built-in PHP server

### Python Dependencies
- `mysql-connector-python`
- `python-dotenv` (optional)
- `schedule` (optional)
- `psutil` (optional)

## 🛠️ Installation & Setup

### 1. Database Setup

1. **Create Database**:
   ```sql
   mysql -u root -p < database/schema.sql
   ```

2. **Verify Installation**:
   ```sql
   mysql -u root -p
   USE website_monitoring;
   SHOW TABLES;
   SELECT COUNT(*) FROM blocked_sites;
   ```

### 2. Python Agent Setup

1. **Navigate to Python Agent Directory**:
   ```bash
   cd python_agent
   ```

2. **Install Dependencies**:
   ```bash
   pip install -r requirements.txt
   ```

3. **Configure Database Connection**:
   Edit `config.json`:
   ```json
   {
       "database": {
           "host": "localhost",
           "database": "website_monitoring",
           "user": "your_username",
           "password": "your_password",
           "charset": "utf8mb4",
           "autocommit": true
       },
       "simulation": {
           "num_users": 5,
           "min_visit_interval": 1,
           "max_visit_interval": 10,
           "max_runtime_hours": 24
       }
   }
   ```

4. **Run the Agent**:
   ```bash
   python agent.py
   ```

### 3. PHP Dashboard Setup

1. **Configure Web Server**:
   - **Apache**: Point document root to `php_dashboard/`
   - **Nginx**: Configure to serve PHP files from `php_dashboard/`
   - **Built-in Server** (for testing):
     ```bash
     cd php_dashboard
     php -S localhost:8000
     ```

2. **Configure Database Connection**:
   Copy and edit the configuration file:
   ```bash
   cp config.example.php config.php
   ```
   
   Edit `config.php` with your database credentials.

3. **Set Permissions** (Linux/Mac):
   ```bash
   chmod 644 *.php
   chmod 600 config.php
   ```

4. **Access Dashboard**:
   Open `http://localhost:8000` (or your configured URL)

## 📁 Project Structure

```
Clixnet/
├── python_agent/
│   ├── agent.py              # Main simulation agent
│   ├── config.json           # Configuration file
│   ├── requirements.txt      # Python dependencies
│   └── *.log                # Log files (generated)
├── database/
│   ├── schema.sql            # Database schema and sample data
│   └── queries.sql           # Advanced analytics queries
└── php_dashboard/
    ├── index.php             # Main dashboard interface
    ├── db_connect.php        # Database connection manager
    ├── config.example.php    # Configuration template
    └── config.php            # Your configuration (create this)
```

## 🎯 Usage

### Running the Python Agent

The agent supports multiple simulation modes:

1. **Continuous Simulation** (default):
   ```bash
   python agent.py
   ```
   - Runs indefinitely until stopped
   - Simulates realistic browsing patterns
   - Logs all activity to database

2. **Single Batch**:
   - Select option 2 when prompted
   - Runs 50 simulated visits
   - Useful for testing

3. **Custom Batch**:
   - Select option 3 when prompted
   - Specify number of visits
   - Quick testing with custom volume

### Using the Dashboard

1. **Access**: Open the dashboard URL in your browser
2. **Filter Data**: Use date range and user filters
3. **View Statistics**: Monitor user activity and blocking rates
4. **Analyze Trends**: Review charts and daily patterns
5. **Export Data**: Use SQL queries for custom reports

### Key Dashboard Features

- **Real-time Statistics**: Live visitor counts and blocking rates
- **Interactive Charts**: Top sites and daily trends visualization
- **Weekly Reports**: Comprehensive top 10 sites analysis (see below)
- **User Analysis**: Per-user activity breakdown
- **Date Filtering**: Custom date range selection
- **Responsive Design**: Works on desktop and mobile devices

## 📈 Weekly Top Sites Report

**New Feature**: The dashboard now includes a comprehensive weekly report showing the top 10 most visited websites with detailed analytics:

### Report Components

1. **Interactive Chart**: 
   - Bar chart displaying total visits and unique users
   - Responsive design with rotation for better readability
   - Real-time data visualization using Chart.js

2. **Detailed Analytics Table**:
   - **Ranking**: Sites ranked by total visits
   - **Visit Metrics**: Total visits with activity period (X/7 days)
   - **User Engagement**: Unique user count with color-coded badges
   - **Security Analysis**: Allow/block ratio with blocked visit counts
   - **Block Rate**: Percentage with color-coded severity indicators
   - **Time Analysis**: First visit to last visit period tracking

3. **Summary Dashboard**:
   - Total sites analyzed
   - Most active website
   - Highest block rate identification
   - Report date range

### Badge System

- **User Count Badges**: 
  - Green (4+ users): High engagement
  - Yellow (2-3 users): Medium engagement  
  - Gray (1 user): Low engagement

- **Block Rate Badges**:
  - Red (50%+): High risk sites
  - Yellow (20-49%): Medium risk sites
  - Blue (1-19%): Low risk sites
  - Green (0%): Clean sites

- **Activity Badges**:
  - Green (6-7 days): Highly active
  - Yellow (3-5 days): Moderately active
  - Gray (1-2 days): Low activity

## 📊 Database Queries

The system includes advanced analytics queries in `database/queries.sql`:

1. **Weekly Top Sites**: Most visited sites per user
2. **Blocking Analysis**: Detailed blocking statistics
3. **Daily Trends**: Time-based browsing patterns
4. **User Behavior**: Individual user analysis
5. **Site Popularity**: Overall site visit rankings

Example usage:
```sql
-- Load and run analytics queries
mysql -u root -p website_monitoring < database/queries.sql
```

## 🔧 Configuration Options

### Python Agent Configuration

```json
{
    "database": {
        "host": "localhost",           # Database host
        "database": "website_monitoring", # Database name
        "user": "root",                # Database user
        "password": "",                # Database password
        "charset": "utf8mb4",          # Character set
        "autocommit": true             # Auto-commit transactions
    },
    "simulation": {
        "num_users": 5,                # Number of simulated users
        "min_visit_interval": 1,       # Minimum seconds between visits
        "max_visit_interval": 10,      # Maximum seconds between visits
        "max_runtime_hours": 24        # Maximum runtime in hours
    }
}
```

### PHP Dashboard Configuration

```php
return [
    'host' => 'localhost',            // Database host
    'dbname' => 'website_monitoring', // Database name
    'username' => 'root',             // Database username
    'password' => '',                 // Database password
    'timezone' => 'UTC',              // Application timezone
    'items_per_page' => 50,           // Pagination limit
    'enable_auto_refresh' => true,    // Auto-refresh dashboard
    'auto_refresh_interval' => 300000 // Refresh interval (ms)
];
```

## 🛡️ Security Features

### Database Security
- **Prepared Statements**: All queries use parameterized statements
- **Input Sanitization**: All user inputs are validated and sanitized
- **Connection Pooling**: Efficient connection management
- **Error Handling**: Comprehensive error logging without data exposure

### Application Security
- **CSRF Protection**: Cross-Site Request Forgery protection
- **XSS Prevention**: Output encoding and validation
- **Session Management**: Secure session handling
- **Access Control**: Role-based access (extensible)

## 📈 Monitoring & Logging

### Python Agent Logging
- **File Logging**: Detailed logs written to `agent.log`
- **Console Output**: Real-time status updates
- **Statistics Tracking**: Performance metrics and error counts
- **Graceful Shutdown**: Signal handling for clean stops

### Dashboard Monitoring
- **Real-time Updates**: Live data refresh
- **Performance Metrics**: Query execution tracking
- **Error Reporting**: Comprehensive error handling
- **Usage Statistics**: User interaction tracking

## 🚀 Production Deployment

### Environment Setup
1. **Use Environment Variables**: Store sensitive data in environment variables
2. **Enable HTTPS**: Secure all web traffic
3. **Configure Firewall**: Restrict database access
4. **Set Up Monitoring**: Use tools like Nagios or Prometheus
5. **Regular Backups**: Automated database backups

### Performance Optimization
1. **Database Indexing**: Optimize query performance
2. **Connection Pooling**: Efficient resource usage
3. **Caching**: Implement Redis/Memcached for frequently accessed data
4. **Load Balancing**: Distribute traffic across multiple servers

### Security Hardening
1. **Database**: Create dedicated user with minimal privileges
2. **Web Server**: Configure security headers and SSL
3. **Application**: Enable all security features in config
4. **Network**: Use VPN or private networks for database connections

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Failed**:
   - Verify MySQL service is running
   - Check credentials in config files
   - Ensure database exists and user has privileges

2. **Python Agent Won't Start**:
   - Install required dependencies: `pip install -r requirements.txt`
   - Check database connectivity
   - Verify config.json syntax

3. **Dashboard Shows Errors**:
   - Check PHP error logs
   - Verify database connection in `db_connect.php`
   - Ensure proper file permissions

4. **No Data in Dashboard**:
   - Run Python agent to generate sample data
   - Check database tables have data
   - Verify date filters in dashboard

### Performance Issues

1. **Slow Queries**:
   - Check database indexes
   - Optimize date range queries
   - Consider query result caching

2. **High Memory Usage**:
   - Reduce batch sizes in Python agent
   - Implement pagination in dashboard
   - Monitor connection pool sizes

## 📞 Support

For issues and questions:
1. Check the troubleshooting section above
2. Review log files for error details
3. Verify configuration settings
4. Test with minimal data sets

## 📝 License

This project is created for educational and demonstration purposes. Please ensure compliance with your organization's security and data policies when deploying in production environments.

## 🔄 Version History

- **v1.1** - Weekly Reports Enhancement (September 10, 2025)
  - **NEW**: Comprehensive weekly top 10 sites report
  - **NEW**: Interactive chart visualization for weekly data
  - **NEW**: Detailed analytics table with ranking system
  - **NEW**: Color-coded badge system for quick insights
  - **NEW**: Summary dashboard with key metrics
  - **ENHANCED**: Modular dashboard architecture
  - **ENHANCED**: Responsive design improvements
  - **ENHANCED**: Database query optimizations

- **v1.0** - Initial release with core functionality
  - Python simulation agent
  - MySQL database schema
  - PHP dashboard with charts
  - Security implementations
  - Production-ready features
