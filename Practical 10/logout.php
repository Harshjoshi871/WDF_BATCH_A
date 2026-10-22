<?php
/**
 * Practical 10: Secure Logout Script
 * StudentHub Portal - CHARUSAT ITUE203
 */
require_once __DIR__ . '/session_helper.php';

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];

    // Invalidate persistent token in database
    $clear_token_stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    if ($clear_token_stmt) {
        $clear_token_stmt->bind_param("i", $uid);
        $clear_token_stmt->execute();
        $clear_token_stmt->close();
    }

    // Log logout event in audit logs
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $action = "USER_LOGOUT";
    $details = "User ID $uid logged out of session.";
    $log_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    if ($log_stmt) {
        $log_stmt->bind_param("isss", $uid, $action, $details, $ip);
        $log_stmt->execute();
        $log_stmt->close();
    }
}

// Expire remember-me cookie
if (isset($_COOKIE['studenthub_remember'])) {
    setcookie('studenthub_remember', '', time() - 3600, '/', '', false, true);
}

// Clear all session variables
$_SESSION = [];

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

header("Location: login.php?msg=logged_out");
exit();
?>
