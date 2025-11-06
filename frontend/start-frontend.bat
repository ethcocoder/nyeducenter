@echo off
echo Checking if backend server is running...
curl -s http://192.168.50.76:5001 > nul
if %errorlevel% neq 0 (
    echo.
    echo ERROR: Backend server is not running!
    echo Please start the backend server first using:
    echo 1. Double-click start-servers.bat in the main folder, or
    echo 2. Run backend/start-backend.bat
    echo.
    echo Press any key to exit...
    pause > nul
    exit /b 1
)

echo Backend server is running!
echo.
echo Starting Frontend Server...
echo.
echo Your site will be available at:
echo http://192.168.50.76:3000
echo.
echo Copying URL to clipboard...
echo http://192.168.50.76:3000 | clip
echo URL has been copied to clipboard!
echo.
echo Press Ctrl+C to stop the server
echo.
npm start 