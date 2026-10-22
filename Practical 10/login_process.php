<?php
/**
 * Practical 10: Secure Login Processing Script
 * StudentHub Portal - CHARUSAT ITUE203
 */
require_once __DIR__ . '/session_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

// 1. Verify CSRF Token
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    $_SESSION['login_error'] = "Security error: Invalid CSRF token. Please try again.";
    header("Location: login.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = "Please provide both email address and password.";
    header("Location: login.php");
    exit();
}

// 2. Fetch User by Email using Prepared Statement
$stmt = $conn->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ? LIMIT 1");
if (!$stmt) {
    $_SESSION['login_error'] = "Database error: " . $conn->error;
    header("Location: login.php");
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    // Avoid user enumeration by using generic error message
    $_SESSION['login_error'] = "Invalid email or password.";
    $stmt->close();
    header("Location: login.php");
    exit();
}

$user = $res->fetch_assoc();
$stmt->close();

// 3. Verify Password using password_verify()
if (!password_verify($password, $user['password'])) {
    $_SESSION['login_error'] = "Invalid email or password.";
    header("Location: login.php");
    exit();
}

// 4. Verify Account Status
if ($user['status'] !== 'active') {
    $_SESSION['login_error'] = "Your account has been deactivated. Please contact the administrator.";
    header("Location: login.php");
    exit();
}

// 5. Regenerate Session ID to Prevent Session Fixation Attacks
session_regenerate_id(true);

// 6. Set Session Variables
$_SESSION['user_id'] = $user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['last_activity'] = time();

// 7. Update Last-Login Timestamp (Intermediate Extension)
$update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
if ($update_stmt) {
    $update_stmt->bind_param("i", $user['id']);
    $update_stmt->execute();
    $update_stmt->close();
}

// 8. Remember-Me Token Generation (Advanced Extension)
if ($remember_me) {
    $raw_token = bin2hex(random_bytes(32));
    $hashed_token = hash('sha256', $raw_token);

    $rem_stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
    if ($rem_stmt) {
        $rem_stmt->bind_param("si", $hashed_token, $user['id']);
        $rem_stmt->execute();
        $rem_stmt->close();

        // 30 days expiration (86400 * 30), HttpOnly cookie
        setcookie('studenthub_remember', $raw_token, [
            'expires'  => time() + (86400 * 30),
            'path'     => '/',
            'domain'   => '',
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}

// 9. Record Login in Audit Logs
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$action = "USER_LOGIN";
$details = "User " . $user['name'] . " (" . $user['role'] . ") logged in successfully.";
$log_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
if ($log_stmt) {
    $log_stmt->bind_param("isss", $user['id'], $action, $details, $ip);
    $log_stmt->execute();
    $log_stmt->close();
}

// 10. Role-Based Redirection
if ($user['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
} else {
    header("Location: student_dashboard.php");
    exit();
}
?>
