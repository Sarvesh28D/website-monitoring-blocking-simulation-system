@echo off
REM Website Monitoring System - Database Setup Script for Windows
REM This script helps set up the MySQL database with proper authentication

echo ========================================
echo Website Monitoring Database Setup
echo ========================================
echo.

echo Checking MySQL installation...
mysql --version
if %errorlevel% neq 0 (
    echo ERROR: MySQL client not found in PATH
    echo Please install MySQL or add it to your PATH
    pause
    exit /b 1
)

echo.
echo ========================================
echo MySQL Authentication Options
echo ========================================
echo.
echo 1. Connect with username and password
echo 2. Connect using Windows Authentication (if configured)
echo 3. Connect with custom host/port
echo 4. Show MySQL connection troubleshooting
echo.
set /p choice="Select option (1-4): "

if "%choice%"=="1" goto option1
if "%choice%"=="2" goto option2
if "%choice%"=="3" goto option3
if "%choice%"=="4" goto option4
goto option1

:option1
echo.
echo === Option 1: Username and Password ===
set /p username="Enter MySQL username (default: root): "
if "%username%"=="" set username=root
set /p password="Enter MySQL password: "

echo.
echo Testing connection...
mysql -u %username% -p%password% -e "SELECT 'Connection successful!' as Status;"
if %errorlevel% neq 0 (
    echo ERROR: Connection failed with provided credentials
    echo Please check your username and password
    pause
    exit /b 1
)

echo.
echo Creating database...
mysql -u %username% -p%password% < database\schema.sql
if %errorlevel% neq 0 (
    echo ERROR: Failed to create database schema
    pause
    exit /b 1
)

echo.
echo Verifying database setup...
mysql -u %username% -p%password% -e "USE website_monitoring; SHOW TABLES; SELECT COUNT(*) as 'Blocked Sites' FROM blocked_sites;"

echo.
echo SUCCESS: Database setup completed!
echo.
echo Next steps:
echo 1. Update python_agent\config.json with your database credentials
echo 2. Update php_dashboard\config.php with your database credentials
echo 3. Run: cd python_agent ^&^& python agent.py
echo 4. Run: cd php_dashboard ^&^& php -S localhost:8000
pause
exit /b 0

:option2
echo.
echo === Option 2: Windows Authentication ===
echo Attempting Windows Authentication...
mysql --default-auth=authentication_windows -e "SELECT 'Connection successful!' as Status;"
if %errorlevel% neq 0 (
    echo ERROR: Windows Authentication not configured
    echo Please configure MySQL for Windows Authentication or use Option 1
    pause
    exit /b 1
)

mysql --default-auth=authentication_windows < database\schema.sql
echo Database setup completed with Windows Authentication!
pause
exit /b 0

:option3
echo.
echo === Option 3: Custom Host/Port ===
set /p host="Enter MySQL host (default: localhost): "
if "%host%"=="" set host=localhost
set /p port="Enter MySQL port (default: 3306): "
if "%port%"=="" set port=3306
set /p username="Enter MySQL username (default: root): "
if "%username%"=="" set username=root
set /p password="Enter MySQL password: "

mysql -h %host% -P %port% -u %username% -p%password% < database\schema.sql
pause
exit /b 0

:option4
echo.
echo ========================================
echo MySQL Connection Troubleshooting
echo ========================================
echo.
echo Common issues and solutions:
echo.
echo 1. ERROR 1045 - Access denied:
echo    - Check username and password
echo    - Ensure MySQL service is running
echo    - Try: net start mysql80 (or mysql)
echo.
echo 2. ERROR 2003 - Can't connect to server:
echo    - Check if MySQL service is running
echo    - Verify MySQL is installed
echo    - Check firewall settings
echo.
echo 3. Command not found:
echo    - Add MySQL bin directory to PATH
echo    - Typically: C:\Program Files\MySQL\MySQL Server 8.0\bin
echo.
echo 4. Reset MySQL root password (if needed):
echo    - Stop MySQL service
echo    - Start with --skip-grant-tables
echo    - Connect and run: ALTER USER 'root'@'localhost' IDENTIFIED BY 'newpassword';
echo.
echo Current MySQL service status:
sc query mysql80
echo.
echo If MySQL service is not running, try:
echo net start mysql80
echo.
pause
exit /b 0
