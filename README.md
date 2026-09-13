# Federal Polytechnic Ilaro
## Campus Safety & Emergency Alert System (CES)

An integrated, modern, and fully responsive campus crisis communication and emergency response management platform designed specifically for **Federal Polytechnic Ilaro (FPI)**. The system enables students and staff to submit rapid emergency distress reports, allows the institutional Chief Security Unit and medical teams to monitor and triage incidents in real time on an interactive Leaflet GIS map, and broadcasts campus-wide emergency safety alerts.

---

## 1. Project Overview

In a tertiary institution with thousands of students, faculty, and administrative facilities, prompt response to emergencies—such as laboratory fires, electrical sparks, medical traumas, physical assaults, or thefts—is vital. The **Campus Safety & Emergency Alert System (CES)** bridges the gap between incident witnesses and emergency response units, replacing slow, uncoordinated reports with immediate digital triage, automated tracking milestones, and emergency broadcasts.

---

## 2. Key Features

### Public Portal
- **Hero & Emergency Action Section:** High-visibility "Report an Emergency" CTA, live active alert banners, and 24/7 hotline dial buttons.
- **Incident Lifecycle Guide:** Clear 4-step explanation of the reporting, dispatch, intervention, and resolution workflow.
- **10+ Emergency Categories:** Fire, Medical Emergency, Security Threat, Accident, Theft, Physical Violence, Gas/Fume Leak, Infrastructure Failure, Natural Hazard, and Other.
- **Emergency Telephone Directory:** Categorized contact hotlines for Central Security, Health Centre Clinic Ambulance, Ogun State Fire Service, and Nigeria Police Force (Ilaro Division).
- **Safety Information Guides:** Step-by-step procedures for fire safety (P.A.S.S. protocol), medical first aid, Run-Hide-Tell security response, and designated campus evacuation assembly zones.
- **Frequently Asked Questions (FAQ):** Interactive accordion explaining anonymous reporting, response speeds, and coverage.

### Student & Staff Portal
- **Role-Based Authentication:** Secure registration and login for students (Matric No.) and staff (Staff ID) with `password_hash()` and session protection.
- **Executive Dashboard:** Live stats (My Reports, Pending, In Progress, Resolved, Active Alerts), urgent broadcast alerts, and quick emergency dispatch card.
- **Interactive Emergency Reporting Form:**
  - Emergency category selector with icons and visual badges.
  - Urgency & severity selector (Low, Medium, High, Critical).
  - Landmark picker with coordinates auto-filling an interactive **Leaflet.js** map with draggable pinpoint marker.
  - Incident description textarea.
  - Photographic evidence upload (JPG, PNG, WEBP with 5MB validation).
  - Callback phone number and contact permission checkbox.
  - Automated generation of unique reference ID (`CES-2026-00125`).
- **My Reports History:** Filter by status (Pending, In Progress, Resolved), detailed milestone timeline, administrative remarks, and evidence viewer.
- **Real-time Notifications:** In-app notification center for report status updates, administrator triage remarks, and broadcast warnings.
- **User Profile & Security:** Update department, emergency contact telephone, and securely change password.

### Administrator Command Console
- **Executive Overview:** Real-time KPI metrics (Total Users, All Reports, Pending Review, Active Emergencies, Resolved, Critical Incidents).
- **Live Campus Incident GIS Map (Leaflet.js):**
  - Incident markers color-coded by severity:
    - **Critical:** Pulsing Red Pin
    - **High:** Orange Pin
    - **Medium:** Amber Pin
    - **Low:** Emerald Green Pin
  - Interactive popup with incident details and one-click direct triage link.
  - Dynamic filters: All, Active Only, Critical, High, Resolved.
- **Emergency Reports Register:**
  - Multi-criteria search and filter engine (Keywords, Category, Severity, Status, Location, Date Range).
  - Printable reports docket.
- **Single Incident Triage & Investigation (`report-view.php`):**
  - Reporter profile dossier (or anonymous indication).
  - GPS coordinates and interactive mini-map.
  - Photo evidence preview with modal lightbox zoom.
  - Triage console: Update status (`Pending`, `Acknowledged`, `In Progress`, `Resolved`, `Rejected`).
  - Response assignment: Assign to specific rapid response squads (Alpha Patrol, Health Centre Ambulance, Fire Safety Marshals, Electrical Maintenance, etc.).
  - Administrative remarks and audit trail.
  - Automated reporter notification dispatch on update.
