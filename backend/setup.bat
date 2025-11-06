@echo off
echo E-Learning System Database and Backend Setup
echo ============================================

:: Check if XAMPP is installed
if not exist "C:\xampp\mysql\bin\mysql.exe" (
  echo XAMPP not found in C:\xampp
  echo Please install XAMPP first or adjust the path in this script
  pause
  exit /b 1
)

:: Verify if XAMPP services are running
echo Checking if XAMPP services are running...
netstat -aon | findstr ":3306" > nul
if %errorlevel% neq 0 (
  echo MySQL is not running. Please start MySQL in XAMPP Control Panel and try again.
  pause
  exit /b 1
)

netstat -aon | findstr ":80" > nul
if %errorlevel% neq 0 (
  echo Apache is not running. Please start Apache in XAMPP Control Panel and try again.
  pause
  exit /b 1
)

echo XAMPP services are running.

:: Create database and import schema
echo Creating database and importing schema...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS elearning"
"C:\xampp\mysql\bin\mysql.exe" -u root elearning < database\schema.sql

if %errorlevel% neq 0 (
  echo Database setup failed. Please check the error message.
  pause
  exit /b 1
)

echo Database setup completed successfully.

:: Install dependencies if needed
if not exist "node_modules" (
  echo Installing dependencies...
  npm install
)

:: Initialize database structure and migrate data
echo Initializing database...
node utils\initDatabase.js

if %errorlevel% neq 0 (
  echo Database initialization failed. Please check the error message.
  pause
  exit /b 1
)

echo Database initialization completed.

:: Start the server
echo Starting the backend server...
echo You can stop the server by pressing Ctrl+C
npm start

pause 