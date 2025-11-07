# HRM Portal

The HRM Portal is a full-featured Human Resource Management platform that streamlines end-to-end HR operations for growing organizations. It combines role-based dashboards, automated workflows, detailed analytics, and progressive web app support to deliver a modern experience for administrators and employees alike.

## Table of Contents
- [Overview](#overview)
- [System Architecture](#system-architecture)
- [Feature Modules](#feature-modules)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Environment Setup](#environment-setup)
- [Database Initialization](#database-initialization)
- [Progressive Web App](#progressive-web-app)
- [Security & Compliance](#security--compliance)
- [Future Enhancements](#future-enhancements)
- [Contact](#contact)

## Overview
- Comprehensive HR lifecycle coverage: onboarding, attendance, leave, payroll, announcements, and analytics.
- Dedicated portals for admins and employees with tailored navigation and permissions.
- Real-time updates powered by WebSockets (preferred) or optimized polling for notifications and dashboards.
- Modular architecture that allows each feature area to evolve independently.
- Built with responsive, mobile-first UI principles and Bootstrap-based styling.

## System Architecture
- **Roles**: Admin (full access) and Employee (self-service access).
- **Authentication**: Email/password, secure hashing, and session-based role checks.
- **Access Control**: RBAC enforced on every page through `admin/session_check.php` and `user/session_check.php`.
- **Session Protection**: Activity tracking, forced logout after 9 hours of inactivity, and HttpOnly cookies.
- **Email Services**: Password recovery using PHPMailer with SMTP credentials (configure securely outside the repo).

## Feature Modules

### 1. Authentication & Security
- Login with email, password hashing, account status validation, remember-me support, and password visibility toggles (`login.php`).
- Password recovery workflow (`forgetpassword.php`, `recoverpassword.php`, `resetpassword.php`) with secure tokens and expiry checks.
- Session validators for admin and user dashboards with role redirection and unauthorized access prevention.

### 2. Admin Dashboard (`admin/index.php`)
- KPI cards (total employees, present today, on leave, departments) with trend indicators.
- Interactive charts for attendance trends, job types, department distribution, salary overview, gender breakdown, joining vs exit, and leave status.
- Quick actions and modal-driven detail views with department-level filters.

### 3. Employee Management (`admin/all-employee.php`)
- DataTable-driven employee directory with filtering by ID, name, department, and status.
- Multi-step forms for creating and updating employee profiles including personal, job, bank, education, experience, and document sections.
- Shift management, department management, soft delete/restore, and export (Excel/PDF/Print) capabilities.
- Document uploads with validation and secure storage paths.

### 4. Attendance Management
- **Admin (`admin/attendance.php`)**: Daily records, advanced filters, auto attendance, calendar-based monthly summaries, detailed day modals, and exports.
- **Employee (`user/userattendance.php`)**: Personal calendar, status cards, break histories, modal detail views, and color-coded indicators.
- Automatic status calculation considering shifts, grace time, half-day detection, and working hour computations.

### 5. Leave Management
- **Admin (`admin/leave-requests.php`)**: Card-based review with employee details, documents, approve/reject actions, admin comments, and notification badges.
- **Employee (`user/leave-history.php`)**: Application form, status tracking (pending/approved/rejected), edit for pending leaves, document uploads, date filters, and exports.
- Automatic day calculations and leave balance tracking.

### 6. Payroll
- **Admin (`admin/payroll.php`)**: Earnings/deductions tables, bulk payslip generation, auto calculations for late/leave deductions, manual adjustments, bank details, and exports.
- **Employee (`user/user-payroll-salary.php`)**: Personal payroll history, printable payslips, salary trend charts, and month filters.
- Calculation covers allowances (fuel, house rent, utility, mobile) and deductions (PF, tax, loan, late, half-day).

### 7. Announcements & Notifications
- **Announcements**: Rich text editor for admins (`admin/admin-annoucement.php`), timeline view for both roles, date validity, and read/unread tracking with sound alerts.
- **Notifications**: Attendance-related notifications (`admin/notifications.php`), mark-all-read, badge counts, and quick access to detailed records.

### 8. User Dashboard (`user/userdashboard.php`)
- Quick stats for today’s status, monthly attendance percentage, leave balance, and department info.
- Shortcut cards to attendance, leave, payroll, and announcements.
- Charts for attendance overview, salary trend, leave usage, and work hours analysis with real-time data refresh.

### 9. Break Management (`user/break.php`)
- Start/end break actions, duration tracking, historical logs, and integration into attendance summaries.

### 10. Joining Form (`admin/joining-form.php`)
- Card view for pending employees, full onboarding modal covering personal to administrative details, document handling, activation, and permanent deletion options.

### 11. Profile Management
- Admin and employee profile panels (`admin/top-bar.php`, `user/topbar.php`) with editable personal info, contact details, profile photos, and document preview.

### 12. Department & Shift Management
- Embedded within employee management to create/update/delete departments, assign managers, and configure shifts with start/end times, grace periods, and half-day hours.

### 13. Data Export & Reporting
- Consistent export tools across modules (Employee, Attendance, Payroll, Leave) supporting Excel, PDF, and print with filtered datasets and formatted reports.

### 14. Real-time Features
- Browser notifications, badge counters, sound alerts, and visibility-aware refresh cycles to surface new leave requests, announcements, and attendance updates.

### 15. Document Management
- Validated uploads for resumes, IDs, and leave documents; preview, download, and secure storage strategies to protect sensitive files.

## Technology Stack
- **Backend**: PHP 8+, MySQL 5.7/8.0
- **Frontend**: HTML5, CSS3, Bootstrap, vanilla JavaScript, Chart.js
- **Realtime/Notifications**: WebSockets-first approach; fallbacks with short polling where required.
- **Email**: PHPMailer 6.x (Composer managed)
- **Build Tools**: Composer for PHP dependencies; manual asset curation for front-end.

## Project Structure
```
admin/                Admin pages, modal views, and API endpoints
assets/               Global styles, scripts, images, audio
database/             SQL schema and seed data (`hrm_db.sql`)
user/                 Employee portal pages and APIs
vendor/               Composer dependencies (PHPMailer, autoload files)
config.php            Database credentials (update per environment)
service-worker.js     Service worker for caching/offline
manifest.json         PWA manifest
forgetpassword.php    SMTP-driven password reset entry point
PROJECT_DOCUMENTATION.md  In-depth functional documentation
```

## Environment Setup
1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd hrm
   ```
2. **Install dependencies**
   ```bash
   composer install
   ```
3. **Configure web server**
   - Place the project in your web root (e.g., `htdocs` for XAMPP).
   - Map a virtual host or local domain to the project directory.
4. **File permissions (Linux/macOS)**
   ```bash
   chmod -R 775 uploads storage
   ```
   Adjust the path if you relocate the upload directories.
5. **Update configuration** (see next section) and import the database.
6. **Access the portals**
   - Admin: `http://your-domain/admin/`
   - Employee: `http://your-domain/user/`

### Configuration Guidelines
- Edit `config.php` to set `DB_HOST`, `DB_USER`, `DB_PASS`, and `DB_NAME`. Keep this file restricted to database connectivity settings.
- Externalize SMTP credentials from `forgetpassword.php` into environment variables or a protected config file.
- Review any hard-coded URLs or asset paths if you deploy to a subdirectory or use HTTPS.
- Ensure PHP `file_uploads` and `max_upload_size` values cover expected document sizes.

## Database Initialization
1. Create a new MySQL database (example: `hrm_db`).
2. Import the bundled schema and sample data:
   ```bash
   mysql -u <user> -p <database_name> < database/hrm_db.sql
   ```
3. Update default admin/user credentials directly in the database or through the onboarding workflow.
4. Run any required ALTER statements as separate migration files per project convention.

## Progressive Web App
- `service-worker.js` implements cache-first strategies for static assets and network-first strategies for API calls, plus offline fallbacks.
- `manifest.json` defines icons, splash screens, and color theming for installable experiences.
- `assets/js/pwa-register.js` handles registration, updates, and install prompts; ensure HTTPS in production to unlock full PWA capabilities.
- Offline page (`offline.html`) provides a graceful experience when the network is unavailable.

## Security & Compliance
- Enforce HTTPS and secure cookies in production environments.
- Sanitize and validate all incoming data in API endpoints to prevent SQL injection and XSS.
- Limit PHPMailer credentials to secure storage; rotate SMTP passwords regularly.
- Monitor vendor dependencies (`composer update`) for vulnerability patches.
- Keep document uploads outside the public root if possible, or protect directories with access rules.

## Future Enhancements
- Advanced analytics with customizable reporting dashboards.
- Native mobile applications leveraging the existing APIs.
- Third-party integrations (ERP, accounting, and messaging platforms).
- Automated workflows for approvals and reminders.
- Internal communication tools (chat, announcements feed).

## Contact
- Website: [amaanullah.com](https://amaanullah.com)
- Email: [info@amaanullah.com](mailto:info@amaanullah.com)

Contributions, bug reports, and feature requests are welcome. Feel free to open an issue or submit a pull request to help the platform evolve.


