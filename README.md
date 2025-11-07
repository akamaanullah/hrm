# HRM Portal

Comprehensive Human Resource Management (HRM) web application built with PHP, MySQL, and vanilla JS. The system streamlines daily HR operations for administrators and employees with modern UI, real-time updates, and PWA capabilities.

## Table of Contents
- [Key Features](#key-features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Build & Development Scripts](#build--development-scripts)
- [Progressive Web App](#progressive-web-app)
- [Security Notes](#security-notes)
- [Contact](#contact)

## Key Features
- **Role-Based Dashboards**: Dedicated admin and employee portals with tailored analytics and navigation.
- **Employee Lifecycle Management**: Onboarding workflows, profile management, document storage, and shift assignments.
- **Attendance & Break Tracking**: Daily attendance monitoring, auto status calculations, and detailed break logs.
- **Leave Management**: Submit, approve, and track leave requests with document uploads and audit history.
- **Payroll Automation**: Allowances, deductions, and payslip generation with bank details and export options.
- **Announcements & Notifications**: Rich text announcements, unread tracking, sound alerts, and badge updates.
- **Reporting & Exports**: DataTables with advanced filters, Excel/PDF/print exports, and department-wise insights.
- **PWA Ready**: Offline fallback, install prompts, caching strategies, and push notification hooks.

Detailed module-by-module documentation is available in `PROJECT_DOCUMENTATION.md`.

## Tech Stack
- **Backend**: PHP 8+, MySQL 5.7/8.0
- **Frontend**: HTML5, CSS3, Bootstrap, vanilla JavaScript, Chart.js
- **Email**: PHPMailer 6.x (installed via Composer)
- **Tooling**: Composer, Service Worker, Manifest-based PWA assets

## Project Structure
```
admin/                Admin-facing pages, APIs, and UI components
assets/               Global styles, scripts, images, and sounds
database/             Schema and seed SQL (`hrm_db.sql`)
user/                 Employee-facing pages, APIs, and UI components
vendor/               Composer dependencies (PHPMailer)
config.php            Database credentials (update for your environment)
forgetpassword.php    Password reset trigger page (email flow entry point)
service-worker.js     PWA service worker implementation
manifest.json         PWA manifest definition
```

## Getting Started
1. **Clone the repository**
   ```bash
   git clone <repo-url>
   cd hrm
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Configure web server**
   - Place the project inside your web root (e.g., `htdocs` for XAMPP).
   - Ensure virtual host or local domain points to the project folder.

4. **Set file permissions** (Linux/macOS)
   ```bash
   chmod -R 775 storage uploads
   ```
   > Adjust the path if you change document upload directories.

5. **Import the database** (see [Database Setup](#database-setup)).

6. **Visit the application**
   - Admin: `http://your-domain/admin/`
   - User: `http://your-domain/user/`

## Environment Configuration
- Copy `config.php` and update database constants (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`). Keep this file limited to connection credentials.
- Move SMTP credentials (currently inside `forgetpassword.php`) to a secure config source (environment variables or a non-tracked PHP config) and include them where needed.
- Update `$baseUrl` variables or hard-coded paths if serving from a subdirectory.

## Database Setup
1. Create a database (e.g., `hrm_db`).
2. Import schema and seed data:
   ```bash
   mysql -u <user> -p < db_name < database/hrm_db.sql
   ```
3. Adjust any sample admin credentials directly in the database or via the onboarding flow.

## Build & Development Scripts
- `composer install` — installs PHP dependencies (PHPMailer).
- `composer update` — updates dependencies (ensure compatibility with your PHP version).
- For front-end assets, standard Bootstrap/JS files are curated manually; no bundler is required.

## Progressive Web App
- `service-worker.js` implements caching strategies for static assets and API requests.
- `manifest.json` defines icons, theme colors, and install behavior.
- `assets/js/pwa-register.js` handles service worker registration. Ensure HTTPS is enabled in production for full PWA functionality.

## Security Notes
- Store API keys, SMTP passwords, and secrets outside the repository.
- Use HTTPS in production to secure login sessions and PWA features.
- Review input validation in API endpoints before deploying to production.
- Update PHPMailer via Composer to patch security fixes promptly.

## Contact
- Website: [amaanullah.com](https://amaanullah.com)
- Email: [info@amaanullah.com](mailto:info@amaanullah.com)

Feel free to open issues or pull requests for enhancements, bug fixes, or new feature discussions.


