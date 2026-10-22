<?php
/**
 * Practical 11: Edit Student Record
 * StudentHub Portal - CHARUSAT ITUE203
 */
session_start();
require_once __DIR__ . '/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch existing record
$fetch_stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$fetch_stmt->bind_param("i", $id);
$fetch_stmt->execute();
$student = $fetch_stmt->get_result()->fetch_assoc();
$fetch_stmt->close();

if (!$student) {
    $_SESSION['flash_msg'] = "Student record not found.";
    $_SESSION['flash_type'] = "danger";
    header("Location: index.php");
    exit();
}

$error = '';
$name = $student['name'];
$email = $student['email'];
$course = $student['course'];
$year = $student['year'];
$phone = $student['phone'];
$status = $student['status'];

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
        // Check duplicate email on other students
        $check_stmt = $conn->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
        $check_stmt->bind_param("si", $email, $id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "The email '$email' is already taken by another student.";
            $check_stmt->close();
        } else {
            $check_stmt->close();

            // Update student record using MySQLi Prepared Statement
            $update_stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, course = ?, year = ?, phone = ?, status = ? WHERE id = ?");
            $update_stmt->bind_param("sssissi", $name, $email, $course, $year, $phone, $status, $id);

            if ($update_stmt->execute()) {
                $update_stmt->close();

                // Audit log entry
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $action = "EDIT_STUDENT";
                $details = "Updated student record #$id ($name).";
                $log_stmt = $conn->prepare("INSERT INTO audit_logs (action, details, ip_address) VALUES (?, ?, ?)");
                if ($log_stmt) {
                    $log_stmt->bind_param("sss", $action, $details, $ip);
                    $log_stmt->execute();
                    $log_stmt->close();
                }

                $_SESSION['flash_msg'] = "Student '$name' was updated successfully!";
                $_SESSION['flash_type'] = "success";
                header("Location: index.php");
                exit();
            } else {
                $error = "Database update failed: " . $conn->error;
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
    <title>StudentHub - Edit Student #<?= $id ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="header-container">
            <div class="brand">
                <span class="logo-badge">👥</span>
                <div>
                    <h1>StudentHub Portal</h1>
                    <p>Practical 11: Edit Student #<?= $id ?></p>
                </div>
            </div>
            <nav>
                <a href="index.php">&larr; Back to Student List</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="card form-card">
            <h2>Edit Student Details</h2>
            <p class="subtitle">Modify academic, contact, or enrollment status information.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit_student.php?id=<?= $id ?>">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="course">Course *</label>
                        <select id="course" name="course" required>
                            <option value="B.Tech IT" <?= $course === 'B.Tech IT' ? 'selected' : '' ?>>B.Tech IT</option>
                            <option value="B.Tech CSE" <?= $course === 'B.Tech CSE' ? 'selected' : '' ?>>B.Tech CSE</option>
                            <option value="BCA" <?= $course === 'BCA' ? 'selected' : '' ?>>BCA</option>
                            <option value="MCA" <?= $course === 'MCA' ? 'selected' : '' ?>>MCA</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="year">Year *</label>
                        <select id="year" name="year" required>
                            <option value="1" <?= (int)$year === 1 ? 'selected' : '' ?>>Year 1</option>
                            <option value="2" <?= (int)$year === 2 ? 'selected' : '' ?>>Year 2</option>
                            <option value="3" <?= (int)$year === 3 ? 'selected' : '' ?>>Year 3</option>
                            <option value="4" <?= (int)$year === 4 ? 'selected' : '' ?>>Year 4</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Enrollment Status</label>
                        <select id="status" name="status">
                            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive (Soft-Deleted)</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Student</button>
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
