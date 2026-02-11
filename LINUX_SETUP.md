# 🐧 Linux Manual Installation Guide

This guide explains how to install the **Alert Dashboard** on a Linux server (Ubuntu/Debian recommended) using **Nginx**, **PHP-FPM**, and **MySQL**.

> **Note**: For the easiest setup, use the [Docker Installation](README.md#docker-recommended) method (`./install.sh`). This manual guide is for bare-metal deployments where Docker is not an option.

---

## ✅ Prerequisites

-   **OS**: Ubuntu 20.04/22.04 LTS or Debian 11/12
-   **Web Server**: Nginx
-   **PHP**: 8.1 or higher (with FPM)
-   **Database**: MySQL 8.0+ or MariaDB 10.6+
-   **Composer**: Dependency Manager

---

## 📦 Step 1: Install Dependencies

Run the following commands to install Nginx, PHP, MySQL, and required extensions:

```bash
# Update repositories
sudo apt update

# Install Nginx, MySQL, Git, Unzip
sudo apt install -y nginx mysql-server git unzip curl

# Add PHP Repository (if needed for latest version)
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2 and Extensions
sudo apt install -y php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml \
    php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl

# Install Composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
```

---

## 🗄️ Step 2: Configure Database

Secure your MySQL installation and create a database for the dashboard.

```bash
# Secure installation (optional but recommended)
sudo mysql_secure_installation

# Log in to MySQL
sudo mysql

# --- Inside MySQL Shell ---
CREATE DATABASE alert_dashboard;
CREATE USER 'alert_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON alert_dashboard.* TO 'alert_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 📂 Step 3: Deploy Application

Clone the repository and install dependencies.

```bash
# Navigate to web root
cd /var/www/html

# Clone the repository (replace with your repo URL if private)
# Assuming you are uploading files or cloning:
sudo git clone https://github.com/your-repo/alert-dashboard.git
cd alert-dashboard

# Set Permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Configure Environment
cp .env.example .env
nano .env
# ---------------------------------------------------------
# UPDATE THESE VALUES:
# DB_DATABASE=alert_dashboard
# DB_USERNAME=alert_user
# DB_PASSWORD=secure_password
# APP_URL=http://your-domain.com
# ---------------------------------------------------------

# Install PHP Dependencies
composer install --no-dev --optimize-autoloader

# Generate Application Key
php artisan key:generate

# Build Frontend Assets
# (You need Node.js/NPM for this. If not installed:)
# curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash - && sudo apt install -y nodejs
npm install
npm run build

# Run Database Migrations
php artisan migrate --seed --force

# Create Storage Link
php artisan storage:link
```

---

## 🌐 Step 4: Configure Nginx

Create a new Nginx server block.

```bash
sudo nano /etc/nginx/sites-available/alert-dashboard
```

Paste the following configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com; # Or your server IP
    root /var/www/html/alert-dashboard/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site and restart Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/alert-dashboard /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## 🔄 Step 5: Configure Background Workers

Create a Supervisor configuration to keep the queue worker running.

```bash
sudo apt install -y supervisor
sudo nano /etc/supervisor/conf.d/alert-dashboard-worker.conf
```

Add this content:

```ini
[program:alert-dashboard-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/alert-dashboard/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/alert-dashboard/storage/logs/worker.log
```

Start Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start alert-dashboard-worker:*
```

Add the Scheduler Cron Job:

```bash
sudo crontab -u www-data -e
```
Add this line:
```
* * * * * php /var/www/html/alert-dashboard/artisan schedule:run >> /dev/null 2>&1
```

---

## 🎉 Done!

Visit `http://your-domain.com` (or your Server IP) to see the dashboard.
