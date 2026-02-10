#!/bin/bash

# Alert Dashboard - Interactive Installer for Linux
# Detects port conflicts and proposes alternatives

echo "------------------------------------------------"
echo "🛡️ Alert Dashboard - Docker Installation"
echo "------------------------------------------------"

# 0. Cleanup Existing Services
echo "🔄 Checking for existing installation..."

# Detect which compose command to use
if docker compose version >/dev/null 2>&1; then
    DOCKER_CMD="docker compose"
else
    DOCKER_CMD="docker-compose"
fi

# Stop existing containers to free up ports
if [ -f "docker-compose.yml" ]; then
    echo "🛑 Stopping running containers..."
    $DOCKER_CMD down --remove-orphans 2>/dev/null
fi

# Function to check if a port is in use
check_port() {
    (ss -tuln 2>/dev/null | grep -q ":$1 ") || (netstat -tuln 2>/dev/null | grep -q ":$1 ") || (echo >/dev/tcp/localhost/$1) 2>/dev/null
    return $?
}


# 1. Check HTTP Port (Default 80)
HTTP_PORT=80
if check_port 80; then
    echo "⚠️ Port 80 is already in use by another service."
    HTTP_PORT=8080
    while check_port $HTTP_PORT; do
        HTTP_PORT=$((HTTP_PORT + 1))
    done
    read -p "Use port $HTTP_PORT instead? [Y/n]: " choice
    choice=${choice:-Y}
    if [[ ! $choice =~ ^[Yy]$ ]]; then
        read -p "Enter custom port: " HTTP_PORT
    fi
fi

# 2. Check MySQL Port (Default 3306)
DB_PORT_HOST=3306
if check_port 3306; then
    echo "⚠️ Port 3306 is already in use."
    DB_PORT_HOST=3307
    while check_port $DB_PORT_HOST; do
        DB_PORT_HOST=$((DB_PORT_HOST + 1))
    done
    read -p "Use port $DB_PORT_HOST instead? [Y/n]: " choice
    choice=${choice:-Y}
    if [[ ! $choice =~ ^[Yy]$ ]]; then
        read -p "Enter custom port: " DB_PORT_HOST
    fi
fi

# Detect Local IP
IP_ADDR=$(ip route get 1 2>/dev/null | awk '{print $7;exit}')
if [ -z "$IP_ADDR" ]; then
    IP_ADDR=$(hostname -I | awk '{print $1}')
fi
if [ -z "$IP_ADDR" ]; then
    IP_ADDR="localhost"
fi

# 3. Configure APP_URL (Domain vs IP)
echo "------------------------------------------------"
echo "🌐 Domain Configuration"
echo "------------------------------------------------"
read -p "👉 Do you have a custom domain for this server? (e.g., alert.company.com) [y/N]: " has_domain
has_domain=${has_domain:-N}

if [[ "$has_domain" =~ ^[Yy]$ ]]; then
    read -p "   Enter your domain (NO http://): " CUSTOM_DOMAIN
    APP_URL="http://$CUSTOM_DOMAIN"
    echo "✅ APP_URL set to: $APP_URL"
