# HRM Portal - Complete Project Documentation

## Project Overview

HRM Portal ek comprehensive Human Resource Management System hai jo organizations ke liye employee management, attendance tracking, leave management, payroll processing, aur announcements ke liye design kiya gaya hai. System do main user roles support karta hai: Admin aur Regular Users.

---

## System Architecture

### User Roles
1. **Admin Role**: Complete system access with management capabilities
2. **User Role**: Limited access for personal information and self-service features

### Access Control
- Session-based authentication system
- Role-based access control (RBAC)
- Automatic redirection based on user role after login
- Session timeout protection (9 hours inactivity)

---

## Module 1: Authentication & Security

### Login System
- **Location**: `login.php`
- **Features**:
  - Email-based authentication
  - Password verification using secure hashing
  - Account status checking (active/inactive)
  - Automatic role-based redirection after login
  - Session management with activity tracking
  - "Remember me" functionality support
  - Password visibility toggle

### Session Management
- **Admin Session Check**: `admin/session_check.php`
- **User Session Check**: `user/session_check.php`
- **Features**:
  - Session validation on every page load
  - Automatic logout on session expiry
  - Role verification
  - Unauthorized access prevention

### Password Recovery
- **Files**: `forgetpassword.php`, `recoverpassword.php`, `resetpassword.php`
- **Features**:
  - Email-based password reset
  - Secure token generation
  - Password reset link expiration
  - New password validation

---

## Module 2: Admin Dashboard

### Dashboard Overview
- **Location**: `admin/index.php`
- **Features**:
  - Real-time statistics cards:
    - Total Employees count with trend
    - Present Today count with percentage
    - On Leave count
    - Departments count
  - Interactive charts:
    - Weekly Attendance Overview (line chart)
    - Employees by Job Type (Internship, Probation, Permanent)
    - Department-wise Employee Distribution (donut chart)
    - Monthly Salary Overview (bar chart)
    - Gender Distribution (donut chart with stats)
    - Employee Joining & Exit Trend (line chart)
    - Leave Requests Overview (chart)
  - Quick action buttons for common tasks
  - Clickable statistics cards that open detailed modals
  - Department-wise filtering in modals

### Statistics Features
- Real-time data updates
- Trend indicators (increase/decrease)
- Percentage calculations
- Historical data comparison
- Modal views for detailed information

---

## Module 3: Employee Management

### All Employees Page
- **Location**: `admin/all-employee.php`
- **Features**:
  - Complete employee listing with DataTables
  - Advanced filtering:
    - Employee ID filter
    - Name search
    - Department filter
    - Active/Deleted employees toggle
  - Employee operations:
    - Add new employee (multi-step form)
    - Edit employee details
    - View complete employee profile
    - Soft delete/restore employees
  - Department management:
    - Add new departments
    - Edit department details
    - Assign department managers and heads
    - Delete departments
  - Shift management:
    - Create new shifts
    - Define shift timings
    - Set grace time
    - Configure half-day hours
    - View all shifts in table format
  - Export functionality (Excel, PDF, Print)

### Employee Profile Sections
1. **Personal Information**:
   - Name (First, Middle, Last)
   - Gender
   - Date of Birth
   - Phone
   - Email (with duplicate checking)
   - Address
   - ID Card Number
   - Emergency Contact & Relation

2. **Job Information**:
   - Job Title/Designation
   - Department & Sub-Department
   - Shift Timing
   - Job Type (Internship/Probation/Permanent)
   - Joining Date
   - Salary

3. **Bank Information**:
   - Bank Name (multiple bank options)
   - Account Type (IBAN/IBFT/Mobile Banking)
   - Account Title
   - Account Number (with validation based on type)
   - Bank Branch

4. **Education Information**:
   - Qualification
   - Degree/Certification
   - Professional Expertise
   - College/University

5. **Experience Information**:
   - Last Employer
   - Last Designation
   - Experience From/To Dates

6. **Document Attachments**:
   - Resume/CV (PDF, DOC, DOCX)
   - ID Card (JPG, PNG, PDF)
   - Other Documents (multiple files)

### Employee View Modal
- Complete employee profile display
- Leave history integration
- Document viewing and downloading
- Professional profile layout

---

## Module 4: Attendance Management

### Admin Attendance Page
- **Location**: `admin/attendance.php`
- **Features**:
  - Complete attendance records table
  - Advanced filtering:
    - Employee ID
    - Employee Name
    - Department
    - Status (Present/Absent/Late/Half-day)
    - Date range
  - Attendance status tracking:
    - Present
    - Absent
    - Late (with grace time calculation)
    - Half-day
  - Auto Attendance feature:
    - Bulk attendance marking
    - Filter employees by ID, Name, Department
    - Select date for attendance
    - Checkbox selection for multiple employees
  - Monthly attendance reports:
    - Calendar view for date selection
    - Date range filtering
    - Summary cards (Present, Absent, Late, Half-day)
    - Detailed attendance table
  - Daily attendance detail modal:
    - Check-in/Check-out times
    - Working hours calculation
    - Status information
    - Break records
  - Export functionality

