# Practical 10: Secure Login/Logout with Sessions, Role-Based Access, Timeout, and Remember-Me Token

## 1. Problem Definition
Develop secure login and logout functionality for StudentHub using PHP sessions. Verify passwords with `password_verify()`, regenerate session IDs upon login to eliminate session fixation, restrict dashboard access using Role-Based Access Control (RBAC), implement automatic session timeout upon inactivity, and configure a secure remember-me token stored as a cryptographic hash.

## 2. Key Questions & Viva Analysis
1. **Are sessions started and destroyed securely?**
   - Yes:
     - Sessions are initialized with secure parameters (`httponly = true`, `samesite = 'Lax'`, and HTTPS conditional flag).
     - Upon successful login, `session_regenerate_id(true)` replaces the session identifier while invalidating the old one.
     - Logout explicitly deletes the persistent remember token from the database, unsets `$_SESSION`, expires the session cookie with `setcookie(session_name(), '', time() - 42000)`, and terminates the session with `session_destroy()`.
2. **Is `password_verify()` used correctly?**
   - Yes, user-submitted plaintext passwords are verified directly against the stored bcrypt hash from the database: `password_verify($password, $user['password'])`.
3. **Is unauthorized dashboard access blocked?**
   - Yes, `session_helper.php` defines `require_role('admin')` and `require_role('student')`. Any unauthenticated user attempting to access a dashboard is redirected to `login.php?msg=login_required`, and unauthorized roles receive `?msg=unauthorized`.
4. **Is session timeout implemented?**
   - Yes, each HTTP request compares `time() - $_SESSION['last_activity']`. If the difference exceeds `SESSION_TIMEOUT_DURATION` (900 seconds / 15 minutes), the session is cleared and redirected to `login.php?msg=timeout`.
5. **Are admin and student users redirected to appropriate dashboards?**
   - Yes:
     - `role === 'admin'` users are routed to `admin_dashboard.php`.
     - `role === 'student'` users are routed to `student_dashboard.php`.

## 3. Extensions Implemented
- **Intermediate Extension (Last-Login Timestamp):** Upon successful authentication, the `users` table records `last_login = NOW()`, displayed on the user's dashboard.
- **Advanced Extension (Secure Remember-Me Architecture):**
  - Generates 32 random bytes via `random_bytes(32)`.
  - Computes `hash('sha256', $raw_token)` and saves this hash in `users.remember_token` (ensuring database breaches do not leak login tokens).
  - Sends the raw token to the client inside an `HttpOnly`, `SameSite=Lax` cookie valid for 30 days.

## 4. Test Scenarios
| Test ID | Test Scenario | Input Credentials | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-01 | Admin Login | `admin@studenthub.com` / `Admin@123` | Redirects to `admin_dashboard.php` | Pass |
| TC-02 | Student Login | `harsh@gmail.com` / `Student@123` | Redirects to `student_dashboard.php` | Pass |
| TC-03 | Wrong Password | `admin@studenthub.com` / `WrongPass` | Blocked with "Invalid email or password" | Pass |
| TC-04 | Unauthorized Access | Non-logged-in user opening `admin_dashboard.php` | Blocked and redirected to `login.php?msg=login_required` | Pass |
| TC-05 | RBAC Violation | Student opening `admin_dashboard.php` | Blocked with `?msg=unauthorized` | Pass |
| TC-06 | Secure Logout | Click "Sign Out" | Session destroyed, cookies cleared, redirected to `login.php?msg=logged_out` | Pass |
