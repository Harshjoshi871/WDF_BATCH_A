<?php
/**
 * Practical 11: Delete Student (Soft Delete & Permanent Delete)
 * StudentHub Portal - CHARUSAT ITUE203
 */
session_start();
require_once __DIR__ . '/db.php';

$id = intval($_GET['id'] ?? 0);
$type = $_GET['type'] ?? 'soft';

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Check student name
$name_stmt = $conn->prepare("SELECT name, status FROM students WHERE id = ?");
$name_stmt->bind_param("i", $id);
$name_stmt->execute();
$student = $name_stmt->get_result()->fetch_assoc();
$name_stmt->close();

if (!$student) {
    $_SESSION['flash_msg'] = "Student not found.";
    $_SESSION['flash_type'] = "danger";
    header("Location: index.php");
    exit();
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

if ($type === 'permanent') {
    // Permanent Hard Delete (DELETE statement)
    $del_stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $del_stmt->bind_param("i", $id);
    if ($del_stmt->execute()) {
        $del_stmt->close();

        // Audit Log
        $action = "STUDENT_PERMANENT_DELETE";
        $details = "Permanently deleted student record #$id (" . $student['name'] . ").";
        $log_stmt = $conn->prepare("INSERT INTO audit_logs (action, details, ip_address) VALUES (?, ?, ?)");
        if ($log_stmt) {
            $log_stmt->bind_param("sss", $action, $details, $ip);
            $log_stmt->execute();
            $log_stmt->close();
        }

        $_SESSION['flash_msg'] = "Student '" . $student['name'] . "' was permanently deleted.";
        $_SESSION['flash_type'] = "warning";
    } else {
        $_SESSION['flash_msg'] = "Could not delete student: " . $conn->error;
        $_SESSION['flash_type'] = "danger";
    }
} else {
    // Advanced Extension: Soft Delete using status column
    $new_status = ($student['status'] === 'active') ? 'inactive' : 'active';
    $soft_stmt = $conn->prepare("UPDATE students SET status = ? WHERE id = ?");
    $soft_stmt->bind_param("si", $new_status, $id);
    if ($soft_stmt->execute()) {
        $soft_stmt->close();

        // Audit Log
        $action = ($new_status === 'inactive') ? "STUDENT_SOFT_DELETE" : "STUDENT_REACTIVATE";
        $details = "Changed student #$id status to $new_status.";
        $log_stmt = $conn->prepare("INSERT INTO audit_logs (action, details, ip_address) VALUES (?, ?, ?)");
        if ($log_stmt) {
            $log_stmt->bind_param("sss", $action, $details, $ip);
            $log_stmt->execute();
            $log_stmt->close();
        }

        $msg_word = ($new_status === 'inactive') ? 'deactivated (Soft Deleted)' : 'reactivated';
        $_SESSION['flash_msg'] = "Student '" . $student['name'] . "' was successfully $msg_word.";
        $_SESSION['flash_type'] = "success";
    } else {
        $_SESSION['flash_msg'] = "Could not update status: " . $conn->error;
        $_SESSION['flash_type'] = "danger";
    }
}

header("Location: index.php");
exit();
?>
