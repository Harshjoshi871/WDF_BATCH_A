<?php
/**
 * Practical 15: Authentication & Session Security Middleware
 * StudentHub Full-Stack Portal
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';

define('SESSION_TIMEOUT_SECONDS', 900); // 15 Minutes

// 1. Persistent Remember-Me Token Auto-Login
if (!isset($_SESSION['user_id']) && isset($_COOKIE['studenthub_remember'])) {
    $raw_token = $_COOKIE['studenthub_remember'];
    $hashed_token = hash('sha256', $raw_token);

    $stmt = $conn->prepare("SELECT id, name, email, role, status FROM users WHERE remember_token = ? AND status = 'active' LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $hashed_token);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user = $res->fetch_assoc()) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();

            log_audit_action('AUTO_LOGIN_REMEMBER_ME', "User {$user['name']} automatically logged in via token.");
        } else {
            setcookie('studenthub_remember', '', time() - 3600, '/', '', false, true);
        }
        $stmt->close();
    }
}

// 2. Session Inactivity Timeout Handler
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_SECONDS)) {
        $timed_out_uid = $_SESSION['user_id'];
        log_audit_action('SESSION_TIMEOUT', "Session expired due to 15m inactivity.");

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
        }
        session_destroy();

        header("Location: login.php?msg=timeout");
        exit();
    }
    $_SESSION['last_activity'] = time();
}

// 3. User & Role Verification Functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user() {
    if (!is_logged_in()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role'  => $_SESSION['role'] ?? 'student'
    ];
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php?msg=login_required");
        exit();
    }
}

function require_admin() {
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        header("Location: login.php?msg=unauthorized");
        exit();
    }
}

function require_student() {
    require_login();
    if (($_SESSION['role'] ?? '') !== 'student') {
        header("Location: admin_dashboard.php");
        exit();
    }
}

function log_audit_action($action, $details = '') {
    global $conn;
    $uid = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isss", $uid, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
    }
}
?>
