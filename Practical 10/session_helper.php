<?php
/**
 * Practical 10: Centralized Session and RBAC Security Helper
 * StudentHub Portal - CHARUSAT ITUE203
 */

if (session_status() === PHP_SESSION_NONE) {
    // Enforce secure cookie attributes
    session_set_cookie_params([
        'lifetime' => 0,          // Expire when browser closes
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,       // Block JavaScript document.cookie access
        'samesite' => 'Lax'       // CSRF mitigation
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';

// Inactivity timeout configuration (15 minutes = 900 seconds)
define('SESSION_TIMEOUT_DURATION', 900);

/**
 * 1. Auto-login using Remember-Me Token (Advanced Extension)
 * Avoids raw tokens in database by verifying against a SHA-256 hash.
 */
if (!isset($_SESSION['user_id']) && isset($_COOKIE['studenthub_remember'])) {
    $raw_token = $_COOKIE['studenthub_remember'];
    $hashed_token = hash('sha256', $raw_token);

    $token_stmt = $conn->prepare("SELECT id, name, email, role, status FROM users WHERE remember_token = ? AND status = 'active' LIMIT 1");
    if ($token_stmt) {
        $token_stmt->bind_param("s", $hashed_token);
        $token_stmt->execute();
        $token_res = $token_stmt->get_result();

        if ($user = $token_res->fetch_assoc()) {
            // Regenerate session id to avoid fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();

            // Log auto-login event
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $act = 'USER_AUTO_LOGIN';
            $det = "User logged in automatically via persistent remember-me token.";
            $log_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
            if ($log_stmt) {
                $log_stmt->bind_param("isss", $user['id'], $act, $det, $ip);
                $log_stmt->execute();
                $log_stmt->close();
            }
        } else {
            // Invalidate stale or forged cookie
            setcookie('studenthub_remember', '', time() - 3600, '/', '', false, true);
        }
        $token_stmt->close();
    }
}

/**
 * 2. Session Inactivity Timeout Check
 */
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_DURATION)) {
        // Inactivity limit exceeded
        $expired_uid = $_SESSION['user_id'];
        
        // Log timeout in audit logs
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $act = 'SESSION_TIMEOUT';
        $det = "Session terminated automatically after " . (SESSION_TIMEOUT_DURATION / 60) . " minutes of inactivity.";
        $t_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        if ($t_stmt) {
            $t_stmt->bind_param("isss", $expired_uid, $act, $det, $ip);
            $t_stmt->execute();
            $t_stmt->close();
        }

        // Clean up session
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        header("Location: ../Practical 10/login.php?msg=timeout");
        exit();
    }

    // Refresh last activity timestamp on each request
    $_SESSION['last_activity'] = time();
}

/**
 * 3. Role-Based Access Control (RBAC) Enforcement Functions
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_logged_in_user() {
    if (!is_logged_in()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role'  => $_SESSION['role'] ?? 'student',
    ];
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: ../Practical 10/login.php?msg=login_required");
        exit();
    }
}

function require_role($required_role) {
    require_login();
    if ($_SESSION['role'] !== $required_role) {
        header("Location: ../Practical 10/login.php?msg=unauthorized");
        exit();
    }
}
?>
