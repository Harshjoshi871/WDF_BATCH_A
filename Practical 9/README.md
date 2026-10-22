# Practical 9: Secure User Registration with Database Insert, Duplicate Email Check, and Password Hashing

## 1. Problem Definition
Implement secure student user registration by inserting details into MySQL using MySQLi. Validate input on both frontend and backend, check for duplicate email/username, hash passwords securely using `password_hash()`, and display descriptive success and error messages.

## 2. Key Questions & Viva Analysis
1. **Is `password_hash()` used before storing passwords?**
   - Yes, `password_hash($password, PASSWORD_BCRYPT)` is utilized to generate a secure 60-character cryptographic hash with automatic salt generation before storing the password in the `users` table. Plain-text passwords and legacy hashes (like MD5 or SHA1) are strictly avoided.
2. **Is duplicate email/username checked?**
   - Yes, before inserting the record, a parameterized query executes `SELECT id FROM users WHERE email = ?` to verify whether the email is already registered. If found, registration stops and informs the user.
3. **Are frontend and backend validations consistent?**
   - Yes, both frontend JavaScript (`register.js`) and backend PHP (`register_process.php`) apply identical rules:
     - Name: 3–50 alphabetic characters and spaces.
     - Email: RFC-compliant email address validation.
     - Phone: 10-digit Indian mobile numbers beginning with 6-9.
     - Password: Minimum 8 characters, at least 1 uppercase, 1 lowercase, 1 number, and 1 special symbol (`@#$!%*?&`).
     - Course & Year: Must belong to permitted domain values.

## 3. Extensions Implemented
- **Intermediate Extension (Prepared Statements):** Implemented parameterized prepared statements with `bind_param()` across duplicate checking, user insertion, student profile creation, and logging to prevent SQL injection vulnerabilities.
- **Advanced Extension (CSRF Protection & Audit Logs):**
  - Cryptographically secure CSRF token generated via `random_bytes(32)` in the session and verified using `hash_equals()`.
  - Registration event logged into the `audit_logs` table capturing `user_id`, `action = 'USER_REGISTER'`, IP address, and timestamp.

## 4. File Structure
- `register.php`: Accessible HTML5 registration form with real-time password strength meter and eye toggle.
- `register_process.php`: Backend processor with validation, duplicate checks, bcrypt hashing, transactions, and audit logging.
- `register.js`: Client-side validation script.
- `style.css`: StudentHub responsive layout and styling.
- `db.php`: Database connection wrapper.

## 5. Test Scenarios
| Test ID | Test Scenario | Input Data | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-01 | Valid Registration | Harsh Joshi, harsh.new@charusat.edu.in, 9876543210, IT, Year 2, `Student@123` | Form submits, record saved to DB, redirects to success alert | Pass |
| TC-02 | Duplicate Email Check | Harsh Joshi, `harsh@gmail.com` (Existing) | Returns error: "Email is already registered" | Pass |
| TC-03 | Weak Password Check | Password: `12345` | Frontend & backend block submission with password complexity error | Pass |
| TC-04 | Mismatched Passwords | Password: `Student@123`, Confirm: `Student@456` | Blocked with "Passwords do not match" | Pass |
| TC-05 | Invalid Phone Number | Phone: `12345` or `abcdef` | Blocked with 10-digit mobile number format error | Pass |
