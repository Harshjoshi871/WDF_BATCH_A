# Practical 11: Student Management CRUD with Search and Filter

## 1. Problem Definition
Develop a student management module enabling authorized users to add, view, edit, delete, search, and filter student records. All database interactions are executed through secure MySQLi prepared statements with explicit success/failure feedback messages.

## 2. Key Questions & Viva Analysis
1. **Are SELECT, INSERT, UPDATE, and DELETE operations working?**
   - **SELECT:** Paginated, multi-condition query (`SELECT id, name, email, course, year, phone, status FROM students WHERE ... LIMIT ? OFFSET ?`).
   - **INSERT:** Prepared statement (`INSERT INTO students (name, email, course, year, phone, status) VALUES (?, ?, ?, ?, ?, ?)`).
   - **UPDATE:** Parameterized statement (`UPDATE students SET name=?, email=?, course=?, year=?, phone=?, status=? WHERE id=?`).
   - **DELETE:** Two modes:
     - Permanent hard delete (`DELETE FROM students WHERE id = ?`).
     - Soft delete (`UPDATE students SET status = 'inactive' WHERE id = ?`).
2. **Is search by name/email/course implemented?**
   - Yes, dynamic SQL construction matches `(name LIKE ? OR email LIKE ? OR phone LIKE ?)` alongside exact equality filters on `course = ?` and `status = ?`.
3. **Are delete operations confirmed before execution?**
   - Yes, client-side JavaScript `onclick="return confirm('...');"` prevents accidental deletion by prompting for confirmation before navigation.

## 3. Extensions Implemented
- **Intermediate Extension (CSV Export):** `export_csv.php` streams student data matching current filter and search criteria using `fputcsv()` with UTF-8 BOM encoding for Excel compatibility.
- **Advanced Extension (Soft Delete via Status):** Students can be deactivated without losing historic registration records or cascade data, with a dedicated status pill (`Active` / `Inactive`).

## 4. Seed Dataset
The module is pre-populated with 25 realistic student records spanning CHARUSAT academic departments:
- B.Tech Information Technology (IT)
- B.Tech Computer Science and Engineering (CSE)
- Bachelor of Computer Applications (BCA)
- Master of Computer Applications (MCA)

## 5. Test Scenarios
| Test ID | Test Scenario | Steps | Expected Result | Pass/Fail |
|---|---|---|---|---|
| TC-01 | View & Pagination | Open `index.php` | Displays 8 students per page with pagination controls | Pass |
| TC-02 | Search Query | Enter "Harsh" or "Patel" in search | Filters table to matching student names or emails | Pass |
| TC-03 | Course Filter | Select "B.Tech IT" | Displays only students enrolled in B.Tech IT | Pass |
| TC-04 | Add Student | Click "Add New Student", enter details | Record created with prepared statement, flash banner shown | Pass |
| TC-05 | Edit Student | Click "Edit", modify course/year | Record updated in DB, success message displayed | Pass |
| TC-06 | Soft Delete | Click "Deactivate" | Student status toggled to `Inactive` | Pass |
| TC-07 | CSV Export | Click "Export to CSV" | Downloads `.csv` file matching current filter state | Pass |
