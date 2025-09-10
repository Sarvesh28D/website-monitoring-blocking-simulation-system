# PHP Installation Guide for Windows

## Option 1: Install PHP (Recommended)

### Method A: Using Chocolatey (Easiest)
1. Install Chocolatey (if not already installed):
   ```powershell
   Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
   ```

2. Install PHP:
   ```powershell
   choco install php
   ```

3. Restart your terminal and run:
   ```powershell
   php -S localhost:8000
   ```

### Method B: Manual Installation
1. Download PHP from: https://windows.php.net/download/
2. Extract to C:\php
3. Add C:\php to your PATH environment variable
4. Copy php.ini-production to php.ini
5. Enable PDO MySQL extension in php.ini:
   ```ini
   extension=pdo_mysql
   extension=mysqli
   ```

## Option 2: Use XAMPP (Full Stack)
1. Download XAMPP from: https://www.apachefriends.org/
2. Install and start Apache
3. Copy the php_dashboard folder to C:\xampp\htdocs\
4. Access via: http://localhost/php_dashboard/

## Option 3: Use Database Queries Directly (Quick Alternative)

Since you have MySQL working, you can run the analytics queries directly:
```sql
-- Show current statistics
mysql -u root -p -e "
USE website_monitoring;
SELECT 
    COUNT(*) as total_visits,
    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_visits,
    COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_visits,
    ROUND(COUNT(CASE WHEN status = 'blocked' THEN 1 END) * 100.0 / COUNT(*), 2) as block_percentage
FROM sites_visited;
"

-- Show top visited sites
mysql -u root -p -e "
USE website_monitoring;
SELECT 
    site_name,
    COUNT(*) as visit_count,
    status
FROM sites_visited 
GROUP BY site_name, status 
ORDER BY visit_count DESC 
LIMIT 10;
"

-- Show user activity
mysql -u root -p -e "
USE website_monitoring;
SELECT 
    user_id,
    COUNT(*) as total_visits,
    COUNT(CASE WHEN status = 'blocked' THEN 1 END) as blocked_visits,
    COUNT(CASE WHEN status = 'allowed' THEN 1 END) as allowed_visits
FROM sites_visited 
GROUP BY user_id 
ORDER BY user_id;
"
```

## Current System Status ✅
- ✅ Database: Successfully created and populated
- ✅ Python Agent: Running and generating data
- ✅ Sample Data: 20 visits logged (19 allowed, 1 blocked)
- ⏳ PHP Dashboard: Waiting for PHP installation

## Quick Test Command
After installing PHP, test with:
```powershell
cd php_dashboard
php -S localhost:8000
```
Then open: http://localhost:8000
