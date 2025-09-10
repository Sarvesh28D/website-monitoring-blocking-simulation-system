#!/usr/bin/env python3
"""
Website Monitoring System - Setup Script
=========================================

This script helps set up and verify the website monitoring system.
It checks dependencies, configures the database, and validates the installation.

Author: Expert Software Engineer
Created: September 10, 2025
"""

import os
import sys
import json
import subprocess
import mysql.connector
from mysql.connector import Error
import importlib.util

def print_header(title):
    """Print a formatted header"""
    print("\n" + "="*60)
    print(f" {title}")
    print("="*60)

def print_step(step, description):
    """Print a formatted step"""
    print(f"\n[Step {step}] {description}")
    print("-" * 40)

def check_python_version():
    """Check if Python version is compatible"""
    print("Checking Python version...")
    if sys.version_info < (3, 7):
        print(f"❌ Python 3.7+ required. Current version: {sys.version}")
        return False
    print(f"✅ Python version: {sys.version}")
    return True

def check_python_dependencies():
    """Check if required Python packages are installed"""
    print("Checking Python dependencies...")
    
    required_packages = [
        'mysql.connector',
        'json',
        'datetime',
        'random',
        'time',
        'logging',
        'threading'
    ]
    
    missing_packages = []
    
    for package in required_packages:
        try:
            if package == 'mysql.connector':
                import mysql.connector
            else:
                importlib.import_module(package)
            print(f"✅ {package}")
        except ImportError:
            print(f"❌ {package}")
            missing_packages.append(package)
    
    if missing_packages:
        print(f"\n❌ Missing packages: {', '.join(missing_packages)}")
        print("Install with: pip install mysql-connector-python")
        return False
    
    return True

def install_python_dependencies():
    """Install Python dependencies"""
    print("Installing Python dependencies...")
    
    try:
        subprocess.check_call([
            sys.executable, '-m', 'pip', 'install', 
            'mysql-connector-python', 'python-dotenv', 'schedule', 'psutil'
        ])
        print("✅ Dependencies installed successfully")
        return True
    except subprocess.CalledProcessError as e:
        print(f"❌ Failed to install dependencies: {e}")
        return False

def check_mysql_connection(config):
    """Test MySQL connection"""
    print("Testing MySQL connection...")
    
    try:
        connection = mysql.connector.connect(**config)
        cursor = connection.cursor()
        cursor.execute("SELECT VERSION()")
        version = cursor.fetchone()
        print(f"✅ MySQL connection successful. Version: {version[0]}")
        cursor.close()
        connection.close()
        return True
    except Error as e:
        print(f"❌ MySQL connection failed: {e}")
        return False

def create_database_schema(config):
    """Create database schema"""
    print("Creating database schema...")
    
    schema_file = os.path.join('database', 'schema.sql')
    if not os.path.exists(schema_file):
        print(f"❌ Schema file not found: {schema_file}")
        return False
    
    try:
        # Read schema file
        with open(schema_file, 'r', encoding='utf-8') as f:
            schema_sql = f.read()
        
        # Split into individual statements
        statements = [stmt.strip() for stmt in schema_sql.split(';') if stmt.strip()]
        
        # Connect without specifying database first
        temp_config = config.copy()
        if 'database' in temp_config:
            del temp_config['database']
        
        connection = mysql.connector.connect(**temp_config)
        cursor = connection.cursor()
        
        for statement in statements:
            if statement:
                try:
                    cursor.execute(statement)
                    print(f"✅ Executed: {statement[:50]}...")
                except Error as e:
                    if "already exists" in str(e).lower():
                        print(f"⚠️  Already exists: {statement[:50]}...")
                    else:
                        print(f"❌ Failed: {statement[:50]}... Error: {e}")
        
        connection.commit()
        cursor.close()
        connection.close()
        
        print("✅ Database schema created successfully")
        return True
        
    except Exception as e:
        print(f"❌ Failed to create database schema: {e}")
        return False

def verify_database_setup(config):
    """Verify database setup"""
    print("Verifying database setup...")
    
    try:
        connection = mysql.connector.connect(**config)
        cursor = connection.cursor()
        
        # Check tables exist
        cursor.execute("SHOW TABLES")
        tables = [table[0] for table in cursor.fetchall()]
        
        expected_tables = ['blocked_sites', 'sites_visited']
        for table in expected_tables:
            if table in tables:
                print(f"✅ Table '{table}' exists")
            else:
                print(f"❌ Table '{table}' missing")
                return False
        
        # Check sample data
        cursor.execute("SELECT COUNT(*) FROM blocked_sites")
        blocked_count = cursor.fetchone()[0]
        print(f"✅ Blocked sites sample data: {blocked_count} records")
        
        cursor.close()
        connection.close()
        return True
        
    except Error as e:
        print(f"❌ Database verification failed: {e}")
        return False

