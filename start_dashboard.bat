@echo off
REM Quick PHP Setup for Website Monitoring Dashboard
REM This script downloads and sets up PHP without requiring admin rights

echo ========================================
echo PHP Quick Setup for Website Monitoring
echo ========================================
echo.

echo This will download and set up PHP in your user folder.
echo No administrator rights required!
echo.
pause

REM Create PHP directory in user folder
set PHP_DIR=%USERPROFILE%\php
if not exist "%PHP_DIR%" mkdir "%PHP_DIR%"

echo Creating PHP directory at: %PHP_DIR%
echo.

echo ========================================
echo MANUAL DOWNLOAD REQUIRED
echo ========================================
echo.
echo Please follow these steps:
echo.
echo 1. Open your web browser
echo 2. Go to: https://windows.php.net/download/
echo 3. Download: "PHP 8.3 VC15 x64 Thread Safe" (zip file)
echo 4. Extract the downloaded zip file to: %PHP_DIR%
echo 5. Come back here and press any key to continue
echo.
pause

REM Check if PHP was extracted
if exist "%PHP_DIR%\php.exe" (
    echo ✅ PHP found at %PHP_DIR%\php.exe
) else (
    echo ❌ PHP not found. Please make sure you extracted PHP to %PHP_DIR%
    echo Expected file: %PHP_DIR%\php.exe
    pause
    exit /b 1
)

REM Create php.ini
echo Creating PHP configuration...
copy "%PHP_DIR%\php.ini-production" "%PHP_DIR%\php.ini" >nul 2>&1

REM Enable required extensions
echo Enabling required extensions...
powershell -Command "(Get-Content '%PHP_DIR%\php.ini') -replace ';extension=pdo_mysql', 'extension=pdo_mysql' | Set-Content '%PHP_DIR%\php.ini'"
powershell -Command "(Get-Content '%PHP_DIR%\php.ini') -replace ';extension=mysqli', 'extension=mysqli' | Set-Content '%PHP_DIR%\php.ini'"

echo.
echo ========================================
echo TESTING PHP INSTALLATION
echo ========================================
echo.

REM Test PHP
"%PHP_DIR%\php.exe" --version
if %errorlevel% equ 0 (
    echo ✅ PHP is working correctly!
) else (
    echo ❌ PHP test failed
    pause
    exit /b 1
)

echo.
echo ========================================
echo STARTING DASHBOARD
echo ========================================
echo.

REM Change to dashboard directory
cd /d "%~dp0php_dashboard"

echo Starting PHP development server...
echo Dashboard will be available at: http://localhost:8000
echo Press Ctrl+C to stop the server
echo.

"%PHP_DIR%\php.exe" -S localhost:8000

echo.
echo Dashboard stopped.
pause
