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

echo "✅ Configuration ready (Web: $HTTP_PORT, DB: $DB_PORT)"
echo "🚀 Launching Docker containers..."

docker-compose up -d

echo "------------------------------------------------"
echo "✨ Installation Complete!"
echo "Access the Setup Wizard at: http://localhost:$HTTP_PORT/setup"
echo "------------------------------------------------"