else
    # Fallback to IP / Nip.io
    if [[ "$IP_ADDR" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "💡 Detected Local IP: $IP_ADDR"
        echo "   Google OAuth requires a public Top-Level Domain (TLD)."
        echo "   We can use 'nip.io' to automatically map 'http://$IP_ADDR.nip.io' to your local IP."
        echo ""
        read -p "👉 Do you want to use nip.io for Google OAuth compatibility? (Recommended) [Y/n]: " use_nip
        use_nip=${use_nip:-Y}

        if [[ "$use_nip" =~ ^[Yy]$ ]]; then
            APP_URL="http://$IP_ADDR.nip.io"
            echo "✅ APP_URL set to: $APP_URL"
        else
            APP_URL="http://$IP_ADDR"
            echo "⚠️  APP_URL set to: $APP_URL (Google OAuth may not work)"
        fi
    else
        APP_URL="http://$IP_ADDR"
    fi
fi

# 3. Create/Update .env
echo "Configuring environment..."
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Function to update or add env var
update_env() {
    local key=$1
    local value=$2
    if grep -q "^${key}=" .env; then
        sed -i "s|^${key}=.*|${key}=${value}|" .env
    else
        echo "${key}=${value}" >> .env
    fi
}

update_env "APP_PORT" "$HTTP_PORT"
update_env "DB_PORT_HOST" "$DB_PORT_HOST"
update_env "APP_URL" "$APP_URL"

# Fix permissions and line endings
chmod +x docker-entrypoint.sh
sed -i 's/\r$//' docker-entrypoint.sh 2>/dev/null
sed -i 's/\r$//' .env 2>/dev/null

# 2. Configure Database Credentials (Prevent 'root' user crash)
if [ -f .env ]; then
    # If DB_USERNAME is root, change it to alert_user to avoid Docker crash
    if grep -q "DB_USERNAME=root" .env; then
        echo "⚠️  Detected 'root' as DB_USERNAME. This causes Docker MySQL to crash."
        echo "🔧 Fixing .env: DB_USERNAME -> alert_user"
        # Use simple sed for cross-platform compatibility (Linux standard)
        sed -i 's/DB_USERNAME=root/DB_USERNAME=alert_user/' .env
    fi
else
    # Create .env if missing
    cp .env.example .env
    sed -i 's/DB_USERNAME=root/DB_USERNAME=alert_user/' .env
fi

echo "✅ Configuration ready (Web: $HTTP_PORT, DB: $DB_PORT_HOST)"
echo "🚀 Launching Docker containers..."

# Launch
$DOCKER_CMD up -d --build

# Wait a few seconds for startup
echo "⏳ Waiting for services to initialize..."
sleep 5

# Detect Local IP
IP_ADDR=$(hostname -I | awk '{print $1}')
if [ -z "$IP_ADDR" ]; then
    IP_ADDR="localhost"
fi

# Verify Container Status
echo "------------------------------------------------"
echo "🔍 Checking Service Status:"
$DOCKER_CMD ps

# Check if webserver is down
if ! $DOCKER_CMD ps | grep -q "alert-dashboard-webserver.*Up"; then
    echo "❌ ERROR: Webserver container failed to stay Up."
    echo "Logs:"
    $DOCKER_CMD logs webserver
    exit 1
fi

# Deep Diagnostics for App Container
echo "🔍 Checking App Container Internals..."
$DOCKER_CMD exec app php-fpm -t || echo "⚠️ PHP-FPM Configuration Test Failed"
$DOCKER_CMD exec app ps aux | grep php || echo "⚠️ PID Check Failed"
$DOCKER_CMD exec app netstat -tulpn || echo "⚠️ Network Check Failed (netstat missing?)"
$DOCKER_CMD exec app cat /usr/local/etc/php-fpm.d/www.conf | grep listen || echo "⚠️ Config Check Failed"

# Check if app is listening on 9000 (Wait loop for DB initialization)
echo "⏳ Waiting for App to initialize (this checks if PHP-FPM is ready)..."
MAX_RETRIES=30
COUNT=0
resolved=false

while [ $COUNT -lt $MAX_RETRIES ]; do
    if $DOCKER_CMD exec app nc -z localhost 9000; then
        resolved=true
        break
    fi
    echo -n "."
    sleep 2
    COUNT=$((COUNT+1))
done
echo ""

if [ "$resolved" = false ]; then
    echo "❌ ERROR: PHP-FPM failed to start within 60 seconds."
    echo "--- App Logs ---"
    $DOCKER_CMD logs app --tail 20
    echo "--- DB Logs ---"
    $DOCKER_CMD logs db --tail 20
    exit 1
fi

echo "✅ PHP-FPM is up and running!"

# 4. Final Configuration Checks
echo "🔧 Running final configuration checks..."

# Fix Missing App Key (Causes 500 Error)
if $DOCKER_CMD exec app grep -q "^APP_KEY=$" .env 2>/dev/null || \
   ! $DOCKER_CMD exec app grep -q "^APP_KEY=" .env 2>/dev/null; then
    echo "🔑 APP_KEY is missing. Generating..."
    $DOCKER_CMD exec app php artisan key:generate --force
    $DOCKER_CMD exec app php artisan config:clear
fi

# Ensure storage link exists
$DOCKER_CMD exec app php artisan storage:link 2>/dev/null || true

# Check if app container is down (PHP-FPM)
if ! $DOCKER_CMD ps | grep -q "alert-dashboard-app.*Up"; then
    echo "❌ ERROR: App container failed to start."
    echo "Logs:"
    $DOCKER_CMD logs app
    exit 1
fi

echo "------------------------------------------------"
echo "✨ Installation Complete!"
echo "------------------------------------------------"
echo "🌍 Access the Setup Wizard at: http://$IP_ADDR:$HTTP_PORT/setup"
echo ""
echo "💡 TIP: If the page doesn't load, check your Linux firewall:"
echo "   sudo ufw allow $HTTP_PORT/tcp"
echo "------------------------------------------------"
