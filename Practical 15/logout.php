<?php
/**
 * Practical 15: Logout Script
 * StudentHub Full-Stack Portal
 */
require_once __DIR__ . '/config/auth.php';

if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $clear = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    if ($clear) {
        $clear->bind_param("i", $uid);
        $clear->execute();
        $clear->close();
    }
    log_audit_action('USER_LOGOUT', "User ID $uid logged out of session.");
}

if (isset($_COOKIE['studenthub_remember'])) {
    setcookie('studenthub_remember', '', time() - 3600, '/', '', false, true);
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}

session_destroy();

header("Location: login.php?msg=logged_out");
exit();
?>
