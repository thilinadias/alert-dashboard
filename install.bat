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

:: 3. Configure APP_URL (Domain vs IP)
echo.
echo ------------------------------------------------
echo 🌐 Domain Configuration
echo ------------------------------------------------
set /p HAS_DOMAIN="👉 Do you have a custom domain for this server? (e.g., alert.company.com) [Y/N]: "
if /i "%HAS_DOMAIN%"=="Y" (
    set /p CUSTOM_DOMAIN="   Enter your domain (NO http://): "
    set APP_URL=http://!CUSTOM_DOMAIN!
    echo ✅ APP_URL set to: !APP_URL!
) else (
    echo.
    echo ------------------------------------------------
    echo 🌍 Detecting Local IP Address...
    for /f "delims=" %%a in ('powershell -Command "Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.InterfaceAlias -match 'Wi-Fi|Ethernet' -and $_.PrefixOrigin -eq 'Dhcp' } | Select-Object -ExpandProperty IPAddress -First 1"') do set IP_ADDR=%%a

    if "!IP_ADDR!"=="" set IP_ADDR=localhost

    echo Detected IP: !IP_ADDR!
    echo.
    if not "!IP_ADDR!"=="localhost" (
        echo 💡 Google OAuth requires a public Top-Level Domain and does not support raw IPs.
        echo    We can use 'nip.io' to map http://!IP_ADDR!.nip.io to your local IP.
        echo.
        set /p USE_NIP="👉 Do you want to use nip.io? (Recommended) [Y/N]: "
        if /i "!USE_NIP!"=="Y" (
            set APP_URL=http://!IP_ADDR!.nip.io
            echo ✅ APP_URL set to: http://!IP_ADDR!.nip.io
        ) else (
            set APP_URL=http://!IP_ADDR!
            echo ⚠️  APP_URL set to: http://!IP_ADDR!
        )
    ) else (
        set APP_URL=http://localhost
    )
)

:: 4. Create/Update .env
echo Configuring environment...
if not exist .env copy .env.example .env >nul

:: We use a simple echo here as Windows doesn't have sed natively
echo APP_PORT=%HTTP_PORT%>>.env
echo DB_PORT_HOST=%DB_PORT%>>.env
echo APP_URL=!APP_URL!>>.env

echo ✅ Configuration ready (Web: %HTTP_PORT%, DB: %DB_PORT%)
echo 🚀 Launching Docker containers...

:: Check for Docker availability
echo.
echo 🐳 Checking for Docker...

:: Try to find docker-compose (Legacy)
where docker-compose >nul 2>&1
if %errorlevel% equ 0 (
    set COMPOSE_CMD=docker-compose
    echo ✅ Found: docker-compose
    goto :Launch
)

:: Try to find docker compose (Modern)
docker compose version >nul 2>&1
if %errorlevel% equ 0 (
    set COMPOSE_CMD=docker compose
    echo ✅ Found: docker compose plugin
    goto :Launch
)

:: Failed to find either
echo.
echo ❌ ERROR: Docker Compose not found!
echo.
echo Please install Docker Desktop for Windows:
echo 👉 https://www.docker.com/products/docker-desktop/
echo.
pause
exit /b 1

:Launch
echo 🚀 Launching Docker containers...
%COMPOSE_CMD% up -d

echo ------------------------------------------------
echo ✨ Installation Complete!
echo Access the Setup Wizard at: !APP_URL!:%HTTP_PORT%/setup
echo ------------------------------------------------
pause
