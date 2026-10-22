# Practical 14: Admin Dashboard with Role-Based Access Control, Dynamic Menu, and Audit Log

## 1. Problem Definition
Develop an administrative dashboard delivering dynamic database aggregation metrics (total students, events, registrations, active users), strict Role-Based Access Control (RBAC), a role-dependent dynamic navigation menu, active/inactive user account toggling, interactive analytics charts, and a comprehensive security audit log trail.

## 2. Key Questions & Viva Analysis
1. **Are dashboard values loaded dynamically from the database?**
   - Yes, values are queried live on every request via SQL aggregations:
     - Total Students: `SELECT COUNT(*) FROM students`
     - Total Events: `SELECT COUNT(*) FROM events`
     - Total Registrations: `SELECT COUNT(*) FROM registrations`
     - Active Users: `SELECT COUNT(*) FROM users WHERE status = 'active'`
2. **Is access restricted to admin users?**
   - Yes, `session_check.php` confirms that `$_SESSION['user_id']` exists and `$_SESSION['role'] === 'admin'`. Non-authenticated users are routed to `login.php?msg=login_required` and unauthorized roles (e.g., students) receive `?msg=unauthorized`.
3. **Are role-based menus working?**
   - Yes, `$menu_items` array renders dynamically: administrators see management links (Students, Events, AJAX Portal, Audit Logs, Overview), whereas student users see only profile and registration links.
4. **Are important admin actions recorded in `audit_logs`?**
   - Yes, critical system events (logins, logouts, record additions, updates, deletions, and user status toggles) are written to `audit_logs` capturing `user_id`, `action`, `details`, client `ip_address`, and timestamp.

## 3. Extensions Implemented
- **Intermediate Extension (User Status Toggle):** `toggle_user.php` enables one-click switching between `active` and `inactive` status for any user account with safety guards preventing administrators from locking their own session.
- **Advanced Extension (Monthly Registration Analytics Chart):** Dynamic SQL query grouping registrations by month (`DATE_FORMAT(registration_date, '%b %Y')`) rendered as a responsive CSS bar chart with hover tooltips and dynamic scale heights.

## 4. Test Scenarios
| Test ID | Test Scenario | Steps | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-01 | Admin Authentication | Log in with `admin@studenthub.com` | Access granted to Admin Dashboard | Pass |
| TC-02 | RBAC Guard | Student logs in and attempts to access `Practical 14/index.php` | Blocked and redirected with `msg=unauthorized` | Pass |
| TC-03 | Dynamic KPI Cards | Add new student in Practical 11, refresh dashboard | Total Students count increments dynamically | Pass |
| TC-04 | User Status Toggle | Click "Deactivate" next to a user | Status updates to `Inactive`, logged in audit trail | Pass |
| TC-05 | Self-Deactivation Guard | Admin clicks toggle on own account | Blocked with security notice | Pass |
| TC-06 | Audit Log Inspection | Open `audit_logs.php`, filter by action | Displays chronological timeline with client IP addresses | Pass |
