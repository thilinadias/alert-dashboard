# IIS Prerequisites Automator for Alert Dashboard
# Run this as Administrator

$ErrorActionPreference = "Stop"
$WorkDir = "C:\Temp\AlertDashboardSetup"
$PhpDir = "C:\php"

# Create temp directory
New-Item -ItemType Directory -Force -Path $WorkDir | Out-Null

Write-Host "Starting IIS Prerequisites Setup..." -ForegroundColor Cyan

# 1. Install Chocolatey
if (-not (Get-Command choco -ErrorAction SilentlyContinue)) {
    Write-Host "Installing Chocolatey..." -ForegroundColor Yellow
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
    iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
    
    # Refresh env vars
    $env:ChocolateyInstall = Convert-Path "$env:ProgramData\chocolatey"
    $env:Path = "$env:Path;$env:ChocolateyInstall\bin"
    
    Write-Host "----------------------------------------------------------------" -ForegroundColor Red
    Write-Host "CRITICAL: Chocolatey installed .NET Framework 4.8." -ForegroundColor Red
    Write-Host "You MUST restart your server now for it to work." -ForegroundColor Red
    Write-Host "----------------------------------------------------------------"
    Write-Host "Please restart Windows, then run this script again." -ForegroundColor Yellow
    Pause
    exit
}

# 2. Install Tools via Chocolatey
Write-Host "Installing Dependencies (PHP, Composer, URL Rewrite, VC++)..." -ForegroundColor Yellow

# Explicit commands to avoid PowerShell argument parsing issues
Write-Host "Installing PHP..." -ForegroundColor Cyan
choco install php --version=8.2.11 --package-parameters='/ThreadSafe:false /InstallDir:C:\php' -y --force

Write-Host "Installing VC++ Redist..." -ForegroundColor Cyan
choco install vcredist140 -y --force

Write-Host "Installing URL Rewrite..." -ForegroundColor Cyan
choco install urlrewrite -y --force

Write-Host "Installing Composer..." -ForegroundColor Cyan
choco install composer -y --force

Write-Host "Installing MySQL..." -ForegroundColor Cyan
choco install mysql -y --force

# Verify PHP installed
if (-not (Test-Path "$PhpDir\php.ini-production")) {
    Write-Host "ERROR: PHP was not installed to $PhpDir." -ForegroundColor Red
    Write-Host "Manual Fix: Run 'choco install php --version=8.2.11 --package-parameters=""/ThreadSafe:false /InstallDir:C:\php"" -y' in cmd" -ForegroundColor Yellow
    Pause
    exit
}

# 3. Configure PHP.ini
Write-Host "Configuring PHP.ini..." -ForegroundColor Yellow
$PhpIni = "$PhpDir\php.ini"

if (Test-Path "$PhpDir\php.ini-production") {
    Copy-Item "$PhpDir\php.ini-production" $PhpIni -Force
}

# Functions to enable extensions
function Enable-PhpExtension ($name) {
    (Get-Content $PhpIni) -replace ";extension=$name", "extension=$name" | Set-Content $PhpIni
}

Enable-PhpExtension "curl"
Enable-PhpExtension "fileinfo"
Enable-PhpExtension "gd"
Enable-PhpExtension "mbstring"
Enable-PhpExtension "mysqli"
Enable-PhpExtension "openssl"
Enable-PhpExtension "pdo_mysql"
Enable-PhpExtension "zip"

# Set extension dir
(Get-Content $PhpIni) -replace ";extension_dir = `"ext`"", "extension_dir = `"ext`"" | Set-Content $PhpIni

# 4. Configure IIS to use PHP
Write-Host "Linking PHP to IIS..." -ForegroundColor Yellow
$IISPath = "$env:windir\system32\inetsrv\appcmd.exe"

if (Test-Path $IISPath) {
    & $IISPath set config /section:system.webServer/fastCgi /+"[fullPath='C:\php\php-cgi.exe']" /commit:apphost 2>$null
    & $IISPath set config /section:system.webServer/handlers /+"[name='PHP_via_FastCGI',path='*.php',verb='*',modules='FastCgiModule',scriptProcessor='C:\php\php-cgi.exe',resourceType='Either']" /commit:apphost 2>$null
}

Write-Host "------------------------------------------------" -ForegroundColor Cyan
Write-Host "Prerequisites Installed!" -ForegroundColor Green
Write-Host "------------------------------------------------"
Write-Host "Next Step: Create your database in MySQL and follow IIS_SETUP.md." -ForegroundColor White
Pause