- **Emergency Broadcast Center:**
  - Compose high-priority safety advisories with targeted audience (Everyone, Students Only, Staff Only).
  - Configurable duration and expiration dates.
  - Instant high-priority broadcast banner rendered across student and public portals.
  - Active vs. archived broadcast management.
- **User Management Directory:**
  - Search and filter students, staff, and administrators.
  - Account status toggle (Active / Inactive).
- **Emergency Contacts Directory Management:**
  - Add, edit, and manage campus hotlines and dispatch numbers.
- **Analytics & Trends (`analytics.php`):**
  - Interactive **Chart.js** visualizations: Category breakdown, severity ratio, resolution rates, and campus hotspot zones.
- **Security & Audit Activity Logs (`logs.php`):**
  - Comprehensive audit trail recording user logins, report filings, status updates, and broadcast transmissions with IP addresses and timestamps.
- **System Settings (`settings.php`):**
  - Configure institution name, acronym, hotlines, default map coordinates, and broadcast banner visibility.

---

## 3. Technology Stack & Requirements

- **PHP:** Version 8.0 or higher (Tested with PHP 8.2)
- **Database:** MySQL 5.7+ or MariaDB 10.4+ (PDO extension enabled)
- **Web Server:** Apache 2.4+ (XAMPP for Windows)
- **Frontend:**
  - HTML5 & CSS3
  - Tailwind CSS (via CDN)
  - Font Awesome 6 & Lucide Icons
  - Leaflet.js v1.9.4 (Interactive GIS Maps & Coordinate Picker)
  - Chart.js (Data Analytics & Incident Trends)
- **Dependencies:** **Zero external server-side package dependencies**. No Node.js, Composer, or external frameworks required.

---

## 4. XAMPP Installation & Setup

