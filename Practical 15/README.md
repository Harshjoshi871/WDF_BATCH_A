# Practical 15: Final Integration, Testing, Documentation, GitHub Submission, and Local Deployment

## 1. Problem Definition
Integrate all modules developed across the semester into a single, cohesive, production-ready, deployable StudentHub web application. Verify end-to-end routing, mobile responsiveness, authentication & RBAC flows, MySQL database connectivity, security mitigations, and asynchronous AJAX functionality. Deploy locally under XAMPP and Apache Virtual Hosts and prepare the repository for final submission.

## 2. Key Questions & Viva Analysis
1. **Are all pages properly linked and navigable?**
   - Yes, the universal header navigation dynamically adapts across Guest, Student, and Admin user states. Every page (Home, About, Events, AJAX Hub, Timetable, Notices, Contact, Login, Register, Dashboards, and Management tools) contains working cross-links and breadcrumbs.
2. **Are sessions, roles, validations, and database operations working end-to-end?**
   - Yes:
     - Authentication uses Bcrypt password hashing (`password_hash` / `password_verify`).
     - Session fixation is eliminated with `session_regenerate_id(true)`.
     - Inactivity timeout automatically invalidates sessions after 15 minutes.
     - Role-Based Access Control (RBAC) isolates Student and Admin dashboards.
     - Database operations utilize parameterized MySQLi prepared statements preventing SQL injection.
3. **Are security and accessibility checklists completed?**
   - Yes:
     - **Security:** CSRF tokens on forms, strict MIME-type file verification (`finfo_file`), `.htaccess` script execution prevention in upload folders, and chronological audit logging.
     - **Accessibility:** Skip-to-content links, semantic HTML5 tags (`<header>`, `<nav>`, `<main>`, `<section>`, `<footer>`), keyboard navigation, and high-contrast color palettes adhering to WCAG 2.1 AA.
4. **Is project documentation sufficient for installation and testing?**
   - Yes, complete database schemas (`studenthub_master.sql`), credentials, step-by-step setup guides, and Apache virtual host configurations are documented.
5. **Is the project repository clean and properly committed?**
   - Yes, structured practical folders, dedicated `.htaccess` rules, clean assets, and documented commit workflows ensure submission readiness.

## 3. Integrated System Architecture
```
StudentHub Integrated Portal (Practical 15)
│
├── config/
│   ├── db.php                     # Centralized MySQLi & PDO database connection
│   └── auth.php                   # Authentication, sessions, timeout & RBAC guards
│
├── includes/
│   ├── header.php                 # Dynamic role-aware navigation bar & skip links
│   └── footer.php                 # Universal footer with sitemap & academic credits
│
├── css/
│   └── style.css                  # Master responsive design system (Grid & Flexbox)
│
├── js/
│   ├── main.js                    # Mobile menu toggle & password visibility
│   └── ajax_portal.js             # Asynchronous Fetch API CRUD controller
│
├── uploads/
│   ├── .htaccess                  # Prohibits PHP/script execution in media folder
│   └── default_poster.svg         # Fallback event banner graphic
│
├── index.php                      # Portal landing page with live KPI counters
├── about.php                      # About StudentHub & CHARUSAT FTE curriculum
├── events.php                     # Campus events feed with 1-click registration
├── event_register.php             # Student enrollment processor with duplicate check
├── timetable.php                  # Class schedule timetable
├── notices.php                    # Official campus circulars & announcements
├── contact.php                    # Inquiries form with CSV storage (P7 integration)
├── login.php                      # Unified login with remember-me & eye toggle
├── register.php                   # Student registration with regex & bcrypt hashing
├── logout.php                     # Secure logout with session & token invalidation
├── student_dashboard.php          # Student portal with enrolled events manager
├── admin_dashboard.php            # Admin control center with monthly analytics chart
├── manage_students.php            # Student CRUD (Search, filter, pagination, soft-delete)
├── export_students.php            # Filtered student records CSV exporter
├── manage_events.php              # Event CRUD with MIME-validated poster uploads
├── ajax_events.php                # Single Page Application event hub (P13 integration)
├── api.php                        # RESTful JSON API endpoint with status codes
├── audit_logs.php                 # Administrative security audit trail explorer
└── studenthub_master.sql          # Master database DDL with 25 seeded students
```

## 4. Default Testing Credentials
| Role | Email Address | Password | Landing Page |
|---|---|---|---|
| **Administrator** | `admin@studenthub.com` | `Admin@123` | `admin_dashboard.php` |
| **Student** | `harsh@gmail.com` | `Student@123` | `student_dashboard.php` |
| **Student** | `aarav.patel@studenthub.com` | `Student@123` | `student_dashboard.php` |

## 5. Local Deployment on XAMPP
1. Start **Apache** and **MySQL** services in the XAMPP Control Panel.
2. Open `http://localhost/phpmyadmin/` in your browser.
3. Create a new database named `studenthub` or import `studenthub_master.sql` directly into phpMyAdmin.
4. Copy the `Practical 15` folder or files to `C:\xampp\htdocs\StudentHub\`.
5. Access the application in any web browser at:
   `http://localhost/StudentHub/`
