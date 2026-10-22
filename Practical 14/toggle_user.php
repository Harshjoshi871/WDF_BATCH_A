<?php
/**
 * Practical 14: Toggle User Active/Inactive Status (Intermediate Extension)
 * StudentHub Portal - CHARUSAT ITUE203
 */
require_once __DIR__ . '/session_check.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Disallow admin from deactivating themselves
if ($id === intval($_SESSION['user_id'])) {
    $_SESSION['flash_msg'] = "Security restriction: You cannot deactivate your own active admin account.";
    $_SESSION['flash_type'] = "danger";
    header("Location: index.php");
    exit();
}

// Fetch current status
$stmt = $conn->prepare("SELECT name, email, status FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['flash_msg'] = "User account not found.";
    $_SESSION['flash_type'] = "danger";
    header("Location: index.php");
    exit();
}

$new_status = ($user['status'] === 'active') ? 'inactive' : 'active';

// Update status
$update_stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
$update_stmt->bind_param("si", $new_status, $id);

if ($update_stmt->execute()) {
    $update_stmt->close();

    // Audit log
    $admin_id = $_SESSION['user_id'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $action = "USER_STATUS_TOGGLE";
    $details = "Admin changed account status for " . $user['name'] . " (" . $user['email'] . ") to '$new_status'.";
    
    $log_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    if ($log_stmt) {
        $log_stmt->bind_param("isss", $admin_id, $action, $details, $ip);
        $log_stmt->execute();
        $log_stmt->close();
    }

    $_SESSION['flash_msg'] = "User '" . $user['name'] . "' status successfully changed to " . ucfirst($new_status) . ".";
    $_SESSION['flash_type'] = "success";
} else {
    $_SESSION['flash_msg'] = "Database error: " . $conn->error;
    $_SESSION['flash_type'] = "danger";
}

header("Location: index.php");
exit();
?>
