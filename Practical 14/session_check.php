<?php
/**
 * Practical 14: RBAC Guard for Admin Dashboard
 * StudentHub Portal - CHARUSAT ITUE203
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Practical 10/login.php?msg=login_required");
    exit();
}

// Check if user has admin role
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../Practical 10/login.php?msg=unauthorized");
    exit();
}
?>
