# 🛡️ Help Desk Alert Dashboard

A powerful, high-speed, and role-based dashboard for managing help desk alerts, tracking SLAs, and visualizing team performance. 

---

## 🚀 Key Features

- **Double-Speed Monitoring**: Default 10-second polling for real-time alert awareness.
- **Dockerized Ready**: One-command installation for Windows and Linux.
- **Interactive GUI Setup**: Browser-based installer for easy configuration.
- **SSL Management**: Generate or upload certificates directly from the dashboard.
- **Advanced Classification**: Rules-based engine for Severity and Client mapping.
- **SLA Engine**: Visual countdowns and breach alerts to meet service standards.

---

## 🐳 Docker Installation (Recommended)

This is the easiest way to launch the system on both **Windows** (using Docker Desktop) and **Linux**.

1.  **Clone the project**
    ```bash
    git clone https://github.com/yourusername/alert-dashboard.git
    cd alert-dashboard
    ```

2.  **Start with Docker Compose**
    ```bash
    docker-compose up -d
    ```

3.  **Launch the Setup Wizard**
    Open your browser and navigate to: `http://localhost/setup`

---

## 🛠 GUI Setup Wizard

Once you launch the `/setup` URL, the interactive wizard will guide you through:
1.  **Environment Check**: Ensuring your server is ready.
2.  **Database Connection**: Linking your MySQL instance.
3.  **Migration**: Automatically building the database schema.
4.  **Admin Creation**: Setting up your first administrator account.
5.  **Email Configuration**: Connecting your alert source.
6.  **SSL Security**: Securing your dashboard with certificates.

---

## ✉️ Email Configuration (Step-by-Step)

The system fetches alerts via **IMAP** (supports OAuth2). Here is how to set up the most common providers:

### 🔹 Google (Gmail/Workspace)
1.  **Google Cloud Console**:
    - Create a project at [Google Cloud Console](https://console.cloud.google.com/).
    - Enable the **Gmail API**.
    - Go to **OAuth Consent Screen** and set it to "External" (or Internal for Workspace).
    - Go to **Credentials** -> **Create Credentials** -> **OAuth Client ID**.
    - Set the **Authorized redirect URIs** to `http://your-domain.com/oauth/callback`.
2.  **App Setup**:
    - Copy your **Client ID** and **Client Secret** into the Dashboard Settings.
    - Click **"Link Google Account"** in the Dashboard to authorize.

### 🔹 Microsoft (Outlook/Office 365)
1.  **Azure Portal**:
    - Register an App in **Azure Active Directory** (Entra ID).
    - Add **API Permissions**: `IMAP.AccessAsUser.All`.
    - Create a **Client Secret**.
    - Set the Redirect URI to `http://your-domain.com/oauth/callback`.
2.  **App Setup**:
    - Input the Tentant ID, Client ID, and Secret into the Dashboard.

---

## 🔒 Securing with SSL

You can secure your dashboard using the GUI:
1.  Navigate to **Setup -> SSL** (or Settings -> General).
2.  **Self-Signed**: Click "Generate" to create an instant 365-day certificate. 
3.  **Custom**: Upload your own `.crt` and `.key` to the `docker/certs` folder.

---

## 🖥 Manual Installation (XAMPP/Local)

1.  `composer install`
2.  `npm install && npm run build`
3.  `cp .env.example .env && php artisan key:generate`
4.  `php artisan migrate --seed`
5.  `php artisan serve`

---

## ⚖️ License
This project is licensed under the [MIT License](LICENSE).