1. Download and install **XAMPP** from [apachefriends.org](https://www.apachefriends.org/) (Choose PHP 8.0 or later).
2. Start the **XAMPP Control Panel**.
3. Start the **Apache** service (Port 80/443).
4. Start the **MySQL** service (Port 3306).

---

## 5. Database Setup

The database schema and demo records are provided in `database/database.sql`.

### Option A: Automatic Setup via Script (Recommended)
Open your terminal / command prompt and run:
```bash
"C:\xampp\php\php.exe" "C:\xampp\htdocs\campus-safety\setup_db.php"
```
*Or simply navigate to `http://localhost/campus-safety/setup_db.php` in your web browser!*

### Option B: Manual Setup via phpMyAdmin
1. Open your browser and visit: `http://localhost/phpmyadmin/`
2. Click **Databases** and create a new database named: `campus_safety_db` (Collation: `utf8mb4_unicode_ci`).
3. Select `campus_safety_db` from the left sidebar.
4. Click the **Import** tab.
5. Click **Choose File** and select `database/database.sql` from the `campus-safety` directory.
6. Click **Import** at the bottom of the page.

---

## 6. How to Run the Project

1. Ensure the `campus-safety` folder is placed inside your XAMPP web root:
   ```
   C:\xampp\htdocs\campus-safety
   ```
2. Open your web browser (Chrome, Edge, Firefox).
3. Access the application at:
   ```
   http://localhost/campus-safety/
   ```

---

## 7. Demo Login Credentials

Pre-configured accounts are provided for immediate testing. On the login page, you can also use the **"One-Click Fill"** buttons to automatically populate these credentials:

| Role | Email | Password | Details |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@ilaropoly.edu.ng` | `Admin@12345` | Chief Security Desk & Command Center |
| **Staff Member** | `staff@ilaropoly.edu.ng` | `Staff@12345` | Academic Faculty (Computer Science) |
| **Student** | `student@ilaropoly.edu.ng` | `Student@12345` | ND Student (Computer Science) |

---

## 8. Folder Structure

```
campus-safety/
│
├── index.php                      # Public Landing Page with Hero, CTAs, Categories, FAQ
├── about.php                      # About the System, Mission & Campus Coverage
├── safety-guides.php              # Comprehensive Campus Emergency Protocols & First Aid
├── contacts.php                   # Campus Emergency Telephone Directory
├── login.php                      # Secure Authentication with 1-Click Demo Fill
├── register.php                   # Student & Staff Registration Form
├── logout.php                     # Secure Session Termination
├── setup_db.php                   # Automated Database Installer
├── test_system.php                # Automated System Verification Suite
│
├── config/
│   └── database.php               # PDO Database Connection, Base URL & Constants
│
├── includes/
│   ├── header.php                 # Public Header, Navigation & Emergency Alert Banner
│   ├── footer.php                 # Public Footer with Hotlines & Resource Links
│   ├── nav-portal.php             # Authenticated Portal Shell (Sidebar, Topbar & Notifications)
│   ├── footer-portal.php          # Portal Footer Script Closures
│   ├── auth.php                   # Authentication Guards & Role Handlers
│   └── functions.php              # Flash Messages, CSRF, Notifications, Badges, Time Formatting
│
├── student/
│   ├── dashboard.php              # Student/Staff Dashboard with Stats & Active Alerts
│   ├── report-emergency.php       # Emergency Reporting Form with Leaflet Map & File Upload
│   ├── my-reports.php             # User's Submitted Incidents & Milestone Timeline
│   ├── alerts.php                 # Active & Archived Campus Emergency Advisories Feed
│   ├── notifications.php          # Notification Center with Mark-as-Read Actions
│   └── profile.php                # Contact Info Update & Password Change
│
├── admin/
│   ├── dashboard.php              # Executive Command Center Metrics & Mini GIS Map
│   ├── reports.php                # Full Emergency Incident Register with Search & Filters
│   ├── report-view.php            # Detailed Incident Dossier, Status Updates & Unit Assignment
│   ├── map.php                    # Fullscreen Interactive Campus Map with Severity Filters
│   ├── alerts.php                 # Emergency Alert Broadcast Management
│   ├── users.php                  # User Management Directory (Search, Filter, Deactivate)
│   ├── contacts.php               # Emergency Hotlines & Directory Management
│   ├── analytics.php              # Chart.js Visual Incident Statistics & Hotspot Rankings
│   ├── logs.php                   # System Security & Activity Audit Trail
│   └── settings.php               # Institutional, Hotline, and Map Settings
│
├── assets/
│   ├── css/
│   │   └── style.css              # Custom Styling, Theme Variables & Animations
│   ├── js/
│   │   ├── main.js                # Drawer Toggles, Toast Faders & Leaflet Map Helpers
│   ├── images/
│   │   └── logo.svg               # Federal Polytechnic Ilaro Institutional Safety Badge
│   └── uploads/
│       └── reports/               # Secure Storage for Uploaded Incident Photo Evidence
│
└── database/
    └── database.sql               # MySQL Schema & Realistic Sample Data
```

---

## 9. How to Change Database Credentials

Database connection parameters are managed in `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'campus_safety_db');
define('DB_PORT', 3306);
```
Modify these variables if your MySQL server uses a password or a different port.

---

## 10. How to Customize Campus Locations

Federal Polytechnic Ilaro landmarks (Science Complex, Engineering workshops, DSA, Hostels, Gates) are pre-loaded in the `campus_locations` database table.

To add or modify locations:
1. Log in as an Administrator.
2. Navigate to **System Settings** (`admin/settings.php`) to update the default center map coordinates.
3. You can add new campus landmarks directly in MySQL or through the database seed file:
   ```sql
   INSERT INTO campus_locations (name, category, latitude, longitude, description) 
   VALUES ('New Faculty Building', 'Academic', 6.8930000, 3.0160000, 'Lecture theatres and staff offices');
   ```

---

## 11. How to Customize Emergency Contacts

Emergency dispatch telephone numbers can be managed without writing code:
1. Log in with an **Administrator** account (`admin@ilaropoly.edu.ng`).
2. Navigate to **Emergency Directory** in the admin sidebar (`admin/contacts.php`).
3. Fill out the "Register New Emergency Hotline" form to add response units.
4. Contacts appear immediately on the public landing page, contacts directory, and student dashboards with clickable dial links.

---

## 12. Security Considerations Implemented

- **Password Security:** One-way password hashing using PHP's native `password_hash()` with `PASSWORD_BCRYPT`. Plaintext passwords are never saved.
- **SQL Injection Prevention:** 100% of database queries use PDO prepared statements with parameterized inputs.
- **Cross-Site Request Forgery (CSRF):** Secure anti-CSRF tokens on all POST forms.
- **Cross-Site Scripting (XSS):** HTML output escaping via `htmlspecialchars()` helper function `e()`.
- **Session Protection:** Session regeneration on authentication (`session_regenerate_id(true)`) to prevent session fixation attacks.
- **Role-Based Access Control:** Strict server-side route guards (`require_login()`, `require_admin()`) preventing unauthorized URL access.
- **File Upload Security:** File extension and MIME type verification restricting evidence uploads strictly to valid image formats (JPG, JPEG, PNG, WEBP) with 5MB file limits.

---

&copy; 2026 Federal Polytechnic Ilaro, Ogun State, Nigeria. Developed for Campus Safety & Emergency Management.