### User Attendance Page
- **Location**: `user/userattendance.php`
- **Features**:
  - Personal attendance calendar view
  - Date range filtering
  - Attendance cards showing:
    - Date
    - Status badge
    - Check-in time
    - Check-out time
    - Working hours
    - Break information
  - Attendance details modal:
    - Complete day information
    - Break records with timings
    - Total break duration
    - Working hours breakdown
  - Visual status indicators:
    - Color-coded cards (Present/Absent/Late/Half-day)
    - Icons for different statuses

### Attendance Features
- Automatic status calculation based on shift timings
- Grace time consideration for late arrivals
- Half-day detection
- Working hours calculation
- Break time tracking
- Real-time updates

---

## Module 5: Leave Management

### Admin Leave Requests
- **Location**: `admin/leave-requests.php`
- **Features**:
  - Leave request cards display:
    - Employee information
    - Leave type
    - Date range
    - Number of days
    - Reason
    - Applied date
    - Status badge
    - Attached documents
  - Search and filtering:
    - Employee ID and Name
    - Department
    - Leave Type
  - Leave actions:
    - Approve leave requests
    - Reject leave requests
    - Add admin comments
    - View leave documents
  - Leave type management:
    - Add new leave types
    - View existing leave types
    - Delete leave types
  - Real-time badge notification for pending requests
  - Sound notification for new requests

### User Leave History
- **Location**: `user/leave-history.php`
- **Features**:
  - Personal leave applications table
  - Leave application form:
    - Leave type selection
    - Date range (From/To)
    - Reason textarea
    - Document upload (optional)
  - Leave status tracking:
    - Pending
    - Approved
    - Rejected
  - Leave editing (for pending requests):
    - Update leave dates
    - Modify reason
    - Change leave type
    - Update documents
  - Leave details modal:
    - Complete leave information
    - Admin comments
    - Document viewing
  - Date range filtering
  - Export functionality

### Leave Features
- Automatic day calculation
- Leave type validation
- Document attachment support
- Admin comment system
- Status change notifications
- Leave balance tracking

---

## Module 6: Payroll Management

### Admin Payroll
- **Location**: `admin/payroll.php`
- **Features**:
  - Complete payroll table with:
    - Employee information
    - Basic salary
    - Allowances (Fuel, House Rent, Utility, Mobile)
    - Deductions (Provident Fund, Professional Tax, Loan)
    - Leave deductions
    - Late arrival deductions
    - Half-day deductions
    - Total earnings calculation
    - Total deductions calculation
    - Net salary calculation
    - Bank information
    - Payment date
  - Advanced filtering:
    - Employee ID
    - Employee Name
    - Designation
    - Month & Year picker
  - Auto Generate Payslip:
    - Employee selection with filters
    - Bulk payslip generation
    - Automatic calculation of:
      - Leave deductions
      - Late arrival deductions
      - Half-day deductions
      - Total earnings
      - Net salary
  - Edit payroll:
    - Modify deductions
    - Update payment date
    - Change bank information
    - Manual adjustments
  - Payslip viewing:
    - Professional payslip format
    - Company branding
    - Complete salary breakdown
    - Amount in words
    - Print functionality
  - Export functionality

### User Payroll
- **Location**: `user/user-payroll-salary.php`
- **Features**:
  - Personal payroll history table
  - Payslip viewing:
    - Complete salary details
    - Earnings breakdown
    - Deductions breakdown
    - Net salary
    - Print payslip
  - Month-wise filtering
  - Salary trend visualization

### Payroll Calculation Logic
- **Earnings**:
  - Basic Salary
  - Fuel Allowance
  - House Rent Allowance
  - Utility Allowance
  - Mobile Allowance
- **Deductions**:
  - Provident Fund
  - Professional Tax
  - Loan
  - Leave deductions (per day calculation)
  - Late arrival deductions
  - Half-day deductions
- **Net Salary**: Total Earnings - Total Deductions

---

## Module 7: Announcements

### Admin Announcements
- **Location**: `admin/admin-annoucement.php`
- **Features**:
  - Rich text editor for announcements
  - Announcement creation:
    - Title
    - Content (with formatting options)
    - Start date
    - End date
  - Announcement timeline display:
    - Chronological order
    - Date badges
    - Category tags
    - Edit and delete options
  - Search functionality
  - Announcement editing
  - Announcement deletion

