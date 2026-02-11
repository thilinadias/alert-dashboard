# 🔧 Troubleshooting Guide

This page documents common issues encountered during installation and their solutions.

## 🐧 Linux / Docker Common Issues

### 1. "Port 80 is already in use"
**Symptom**: The installer says "Port 80 is already in use by another service."
**Cause**: Another web server (like Apache or a host Nginx) is running.
**Solution**:
-   The installer will automatically ask to use an alternative port (e.g., `8080`).
-   To free up port 80 manually:
    ```bash
    sudo systemctl stop apache2
    sudo systemctl stop nginx
    ```

### 2. Google OAuth Error: "redirect_uri_mismatch"
**Symptom**: Google Login fails with a mismatch error.
**Cause**: You are accessing the dashboard via an IP address (e.g., `http://192.168.1.50`). Google **requires** a valid Top-Level Domain (TLD).
**Solution**:
-   **Use nip.io**: The installer offers this automatically. It maps `http://192.168.1.50.nip.io` -> `192.168.1.50`.
-   **Update Google Console**: Ensure the "Authorized redirect URIs" in Google Cloud Console matches exactly (e.g., `http://192.168.1.50.nip.io/auth/google/callback`).

### 3. "Vite manifest not found" (500 Error)
**Symptom**: The login page crashes with a "Vite manifest not found" error.
**Cause**: The frontend assets (CSS/JS) were not built.
**Solution**:
Running the installer usually fixes this. If manual:
```bash
npm install
npm run build
```

### 4. Database Connection Refused
**Symptom**: `SQLSTATE[HY000] [2002] Connection refused`
**Cause**:
-   **Docker**: The `DB_HOST` in `.env` is set to `localhost` instead of `db`.
-   **Manual**: The MySQL service is not running.
**Solution**:
-   **Docker**: Ensure `.env` has `DB_HOST=db`.
-   **Manual**: `sudo systemctl start mysql`.

### 5. Docker MySQL Crashing ("Root User" Issue)
**Symptom**: The `db` container keeps restarting.
**Cause**: You set `DB_USERNAME=root` in `.env`. The official MySQL Docker image is strict about root access.
**Solution**:
-   Use a non-root user (e.g., `alert_user`).
-   The installer automatically fixes this if detected.

### 6. Permission Denied (storage/logs)
**Symptom**: `The stream or file ".../laravel.log" could not be opened: failed to open stream: Permission denied`
**Cause**: The web server user (`www-data`) cannot write to the storage directory.
**Solution**:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 🐛 Getting Help

If you encounter an issue not listed here:
1.  Check the logs:
    ```bash
    docker compose logs app
    docker compose logs db
    ```
2.  Open an [Issue on GitHub](https://github.com/thilinadias/alert-dashboard/issues).
