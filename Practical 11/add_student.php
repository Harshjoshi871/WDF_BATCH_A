<?php
/**
 * Practical 11: Add New Student
 * StudentHub Portal - CHARUSAT ITUE203
 */
session_start();
require_once __DIR__ . '/db.php';

$error = '';
$name = '';
$email = '';
$course = '';
$year = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $phone = trim($_POST['phone'] ?? '');
    $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';

    if (empty($name) || strlen($name) < 3) {
        $error = "Student name must be at least 3 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!in_array($course, ['B.Tech IT', 'B.Tech CSE', 'BCA', 'MCA'])) {
        $error = "Please select a valid course.";
    } elseif ($year < 1 || $year > 4) {
        $error = "Please select an academic year (1-4).";
    } elseif (!preg_match("/^[6-9]\d{9}$/", $phone)) {
        $error = "Phone number must be a valid 10-digit mobile number.";
    } else {
        // Check duplicate email
        $check_stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "A student with email '$email' already exists in the system.";
            $check_stmt->close();
        } else {
            $check_stmt->close();

            // Insert student record using MySQLi Prepared Statement
            $insert_stmt = $conn->prepare("INSERT INTO students (name, email, course, year, phone, status) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssiss", $name, $email, $course, $year, $phone, $status);

            if ($insert_stmt->execute()) {
                $new_id = $conn->insert_id;
                $insert_stmt->close();

                // Audit log entry
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $action = "ADD_STUDENT";
                $details = "Created student record #$new_id for $name ($course).";
                $log_stmt = $conn->prepare("INSERT INTO audit_logs (action, details, ip_address) VALUES (?, ?, ?)");
                if ($log_stmt) {
                    $log_stmt->bind_param("sss", $action, $details, $ip);
                    $log_stmt->execute();
                    $log_stmt->close();
                }

                $_SESSION['flash_msg'] = "Student '$name' was added successfully!";
                $_SESSION['flash_type'] = "success";
                header("Location: index.php");
                exit();
            } else {
                $error = "Database insertion failed: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Add Student (Practical 11)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="header-container">
            <div class="brand">
                <span class="logo-badge">👥</span>
                <div>
                    <h1>StudentHub Portal</h1>
                    <p>Practical 11: Add Student</p>
                </div>
            </div>
            <nav>
                <a href="index.php">&larr; Back to Student List</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="card form-card">
            <h2>Add New Student Record</h2>
            <p class="subtitle">Enter student academic and contact details.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="add_student.php">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="e.g. Priyansh Dave" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="e.g. student@charusat.edu.in" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="course">Course *</label>
                        <select id="course" name="course" required>
                            <option value="">-- Select Course --</option>
                            <option value="B.Tech IT" <?= $course === 'B.Tech IT' ? 'selected' : '' ?>>B.Tech IT</option>
                            <option value="B.Tech CSE" <?= $course === 'B.Tech CSE' ? 'selected' : '' ?>>B.Tech CSE</option>
                            <option value="BCA" <?= $course === 'BCA' ? 'selected' : '' ?>>BCA</option>
                            <option value="MCA" <?= $course === 'MCA' ? 'selected' : '' ?>>MCA</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="year">Year *</label>
                        <select id="year" name="year" required>
                            <option value="">-- Select Year --</option>
                            <option value="1" <?= $year === 1 ? 'selected' : '' ?>>Year 1</option>
                            <option value="2" <?= $year === 2 ? 'selected' : '' ?>>Year 2</option>
                            <option value="3" <?= $year === 3 ? 'selected' : '' ?>>Year 3</option>
                            <option value="4" <?= $year === 4 ? 'selected' : '' ?>>Year 4</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="10-digit number" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Initial Status</label>
                        <select id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Student</button>
                    <a href="index.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>© 2026 StudentHub Portal | CHARUSAT - ITUE203 Web Development Frameworks</p>
    </footer>

</body>
</html>