### User Announcements
- **Location**: `user/userannouncement.php`
- **Features**:
  - View all active announcements
  - Announcement timeline:
    - Date-wise organization
    - Read/Unread status
    - Badge notification for unread announcements
  - Real-time notification system:
    - Browser notifications
    - Sound alerts
    - Badge count updates
  - Auto-refresh functionality

### Announcement Features
- Rich text formatting (bold, italic, lists, links, colors)
- Date range validity
- Read/Unread tracking
- Notification system
- Timeline visualization

---

## Module 8: Notifications

### Admin Notifications
- **Location**: `admin/notifications.php`
- **Features**:
  - Attendance-related notifications
  - Message display:
    - Employee information
    - Attendance date
    - Reason/message content
    - Time ago display
    - Status badges
  - Mark all as read functionality
  - Auto-refresh (30 seconds)
  - Badge count updates
  - Click to view attendance details
  - Redirect to attendance page with filters

### Notification Features
- Real-time updates
- Sound notifications
- Browser push notifications
- Badge count management
- Message categorization
- Time-based sorting

---

## Module 9: User Dashboard

### User Dashboard
- **Location**: `user/userdashboard.php`
- **Features**:
  - Quick statistics cards:
    - Today's Status (Check-in time, Status)
    - Monthly Attendance Percentage
    - Total Leave Balance (Annual/Sick)
    - Department Information
  - Date range filtering
  - Quick action buttons:
    - My Attendance
    - Leave History
    - Payroll
    - Announcements
  - Charts and analytics:
    - Monthly Attendance Overview (donut chart)
    - Salary Trend (line chart)
    - Leave Usage Analytics (chart)
    - Work Hours Analysis (chart)
  - Recent announcements display
  - Real-time data updates

---

## Module 10: Break Time Management

### Break Time
- **Location**: `user/break.php`
- **Features**:
  - Break start/end functionality
  - Break duration tracking
  - Break history
  - Total break time calculation
  - Integration with attendance system

---

## Module 11: Joining Form

### New Joining Employees
- **Location**: `admin/joining-form.php`
- **Features**:
  - Display inactive/new employees in card format
  - Employee card information:
    - Name
    - Job Title
    - Contact information
    - Department
    - Joining date
    - Created date
  - Complete joining form modal:
    - Personal Information
    - Contact Information
    - Employment Information
    - Education Information
    - Experience Information
    - Banking Information
    - Administrative Access (Department, Job Type, Salary, Shift, Password)
    - Document Attachments
  - Employee activation:
    - Complete profile information
    - Set password
    - Assign department
    - Set job type
    - Configure shift
    - Set salary
    - Activate employee account
  - Employee deletion (permanent)
  - Document viewing and downloading

---

## Module 12: Profile Management

### Admin Profile
- **Location**: `admin/top-bar.php` (Profile Panel)
- **Features**:
  - Profile information display
  - Profile editing:
    - Update personal information
    - Change profile picture
    - Update contact details
  - Profile image upload with drag & drop
  - Real-time profile updates

### User Profile
- **Location**: `user/topbar.php` (Profile Panel)
- **Features**:
  - View own profile:
    - Complete personal information
    - Job information
    - Bank information
    - Education information
    - Experience information
    - Leave history
    - Uploaded documents
  - Edit profile:
    - Update personal information
    - Change profile picture
    - Update contact details
  - Profile image upload
  - Document viewing

---

## Module 13: Department Management

### Department Features
- **Location**: Integrated in `admin/all-employee.php`
- **Features**:
  - Add new departments
  - Edit department details
  - Assign department manager
  - Assign department head
  - View all departments
  - Delete departments
  - Department-wise employee filtering
  - Department statistics

---

## Module 14: Shift Management

### Shift Features
- **Location**: Integrated in `admin/all-employee.php`
- **Features**:
  - Create new shifts
  - Define shift name
  - Set start time
  - Set end time
  - Configure grace time (in minutes)
  - Set half-day hours
  - View all shifts
  - Edit shifts
  - Delete shifts
  - Assign shifts to employees

---

## Module 15: Progressive Web App (PWA)

### PWA Features
- **Service Worker**: `service-worker.js`
- **Manifest**: `manifest.json`
- **Registration**: `assets/js/pwa-register.js`
- **Features**:
  - Offline support
  - App installation prompt
  - Caching strategy:
    - Static assets (cache first)
    - API calls (network first)
  - Offline page display
  - Background sync support
  - Push notification support
  - App shortcuts
  - Install banner

---

## Module 16: Data Export & Reporting

### Export Features
- Available in multiple modules:
  - Employee list export
  - Attendance export
  - Payroll export
  - Leave history export
- Export formats:
  - Excel (XLSX)
  - PDF
  - Print
- Features:
  - Filtered data export
  - Formatted reports
  - Date range selection
  - Custom column selection

---

## Module 17: Real-time Features