def create_config_files():
    """Create configuration files if they don't exist"""
    print("Creating configuration files...")
    
    # Python agent config
    python_config_path = os.path.join('python_agent', 'config.json')
    if not os.path.exists(python_config_path):
        default_config = {
            "database": {
                "host": "localhost",
                "database": "website_monitoring",
                "user": "root",
                "password": "",
                "charset": "utf8mb4",
                "autocommit": True
            },
            "simulation": {
                "num_users": 5,
                "min_visit_interval": 1,
                "max_visit_interval": 10,
                "max_runtime_hours": 24
            }
        }
        
        with open(python_config_path, 'w') as f:
            json.dump(default_config, f, indent=4)
        print(f"✅ Created Python config: {python_config_path}")
    else:
        print(f"✅ Python config exists: {python_config_path}")
    
    # PHP config
    php_config_path = os.path.join('php_dashboard', 'config.php')
    php_example_path = os.path.join('php_dashboard', 'config.example.php')
    
    if not os.path.exists(php_config_path) and os.path.exists(php_example_path):
        import shutil
        shutil.copy(php_example_path, php_config_path)
        print(f"✅ Created PHP config: {php_config_path}")
        print("⚠️  Please edit php_dashboard/config.php with your database credentials")
    else:
        print(f"✅ PHP config exists: {php_config_path}")

def run_sample_simulation():
    """Run a small sample simulation"""
    print("Running sample simulation...")
    
    try:
        # Change to python_agent directory
        original_dir = os.getcwd()
        agent_dir = os.path.join(original_dir, 'python_agent')
        
        if os.path.exists(agent_dir):
            os.chdir(agent_dir)
            
            # Import and run agent
            sys.path.insert(0, agent_dir)
            from agent import MonitoringAgent
            
            agent = MonitoringAgent()
            agent._initialize_users()
            agent.run_single_batch(10)  # Run 10 sample visits
            
            os.chdir(original_dir)
            print("✅ Sample simulation completed successfully")
            return True
        else:
            print(f"❌ Agent directory not found: {agent_dir}")
            return False
            
    except Exception as e:
        print(f"❌ Sample simulation failed: {e}")
        os.chdir(original_dir)
        return False

def main():
    """Main setup function"""
    print_header("Website Monitoring System - Setup Script")
    
    # Step 1: Check Python version
    print_step(1, "Checking Python Environment")
    if not check_python_version():
        print("\n❌ Setup failed: Python version incompatible")
        return False
    
    # Step 2: Check/Install Python dependencies
    print_step(2, "Checking Python Dependencies")
    if not check_python_dependencies():
        print("\nAttempting to install dependencies...")
        if not install_python_dependencies():
            print("\n❌ Setup failed: Could not install Python dependencies")
            return False
    
    # Step 3: Create config files
    print_step(3, "Creating Configuration Files")
    create_config_files()
    
    # Step 4: Get database configuration
    print_step(4, "Database Configuration")
    
    config_file = os.path.join('python_agent', 'config.json')
    if os.path.exists(config_file):
        with open(config_file, 'r') as f:
            config = json.load(f)
        db_config = config['database']
    else:
        print("❌ Configuration file not found")
        return False
    
    # Step 5: Test database connection
    print_step(5, "Testing Database Connection")
    if not check_mysql_connection(db_config):
        print("\n❌ Setup failed: Cannot connect to MySQL")
        print("Please ensure MySQL is running and credentials are correct")
        return False
    
    # Step 6: Create database schema
    print_step(6, "Creating Database Schema")
    if not create_database_schema(db_config):
        print("\n❌ Setup failed: Could not create database schema")
        return False
    
    # Step 7: Verify database setup
    print_step(7, "Verifying Database Setup")
    if not verify_database_setup(db_config):
        print("\n❌ Setup failed: Database verification failed")
        return False
    
    # Step 8: Run sample simulation
    print_step(8, "Running Sample Simulation")
    if not run_sample_simulation():
        print("\n⚠️  Sample simulation failed, but setup may still be functional")
    
    # Success message
    print_header("Setup Complete!")
    print("✅ Website Monitoring System setup completed successfully!")
    print("\nNext steps:")
    print("1. Edit python_agent/config.json with your database credentials")
    print("2. Edit php_dashboard/config.php with your database credentials")
    print("3. Start the Python agent: cd python_agent && python agent.py")
    print("4. Start the PHP dashboard: cd php_dashboard && php -S localhost:8000")
    print("5. Open http://localhost:8000 in your browser")
    
    return True

if __name__ == "__main__":
    try:
        success = main()
        sys.exit(0 if success else 1)
    except KeyboardInterrupt:
        print("\n\n❌ Setup interrupted by user")
        sys.exit(1)
    except Exception as e:
        print(f"\n\n❌ Setup failed with unexpected error: {e}")
        sys.exit(1)
