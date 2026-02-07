#!/bin/bash

# Alert Dashboard - Interactive Installer for Linux
# Detects port conflicts and proposes alternatives

echo "------------------------------------------------"
echo "🛡️ Alert Dashboard - Docker Installation"
echo "------------------------------------------------"

# Function to check if a port is in use
check_port() {
    (echo >/dev/tcp/localhost/$1) &>/dev/null
    return $?
}

# Function to find next available port
find_port() {
    local port=$1
    while check_port $port; do
        port=$((port + 1))
    done
    echo $port
}

# 1. Check HTTP Port (Default 80)
HTTP_PORT=80
if check_port 80; then
    echo "⚠️ Port 80 is already in use."
    HTTP_PORT=$(find_port 8080)
    read -p "Use port $HTTP_PORT instead? [Y/n]: " choice
    choice=${choice:-Y}
    if [[ ! $choice =~ ^[Yy]$ ]]; then
        read -p "Enter custom port: " HTTP_PORT
    fi
fi

# 2. Check MySQL Port (Default 3306)
DB_PORT=3306
if check_port 3306; then
    echo "⚠️ Port 3306 is already in use."
    DB_PORT=$(find_port 3307)
    read -p "Use port $DB_PORT instead? [Y/n]: " choice
    choice=${choice:-Y}
    if [[ ! $choice =~ ^[Yy]$ ]]; then
        read -p "Enter custom port: " DB_PORT
    fi
fi

# 3. Create/Update .env
echo "Configuring environment..."
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Update ports in .env
sed -i "s/^APP_PORT=.*/APP_PORT=$HTTP_PORT/" .env 2>/dev/null || echo "APP_PORT=$HTTP_PORT" >> .env
sed -i "s/^DB_PORT_HOST=.*/DB_PORT_HOST=$DB_PORT/" .env 2>/dev/null || echo "DB_PORT_HOST=$DB_PORT" >> .env

# Fix permissions and line endings for the entrypoint script
chmod +x docker-entrypoint.sh
# Fix potential Windows CRLF issues
sed -i 's/\r$//' docker-entrypoint.sh

echo "✅ Configuration ready (Web: $HTTP_PORT, DB: $DB_PORT)"
echo "🚀 Launching Docker containers..."

# Detect which compose command to use
if docker compose version >/dev/null 2>&1; then
    DOCKER_CMD="docker compose"
else
    DOCKER_CMD="docker-compose"
fi

echo "Using: $DOCKER_CMD"

# Cleanup old attempts that might have corrupted states
$DOCKER_CMD down --remove-orphans 2>/dev/null

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
    echo "Run: '$DOCKER_CMD logs alert-dashboard-webserver' to see why."
    exit 1
fi

echo "------------------------------------------------"
echo "✨ Installation Complete!"
echo "Access the Setup Wizard at: http://$IP_ADDR:$HTTP_PORT/setup"
echo ""
echo "💡 TIP: If the page doesn't load, check your Linux firewall:"
echo "   sudo ufw allow $HTTP_PORT/tcp"
echo "------------------------------------------------"
