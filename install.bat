@echo off
setlocal enabledelayedexpansion

title Alert Dashboard - Interactive Installer

echo ------------------------------------------------
echo 🛡️ Alert Dashboard - Docker Installation
echo ------------------------------------------------

:: 1. Check HTTP Port (Default 80)
set HTTP_PORT=80
netstat -ano | findstr :80 >nul
if %errorlevel% equ 0 (
    echo ⚠️ Port 80 is already in use.
    set HTTP_PORT=8080
    echo Proposing port 8080 instead.
    set /p HTTP_PORT="Enter port to use [default 8080]: "
)

:: 2. Check MySQL Port (Default 3306)
set DB_PORT=3306
netstat -ano | findstr :3306 >nul
if %errorlevel% equ 0 (
    echo ⚠️ Port 3306 is already in use.
    set DB_PORT=3307
    echo Proposing port 3307 instead.
    set /p DB_PORT="Enter port to use [default 3307]: "
)

:: 3. Create/Update .env
echo Configuring environment...
if not exist .env copy .env.example .env >nul

:: We use a simple echo here as Windows doesn't have sed natively
echo APP_PORT=%HTTP_PORT%>>.env
echo DB_PORT_HOST=%DB_PORT%>>.env

echo ✅ Configuration ready (Web: %HTTP_PORT%, DB: %DB_PORT%)
echo 🚀 Launching Docker containers...

docker-compose up -d

echo ------------------------------------------------
echo ✨ Installation Complete!
echo Access the Setup Wizard at: http://localhost:%HTTP_PORT%/setup
echo ------------------------------------------------
pause
