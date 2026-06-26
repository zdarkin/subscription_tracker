# Recurring Subscription Tracking System

A PHP/MySQL web application for tracking recurring software subscriptions with automated email renewal alerts.

---

## Tech Stack

| Layer    | Technology                                 |
| -------- | ------------------------------------------ |
| Frontend | Vanilla HTML, Tailwind CSS CDN, Vanilla JS |
| Backend  | PHP 8.x (PDO, no framework)                |
| Database | MySQL (Laragon)                            |
| Email    | PHPMailer + Gmail SMTP                     |
| Server   | Apache (Laragon) with mod_rewrite          |

---

## Project Structure

```
/
├── config/             App config & .env parser
├── controllers/        MVC Controllers + Session helper
├── models/             PDO-based data models
├── views/              PHP template views
│   ├── auth/           Login & Register pages
│   ├── layout/         Header, Footer, Navbar partials
│   └── subscriptions/  CRUD views + form partial
├── public/             Web root (index.php front controller)
│   ├── css/style.css
│   └── js/app.js
├── scripts/            CLI email worker (cron/scheduler)
├── sql/schema.sql      Database schema
├── vendor/             PHPMailer (Composer)
├── .env                Environment config (NOT committed)
└── composer.json
```

---

## Setup Instructions

### 1. Database

1. Open **phpMyAdmin** (via Laragon → Menu → phpMyAdmin)
2. Import `sql/schema.sql`
3. A database `subscription_tracker` will be created with all tables and a default admin account

### 2. Configure Environment

Edit `.env` in the project root:

```env
DB_HOST=127.0.0.1
DB_NAME=subscription_tracker
DB_USER=root
DB_PASS=

MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_16_char_app_password
```

**Gmail App Password setup:**

1. Go to [myaccount.google.com/security](https://myaccount.google.com/security)
2. Enable 2-Step Verification
3. Search for "App passwords" and generate one for "Mail"
4. Use the 16-character code as `MAIL_PASSWORD`

### 3. Install Dependencies

From the project root, run:

```bash
composer install
```

### 4. Configure Laragon Virtual Host

Set the document root of your virtual host to `<project_root>/public/`.

Or if using default Laragon setup, place the project in:
`C:\laragon\www\subscription-tracker\`

Then access at: `http://localhost/subscription-tracker/public/`

Update `APP_URL` in `.env` and the `RewriteBase` in `public/.htaccess` accordingly.

### 5. Enable mod_rewrite

In Laragon: Menu → Apache → httpd.conf → ensure `mod_rewrite` is enabled and `AllowOverride All` is set for the www directory.

---

## Email Worker Setup (Windows Task Scheduler)

1. Open **Task Scheduler** → Create Basic Task
2. Name: `SubsTrack Email Alerts`
3. Trigger: Daily at **7:00 AM**
4. Action: **Start a Program**
   - Program: `C:\laragon\bin\php\php8.x\php.exe`
   - Arguments: `"C:\laragon\www\subscription-tracker\scripts\email_worker.php"`
5. Save

**Manual test run:**

php scripts/email_worker.php

---

## Security Features

- ✅ Passwords hashed with `bcrypt` (cost 12) via `password_hash()`
- ✅ PDO prepared statements — SQL injection prevention
- ✅ `htmlspecialchars()` output escaping — XSS prevention
- ✅ Session ID regenerated on login — session fixation prevention
- ✅ 15-minute session inactivity timeout
- ✅ `HttpOnly + SameSite=Lax` session cookies
- ✅ Admin-only delete operations (RBAC)
- ✅ Credentials in `.env` (git-ignored)
- ✅ Email log table prevents duplicate alerts

---

## User Roles

| Role  | Capabilities                                        |
| ----- | --------------------------------------------------- |
| user  | Register, login, create/read/update/delete own subs |
| admin | All of the above + user management tools            |
