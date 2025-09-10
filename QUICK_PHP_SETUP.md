# Quick PHP Setup Instructions

## 🚀 Simple 3-Step PHP Installation (No Admin Required!)

### Step 1: Download PHP
1. **Open your browser** and go to: https://windows.php.net/download/
2. **Download** the latest "PHP 8.3 VC15 x64 Thread Safe" ZIP file
3. **Extract** the entire contents to: `C:\Users\ashis\php`

### Step 2: Enable MySQL Extensions
1. **Navigate** to `C:\Users\ashis\php`
2. **Copy** `php.ini-production` and **rename** it to `php.ini`
3. **Open** `php.ini` in Notepad
4. **Find** and **uncomment** these lines (remove the semicolon):
   ```ini
   ;extension=pdo_mysql    →    extension=pdo_mysql
   ;extension=mysqli       →    extension=mysqli
   ```
5. **Save** the file

### Step 3: Test and Run
Open PowerShell and run:
```powershell
C:\Users\ashis\php\php.exe --version
cd C:\Users\ashis\OneDrive\Desktop\Clixnet\php_dashboard
C:\Users\ashis\php\php.exe -S localhost:8000
```

## 🎯 Alternative: Use XAMPP (Easiest Option!)

If the above seems complex, just:
1. **Download XAMPP**: https://www.apachefriends.org/download.html
2. **Install XAMPP** (includes PHP, Apache, MySQL)
3. **Copy** `php_dashboard` folder to `C:\xampp\htdocs\`
4. **Start Apache** from XAMPP Control Panel
5. **Open**: http://localhost/php_dashboard/

## 🔧 Quick Test Commands

After PHP is set up, test with:
```powershell
# Test PHP
C:\Users\ashis\php\php.exe --version

# Test database connection
C:\Users\ashis\php\php.exe -r "try { new PDO('mysql:host=localhost;dbname=website_monitoring', 'root', 'Sarvesh@2004'); echo 'Database connection: OK'; } catch(Exception $e) { echo 'Database error: ' . $e->getMessage(); }"

# Start dashboard
cd C:\Users\ashis\OneDrive\Desktop\Clixnet\php_dashboard
C:\Users\ashis\php\php.exe -S localhost:8000
```

## 📊 Current System Status
✅ **Python Agent**: Working perfectly - generating realistic data  
✅ **MySQL Database**: 20 visits logged, blocking system active  
✅ **Configuration**: All config files set up with your credentials  
⏳ **PHP Dashboard**: Waiting for PHP installation  

## 💡 What You'll See in the Dashboard
- **Beautiful charts** showing top visited sites
- **Real-time statistics** with auto-refresh
- **User activity breakdown** with filtering options
- **Daily trends** with interactive visualizations
- **Blocked sites analysis** with reasons

The system is already generating valuable data - the dashboard will just provide a professional web interface to view it all!
