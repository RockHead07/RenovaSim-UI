@echo off
REM Start Python Editor API Server
REM ================================

echo.
echo ╔════════════════════════════════════════════════╗
echo ║  Starting Python Room Editor API Server...    ║
echo ╚════════════════════════════════════════════════╝
echo.

cd /d "%~dp0"

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Python tidak ditemukan! Pastikan Python sudah ter-install.
    echo 📥 Download dari: https://www.python.org/downloads/
    pause
    exit /b 1
)

REM Install dependencies if needed
echo 📦 Checking dependencies...
pip install -q flask flask-cors flask-restful 2>nul

if errorlevel 1 (
    echo ⚠️ Failed to install dependencies automatically
    echo Please run manually:
    echo   pip install flask flask-cors flask-restful
    pause
)

REM Start the server
echo.
echo 🚀 Starting server...
echo 📍 URL: http://localhost:5000
echo 💡 Press Ctrl+C to stop
echo.

python app_server.py

pause
