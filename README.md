# SubsTrack - Recurring Subscription Tracking System

A complete, production-ready PHP/MySQL web application for tracking recurring software subscriptions, managing spending, and receiving automated email renewal alerts.

---

## 🚀 Features

- **Dashboard & Analytics**: Visualize monthly/annual spend and category breakdowns with interactive charts.
- **Automated Email Alerts**: Daily background worker to notify users of upcoming renewals (e.g., 3 days and 1 day before).
- **Subscription Management**: Full CRUD capabilities for tracking services, costs, billing cycles, and status (Active, Paused, Cancelled).
- **Role-Based Access Control (RBAC)**: Distinct Admin and User roles with an administrative dashboard for user management.
- **Modern UI**: Fully responsive, dark-mode-first glassmorphism design using Tailwind CSS.
- **Robust Security**: Protection against XSS (strict output escaping), SQL Injection (PDO prepared statements), and robust session security (HttpOnly, SameSite, and strict timeouts).

---

## 🛠 Tech Stack

| Layer | Technology |
| --- | --- |
| **Frontend** | HTML5, Tailwind CSS, Vanilla JavaScript, Chart.js (via Canvas) |
| **Backend** | PHP 8.1+ (Vanilla MVC-lite structure, no heavy frameworks) |
| **Database** | MySQL 8.0+ / MariaDB |
| **Email Services** | PHPMailer (supports Mailpit for local dev, Gmail/SMTP for production) |

---

## 📋 Prerequisites

To run this application, ensure your environment has:
- **PHP 8.1 or higher**
- **MySQL 8.0+** or **MariaDB 10.4+**
- **Composer** (for installing PHPMailer)
- A local server environment like **Laragon**, **XAMPP**, or **MAMP** (Laragon is highly recommended).
- **Apache** with `mod_rewrite` enabled.

---

## ⚙️ Setup & Installation

### 1. Clone the Repository
Clone the repository into your web server's document root (e.g., `C:\laragon\www\subscription_tracker`).

### 2. Database Configuration
1. Open your database manager (e.g., phpMyAdmin, HeidiSQL, or TablePlus).
2. Create a new database named `subscription_tracker` (or let the schema file do it).
3. Import the database schema file located at `sql/create_db.sql`.
4. *(Optional)* Import `sql/mock_data.sql` to populate the database with sample users and subscriptions.
   > **Note:** The default admin account (if using mock data) is `admin@example.com` / `admin123`. Regular user accounts (e.g., `alice@example.com`, `bob@example.com`) have the password `password123`.

### 3. Environment Configuration
1. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```
2. Edit `.env` to match your local environment setup:
   - Configure your `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS`.
   - By default, the application is configured to use **Mailpit** (port 1025) for local email testing. If deploying to production, comment out the Mailpit section and provide your live SMTP credentials (e.g., Gmail with an App Password).

### 4. Install Dependencies
Run Composer to install PHPMailer:
```bash
composer install
```

### 5. Web Server Configuration
Point your web server's document root to the `public/` directory of the application, or access it via your local subfolder.
- Ensure `AllowOverride All` is set in your Apache configuration so the `public/.htaccess` file is read correctly to route all traffic through `public/index.php`.

---

## 📧 Email Worker Setup (Cron Job / Task Scheduler)

To send automated renewal alerts, the `scripts/email_worker.php` file must be executed daily.

**For Linux (Cron):**
```bash
0 7 * * * /usr/bin/php /path/to/subscription_tracker/scripts/email_worker.php >> /path/to/subscription_tracker/logs/email_cron.log 2>&1
```

**For Windows (Task Scheduler):**
1. Open Task Scheduler and click **Create Basic Task**.
2. Name it `SubsTrack Email Alerts`.
3. Set the trigger to **Daily** at **7:00 AM**.
4. Set the action to **Start a Program**.
   - Program/script: `C:\laragon\bin\php\php8.1\php.exe` (Path to your PHP executable)
   - Add arguments: `"C:\laragon\www\subscription_tracker\scripts\email_worker.php"`

You can run this script manually in your terminal to test email delivery:
```bash
php scripts/email_worker.php
```

## 👨‍💻 Usage
1. Register a new user account or log in with an existing one.
2. Navigate to **Add Subscription** to track a new recurring cost.
3. The **Dashboard** will automatically update your monthly/annual spend projections and upcoming renewals.
4. Admins can navigate to the **Admin Dashboard** via the footer to manage users and monitor global system metrics.

---
*Developed with ❤️ for better financial tracking.*
