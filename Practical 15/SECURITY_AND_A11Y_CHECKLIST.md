# Security and Accessibility (a11y) Verification Checklist

## 1. Web Security Audit Checklist
| Security Domain | Mitigation / Technique Implemented | File Reference | Verification Status |
|---|---|---|---|
| **SQL Injection (SQLi)** | Parameterized MySQLi prepared statements with `bind_param()` on all queries (`SELECT`, `INSERT`, `UPDATE`, `DELETE`). | `config/auth.php`, `manage_students.php`, `manage_events.php`, `api.php` | PASSED (100% Prepared Statements) |
| **Password Storage** | Bcrypt hashing via `password_hash($password, PASSWORD_BCRYPT)` with dynamic cryptographic salt generation. Verification via `password_verify()`. | `register.php`, `login.php`, `Practical 9/register_process.php` | PASSED (Zero Plaintext Passwords) |
| **Session Fixation** | Immediate invocation of `session_regenerate_id(true)` upon successful user credential verification. | `login.php`, `Practical 10/login_process.php` | PASSED (Old Session Destroyed) |
| **Cross-Site Request Forgery (CSRF)** | Synchronizer token generated with `bin2hex(random_bytes(32))` stored in session and validated with `hash_equals()`. | `login.php`, `register.php`, `Practical 9/register.php` | PASSED (CSRF Tokens Enforced) |
| **Cross-Site Scripting (XSS)** | All dynamic database values and query parameters escaped via `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`. | `includes/header.php`, `events.php`, `manage_students.php`, `timetable.php` | PASSED (Safe Output Encoding) |
| **Session Hijacking & Inactivity** | Enforced 15-minute inactivity session expiration (`SESSION_TIMEOUT_SECONDS = 900`). Automatic redirect to login. | `config/auth.php`, `Practical 10/session_helper.php` | PASSED (Inactivity Timeout Active) |
| **Cookie Security** | Session and Remember-Me cookies set with `httponly=true`, `samesite=Lax`, and conditional `secure` flags. | `config/auth.php`, `Practical 10/session_helper.php` | PASSED (Cookies Inaccessible to JS) |
| **Persistent Token Security** | Raw 32-byte tokens sent to user cookie, while only `hash('sha256', $token)` is saved in the database. | `config/auth.php`, `login.php` | PASSED (DB Breach Resistant) |
| **Malicious File Uploads** | 1) MIME-type inspection via `finfo_file()`. 2) Whitelisted extensions (`jpg`, `png`, `webp`). 3) 2MB size limit. 4) Unique cryptographic renaming (`poster_` + hex). 5) `.htaccess` script execution prevention. | `manage_events.php`, `uploads/.htaccess`, `Practical 12/add_event.php` | PASSED (Triple-Layer Upload Protection) |
| **Auditing & Traceability** | Dedicated `audit_logs` table records every critical administrative operation (login, register, create, edit, delete, status toggle) with client IP. | `config/auth.php`, `audit_logs.php` | PASSED (Persistent Action Logging) |

---

## 2. Accessibility (a11y) Verification Checklist (WCAG 2.1 AA)
| Accessibility Feature | Implementation Description | Verification Status |
|---|---|---|
| **Semantic HTML5 Landmarks** | Pages structured with `<header>`, `<nav>`, `<main id="main-content">`, `<section>`, `<article>`, and `<footer>`. | PASSED |
| **Skip to Main Content Link** | Accessible hidden link `<a href="#main-content" class="skip-link">` positioned at the top of every page for keyboard & screen-reader users. | PASSED |
| **Form Label Associations** | All `<input>`, `<select>`, and `<textarea>` elements paired with descriptive `<label for="...">` tags. | PASSED |
| **Keyboard Navigation** | All buttons, links, form inputs, and modal dialogues are reachable and operable via the `Tab` and `Enter/Space` keys. | PASSED |
| **Color Contrast Ratios** | Text elements achieve minimum contrast ratio of 4.5:1 against backgrounds (Indigo `#4f46e5` on white, Slate `#1e293b` on light gray). | PASSED |
| **Image Alt Attributes** | All informative images and event posters provide descriptive `alt` tags; decorative icons utilize appropriate text equivalents. | PASSED |
| **Responsive Viewports** | Mobile viewport meta tag enabled (`width=device-width, initial-scale=1.0`); responsive typography and fluid grid layouts adapt without horizontal scrolling. | PASSED |
