# 🏛️ IIS Installation Guide for Alert Dashboard

This guide explains how to install the **Alert Dashboard** on Windows Server 2016+ using native **IIS (Internet Information Services)**. This method does **NOT** require XAMPP or Docker.

---

## ✅ Prerequisites

1.  **Windows Server 2016+** (with IIS enabled)
2.  **PHP 8.1 or higher** (Non-Thread Safe for IIS)
3.  **MySQL 8.0**
4.  **Composer** (Dependency Manager)
5.  **URL Rewrite Module 2.1** for IIS

---

## 🚀 Step 1: Install Dependencies

### 1. Install PHP Manager for IIS (Highly Recommended)
-   Download and install [PHP Manager for IIS](https://github.com/phpmanager/phpmanager/releases).
-   This makes registering PHP versions extremely easy.

### 2. Install PHP 8.2 (NTS)
-   Download PHP 8.2 (Non-Thread Safe x64) from [windows.php.net](https://windows.php.net/download/).
-   Extract it to `C:\php`.
-   Rename `php.ini-production` to `php.ini`.
-   Edit `php.ini` and enable these extensions (remove the `;`):
    ```ini
    extension_dir = "ext"
    extension=curl
    extension=fileinfo
    extension=gd
    extension=mbstring
    extension=mysqli
    extension=openssl
    extension=pdo_mysql
    extension=zip
    ```
-   **Register PHP in IIS**: Open IIS Manager -> PHP Manager -> "Register new PHP version" -> Select `C:\php\php-cgi.exe`.

### 3. Install URL Rewrite Module
-   Download and install [URL Rewrite Module 2.1](https://www.iis.net/downloads/microsoft/url-rewrite).
-   **Restart IIS** after installation (`iisreset` in CMD).

### 4. Install MySQL
-   Download and install [MySQL Community Server](https://dev.mysql.com/downloads/mysql/).
-   During setup, set a secure Root Password.
-   Create a database:
    ```sql
    CREATE DATABASE alert_dashboard;
    ```

---

## 📂 Step 2: Deploy the Application

1.  **Copy Files**:
    -   Place the project folder in `C:\inetpub\wwwroot\alert-dashboard` (or anywhere you prefer).

2.  **Install Application Dependencies**:
    -   Open Command Prompt/PowerShell in that folder.
    -   Run:
        ```powershell
        copy .env.example .env
        composer install --no-dev --optimize-autoloader
        npm install && npm run build
        php artisan key:generate
        php artisan migrate --seed --force
        php artisan storage:link
        ```

3.  **Permissions**:
    -   Right-click `storage` and `bootstrap/cache` folders -> Properties -> Security.
    -   Click "Edit" -> "Add".
    -   Enter `IUSR` and `IIS_IUSRS`.
    -   Grant **Full Control** (or at least Modify/Write).

---

## 🌐 Step 3: Configure IIS Site

1.  Open **IIS Manager**.
2.  Right-click "Sites" -> **Add Website**.
    -   **Site name**: `AlertDashboard`
    -   **Physical path**: `C:\inetpub\wwwroot\alert-dashboard\public` (Crucial: Point to `public` folder!)
    -   **Port**: `80` (or `8080` if 80 is used).
3.  Click **OK**.

### Verify `web.config`
Ensure the `web.config` file exists in the `public` folder. This handles the clean URLs.

---

## 📅 Step 4: Scheduled Tasks (Cron)

Laravel requires a background worker for alerts. On Windows, use "Task Scheduler".

1.  Open **Task Scheduler**.
2.  Create a "Basic Task" named `AlertDashboardWorker`.
3.  Trigger: **Daily** (Repeat every 1 minute).
    -   *Wait, basic task wizard doesn't support 'every minute'.*
4.  **Better Way**: Create a `.bat` file at `C:\inetpub\wwwroot\alert-dashboard\run-worker.bat`:
    ```bat
    cd C:\inetpub\wwwroot\alert-dashboard
    C:\php\php.exe artisan schedule:run 1>> NUL 2>&1
    ```
5.  Use Task Scheduler to run this `.bat` file every minute.

---

## 🎉 Done!
Access your site at `http://localhost` (or your server IP).

---

## 🐛 Troubleshooting

-   **HTTP 500 Error**: Check `storage/logs/laravel.log`. Usually permission issues on the `storage` folder.
-   **404 on Login**: URL Rewrite module is missing or `web.config` is invalid.
