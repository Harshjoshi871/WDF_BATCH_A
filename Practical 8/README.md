# Practical 8: MySQL Schema Design, ER Model, and Database Connectivity

## 1. Problem Definition
Design the database schema for the StudentHub portal with normalized entities for students, events, registrations, users, and audit logs. Create secure PHP database connection files supporting both MySQLi and PDO with prepared statement capabilities and error handling.

## 2. Key Questions & Viva Analysis
1. **Is the schema normalized properly?**
   - Yes, the schema adheres to Third Normal Form (3NF). Student data, user authentication records, event information, and event registrations are isolated into separate relational tables to avoid transitive dependencies and redundant records.
2. **Are primary keys and foreign keys correctly defined?**
   - Yes:
     - `users.id` (Primary Key, Auto-increment)
     - `students.id` (Primary Key, Auto-increment), `students.user_id` (Foreign Key referencing `users.id`)
     - `events.id` (Primary Key, Auto-increment)
     - `registrations.id` (Primary Key, Auto-increment), `student_id` (FK referencing `students.id`), `event_id` (FK referencing `events.id`), with a unique composite key `(student_id, event_id)` preventing duplicate registrations.
     - `audit_logs.id` (Primary Key), `user_id` (FK referencing `users.id`).
3. **Is the database connection handled securely?**
   - Yes, database credentials are encapsulated in `db.php`, UTF-8 character encoding (`utf8mb4`) is enforced, error reporting is logged securely without leaking database details to public visitors, and prepared statements are enabled.

## 3. Files in Practical 8
- `studenthub_master.sql`: Complete DDL schema and seed data (25 students, 6 events, sample users with hashed passwords, and audit records).
- `studenthub.sql`: Original submitted SQL structure.
- `db.php`: Centralized dual connection handler supporting MySQLi (`$conn`) and PDO (`$pdo`).
- `test_connection.php`: Interactive visual page to verify database connection health, MariaDB version, and tables.
