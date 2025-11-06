@echo off
echo Starting EduN Learning System...
echo.
echo Your site will be available at:
echo Frontend: http://192.168.50.76:3000
echo Backend:  http://192.168.50.76:5001
echo.
echo Copying frontend URL to clipboard...
echo http://192.168.50.76:3000 | clip
echo Frontend URL has been copied to clipboard!
echo.
echo Starting servers...
echo.

:: Start backend first
start cmd /k "cd backend && npm start"

:: Wait for backend to start
echo Waiting for backend server to start...
timeout /t 10 /nobreak > nul

:: Check if backend is running
curl -s http://192.168.50.76:5001 > nul
if %errorlevel% neq 0 (
    echo.
    echo ERROR: Backend server failed to start!
    echo Please check if:
    echo 1. XAMPP is running
    echo 2. MySQL service is started
    echo 3. Port 5001 is not in use
    echo.
    echo Press any key to exit...
    pause > nul
    exit /b 1
)

echo Backend server is running!
echo.
echo Starting frontend server...
echo.

:: Start frontend
start cmd /k "cd frontend && npm start"

echo.
echo Both servers are starting...
echo You can access the site at: http://192.168.50.76:3000
echo.
echo Press Ctrl+C in each window to stop the servers
echo. 