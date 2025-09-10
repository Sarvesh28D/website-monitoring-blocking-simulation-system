# Project Overview: Website Monitoring & Blocking Simulation

## 🎯 Project Summary

This is a comprehensive, production-quality website monitoring and blocking simulation system built with **Python**, **PHP**, and **MySQL**. The system demonstrates professional software engineering practices including security, scalability, and maintainability.

## 📦 What's Included

### 1. **Python Agent** (`python_agent/`)
- **`agent.py`**: Main simulation engine with multi-threaded user simulation
- **`config.json`**: Configuration file for database and simulation parameters
- **`requirements.txt`**: Python dependencies

**Key Features:**
- Realistic user browsing simulation with configurable patterns
- Secure database operations with connection pooling
- Comprehensive logging and error handling
- Graceful shutdown and signal handling
- Statistics tracking and reporting

### 2. **Database** (`database/`)
- **`schema.sql`**: Complete database schema with sample data
- **`queries.sql`**: Advanced analytics queries for reporting

**Key Features:**
- Optimized table structure with proper indexing
- 15 sample blocked sites with realistic blocking reasons
- Views for common statistics
- Support for user tracking and site categorization

### 3. **PHP Dashboard** (`php_dashboard/`)
- **`index.php`**: Main dashboard interface with responsive design
- **`db_connect.php`**: Secure database connection manager with PDO
- **`config.example.php`**: Configuration template

**Key Features:**
- Beautiful, responsive Bootstrap 5 interface
- Interactive Chart.js visualizations
- Advanced filtering by date range and user
- Real-time statistics with auto-refresh
- Security features: prepared statements, input sanitization

### 4. **Documentation & Setup**
- **`README.md`**: Comprehensive setup and usage guide
- **`setup.py`**: Automated setup script for easy deployment

## 🏗️ Architecture Highlights

### Security Implementation
- **SQL Injection Prevention**: All queries use prepared statements
- **Input Validation**: Comprehensive sanitization of all user inputs
- **Connection Security**: Secure database connection management
- **Session Management**: Proper session handling in PHP
- **Error Handling**: Secure error reporting without data exposure

### Performance Optimization
- **Connection Pooling**: Efficient database connection management
- **Query Optimization**: Indexed queries with proper WHERE clauses
- **Caching Strategy**: Result caching and connection reuse
- **Responsive Design**: Mobile-optimized interface
- **Background Processing**: Multi-threaded simulation engine

### Production Readiness
- **Configuration Management**: Environment-based configuration
- **Logging**: Comprehensive logging with rotation
- **Error Recovery**: Graceful error handling and recovery
- **Monitoring**: Built-in statistics and health checks
- **Scalability**: Designed for multi-user, high-volume scenarios

## 🛠️ Technology Stack

| Component | Technology | Purpose |
|-----------|------------|---------|
| **Backend Agent** | Python 3.7+ | User simulation and data generation |
| **Database** | MySQL 5.7+ | Data storage and analytics |
| **Web Interface** | PHP 7.4+ | Dashboard and reporting |
| **Frontend** | Bootstrap 5 | Responsive UI framework |
| **Charts** | Chart.js | Data visualization |
| **Icons** | Font Awesome | UI icons |

## 📊 Database Schema

### Tables
1. **`blocked_sites`**: Stores blocked websites with blocking reasons
2. **`sites_visited`**: Logs all website visits with user tracking

### Key Fields
- User identification and tracking
- Timestamp-based activity logging
- Site categorization and status tracking
- Comprehensive audit trail

## 🎨 Dashboard Features

### Visual Components
- **Statistics Cards**: Real-time visitor and blocking counts
- **Bar Chart**: Top 5 visited sites with allowed/blocked breakdown
- **Line Chart**: Daily browsing trends over time
- **Data Tables**: Detailed user statistics and blocked sites analysis

### Interactive Features
- **Date Range Filtering**: Custom date range selection
- **User Filtering**: Per-user activity analysis
- **Auto-refresh**: Live data updates every 5 minutes
- **Responsive Design**: Works on desktop, tablet, and mobile

## 🚀 Quick Start

1. **Setup Database**:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

2. **Install Python Dependencies**:
   ```bash
   cd python_agent
   pip install -r requirements.txt
   ```

3. **Configure Settings**:
   - Edit `python_agent/config.json`
   - Copy and edit `php_dashboard/config.example.php` to `config.php`

4. **Run Simulation**:
   ```bash
   cd python_agent
   python agent.py
   ```

5. **Start Dashboard**:
   ```bash
   cd php_dashboard
   php -S localhost:8000
   ```

6. **View Results**: Open http://localhost:8000

## 🔧 Customization Options

### Python Agent
- **User Count**: Adjust number of simulated users
- **Visit Frequency**: Configure visit intervals
- **Site Categories**: Add new website categories
- **Blocking Rules**: Modify blocking logic

### Dashboard
- **Chart Types**: Easy to add new visualization types
- **Filters**: Extend filtering capabilities
- **Themes**: Customize colors and styling
- **Reports**: Add new analytical views

### Database
- **Additional Fields**: Extend tables with more tracking data
- **New Queries**: Add custom analytics queries
- **Optimization**: Add indexes for specific use cases

## 📈 Use Cases

### Educational
- **Web Security Training**: Demonstrate content filtering
- **Database Design**: Show proper schema design
- **Full-Stack Development**: Complete application example

### Professional
- **Proof of Concept**: Baseline for enterprise solutions
- **Training Material**: Team training on security practices
- **System Monitoring**: Template for monitoring systems

### Development
- **Code Reference**: Example of professional coding practices
- **Architecture Template**: Scalable system design patterns
- **Security Examples**: Secure coding demonstrations

## 🛡️ Security Features

- **Prepared Statements**: 100% of database queries are parameterized
- **Input Sanitization**: All user inputs are validated and cleaned
- **Error Handling**: Secure error messages without data leakage
- **Session Security**: Proper session management and timeouts
- **Connection Security**: Secure database connection handling

## 📝 Code Quality

- **Documentation**: Comprehensive inline comments
- **Error Handling**: Robust error recovery mechanisms
- **Logging**: Detailed activity and error logging
- **Modularity**: Clean, reusable code architecture
- **Standards**: Follows industry best practices

This project demonstrates enterprise-level software development practices while maintaining clarity and educational value. It's designed to be both a learning tool and a foundation for real-world applications.
