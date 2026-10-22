<?php
/**
 * Practical 9: Secure Registration Processing Script
 * StudentHub Portal - CHARUSAT ITUE203
 */
session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

// 1. CSRF Token Validation (Advanced Extension)
$posted_token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $posted_token)) {
    $_SESSION['reg_error'] = "Security error: Invalid or expired CSRF token. Please try again.";
    header("Location: register.php");
    exit();
}

// 2. Retrieve & Sanitize Inputs
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$course = trim($_POST['course'] ?? '');
$year = intval($_POST['year'] ?? 0);
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$terms = isset($_POST['terms']);

// Save old input for UX convenience (excluding passwords)
$_SESSION['old_input'] = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'course' => $course,
    'year' => $year
];

// 3. Server-side Validation
$errors = [];

if (empty($name) || !preg_match("/^[A-Za-z\s]{3,50}$/", $name)) {
    $errors[] = "Full Name must be between 3 and 50 characters and contain only letters.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please provide a valid email address.";
}

if (!preg_match("/^[6-9]\d{9}$/", $phone)) {
    $errors[] = "Phone number must be a valid 10-digit Indian mobile number starting with 6-9.";
}

$valid_courses = ['B.Tech IT', 'B.Tech CSE', 'BCA', 'MCA'];
if (!in_array($course, $valid_courses, true)) {
    $errors[] = "Please select a valid course from the list.";
}

if ($year < 1 || $year > 4) {
    $errors[] = "Please select a valid academic year (1-4).";
}

$password_pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$!%*?&])[A-Za-z\d@#$!%*?&]{8,}$/";
if (!preg_match($password_pattern, $password)) {
    $errors[] = "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one digit, and one special character.";
}

if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

if (!$terms) {
    $errors[] = "You must accept the Terms and Conditions to register.";
}

// If validation errors exist, bounce back immediately
if (!empty($errors)) {
    $_SESSION['reg_error'] = implode("<br>", $errors);
    header("Location: register.php");
    exit();
}

// 4. Duplicate Email / Username Check via MySQLi Prepared Statement
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
if (!$check_stmt) {
    $_SESSION['reg_error'] = "Database query preparation failed: " . $conn->error;
    header("Location: register.php");
    exit();
}

$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    $check_stmt->close();
    $_SESSION['reg_error'] = "The email address '<strong>" . htmlspecialchars($email) . "</strong>' is already registered. Please login or use another email.";
    header("Location: register.php");
    exit();
}
$check_stmt->close();

// 5. Password Hashing using password_hash() with BCRYPT
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// 6. Transactional Database Insert (Prepared Statements)
$conn->begin_transaction();

try {
    // A. Insert into users table
    $role = 'student';
    $status = 'active';
    $user_stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
    $user_stmt->bind_param("sssss", $name, $email, $hashed_password, $role, $status);
    $user_stmt->execute();
    $user_id = $conn->insert_id;
    $user_stmt->close();

    // B. Insert into students table
    $stu_stmt = $conn->prepare("INSERT INTO students (user_id, name, email, course, year, phone, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stu_stmt->bind_param("isssiss", $user_id, $name, $email, $course, $year, $phone, $status);
    $stu_stmt->execute();
    $stu_stmt->close();

    // C. Insert into audit_logs table (Advanced Extension)
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $action = "USER_REGISTER";
    $details = "Registered new student account for $name ($email) in $course Year $year.";
    $log_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $log_stmt->bind_param("isss", $user_id, $action, $details, $ip_address);
    $log_stmt->execute();
    $log_stmt->close();

    // Commit Transaction
    $conn->commit();

    // Clear old inputs and set success message
    unset($_SESSION['old_input']);
    $_SESSION['reg_success'] = "Account successfully registered for " . htmlspecialchars($name) . "! You can now log in with your credentials.";
    header("Location: register.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['reg_error'] = "Registration failed due to a server error: " . $e->getMessage();
    header("Location: register.php");
    exit();
}
?>