### Real-time Updates
- **Notification System**:
  - Leave request notifications
  - Announcement notifications
  - Attendance notifications
  - Badge count updates
- **Update Mechanisms**:
  - Auto-refresh intervals (30 seconds)
  - Page visibility change detection
  - Real-time badge updates
  - Sound notifications
  - Browser push notifications

---

## Module 18: Search & Filtering

### Search Features
- Available across all major modules
- Filter types:
  - Text search (ID, Name)
  - Dropdown filters (Department, Status, Type)
  - Date range filters
  - Multi-criteria filtering
- Features:
  - Real-time filtering
  - Clear filters option
  - Filter persistence
  - Combined filters support

---

## Module 19: Document Management

### Document Features
- **Upload Support**:
  - Resume/CV (PDF, DOC, DOCX)
  - ID Card (JPG, PNG, PDF)
  - Other documents (multiple formats)
  - Leave documents
- **Document Operations**:
  - View documents
  - Download documents
  - Delete documents
  - Document preview
- **Storage**:
  - Organized folder structure
  - Secure file paths
  - File type validation

---

## Module 20: Analytics & Reporting

### Analytics Features
- **Dashboard Analytics**:
  - Employee statistics
  - Attendance trends
  - Department distribution
  - Gender distribution
  - Salary distribution
  - Leave trends
  - Joining/Exit trends
- **Chart Types**:
  - Line charts
  - Bar charts
  - Donut charts
  - Pie charts
- **Report Features**:
  - Monthly reports
  - Date range reports
  - Department-wise reports
  - Employee-wise reports

---

## Database Structure (Inferred)

### Main Tables
1. **employees**: Complete employee information
2. **departments**: Department details
3. **shifts**: Shift timings and configurations
4. **attendance**: Daily attendance records
5. **leave_requests**: Leave applications
6. **leave_types**: Available leave types
7. **payroll**: Monthly payroll records
8. **announcements**: Company announcements
9. **notifications**: System notifications

---

## Security Features

1. **Authentication**:
   - Secure password hashing
   - Session management
   - Role-based access control

2. **Data Protection**:
   - Prepared statements (SQL injection prevention)
   - Input validation
   - XSS protection
   - File upload validation

3. **Session Security**:
   - HttpOnly cookies
   - Session timeout
   - Activity tracking
   - Automatic logout

---

## User Interface Features

1. **Responsive Design**:
   - Mobile-friendly layout
   - Tablet optimization
   - Desktop experience

2. **Modern UI Elements**:
   - Gradient buttons
   - Card-based layouts
   - Modal dialogs
   - Toast notifications
   - Loading indicators

3. **User Experience**:
   - Intuitive navigation
   - Quick actions
   - Search functionality
   - Filter options
   - Export capabilities

---

## System Integrations

1. **Email System**: Password recovery, notifications
2. **File Storage**: Document uploads and management
3. **Chart Libraries**: Data visualization
4. **Export Libraries**: Excel, PDF generation
5. **Rich Text Editor**: Announcement content

---

## Workflow Processes

### Employee Onboarding
1. Employee registration/joining form submission
2. Admin review of new joining employees
3. Profile completion by admin
4. Account activation
5. System access granted

### Leave Application Process
1. User applies for leave
2. Leave request appears in admin panel
3. Admin reviews and approves/rejects
4. User receives notification
5. Leave history updated

### Payroll Process
1. Attendance data collection
2. Leave and late arrival calculations
3. Auto payslip generation
4. Manual adjustments (if needed)
5. Payslip generation and payment

### Attendance Tracking
1. Employee check-in
2. System records time
3. Status calculation (Present/Late)
4. Break time tracking
5. Check-out recording
6. Working hours calculation

---

## Error Handling

1. **Form Validation**:
   - Client-side validation
   - Server-side validation
   - Real-time feedback
   - Error messages

2. **System Errors**:
   - Graceful error handling
   - User-friendly messages
   - Error logging
   - Fallback mechanisms

---

## Performance Features

1. **Optimization**:
   - Lazy loading
   - Data pagination
   - Efficient queries
   - Caching strategies

2. **User Experience**:
   - Loading indicators
   - Smooth transitions
   - Responsive feedback
   - Quick actions

---

## Future Enhancement Areas

1. **Advanced Reporting**: Custom report builder
2. **Mobile App**: Native mobile application
3. **Advanced Analytics**: Predictive analytics
4. **Integration**: Third-party system integration
5. **Automation**: Workflow automation
6. **Communication**: Internal messaging system

---

## Conclusion

Yeh HRM Portal ek comprehensive system hai jo organizations ke liye complete human resource management solution provide karta hai. System modular design follow karta hai, jisse har module independently maintain aur enhance kiya ja sakta hai. User-friendly interface, real-time updates, aur comprehensive features ke saath yeh system modern HRM requirements ko efficiently handle karta hai.



