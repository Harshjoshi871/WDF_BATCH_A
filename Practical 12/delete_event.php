<?php
/**
 * Practical 12: Delete Event and Remove Poster File
 * StudentHub Portal - CHARUSAT ITUE203
 */
session_start();
require_once __DIR__ . '/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch poster file before deleting record
$stmt = $conn->prepare("SELECT title, poster FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    $_SESSION['flash_msg'] = "Event not found.";
    $_SESSION['flash_type'] = "danger";
    header("Location: index.php");
    exit();
}

// Delete from database
$del_stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$del_stmt->bind_param("i", $id);

if ($del_stmt->execute()) {
    $del_stmt->close();

    // Remove poster image from filesystem if exists
    if (!empty($event['poster']) && file_exists(__DIR__ . '/uploads/' . $event['poster'])) {
        unlink(__DIR__ . '/uploads/' . $event['poster']);
    }

    // Audit log entry
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $action = "DELETE_EVENT";
    $details = "Deleted event #$id: '" . $event['title'] . "'.";
    $log_stmt = $conn->prepare("INSERT INTO audit_logs (action, details, ip_address) VALUES (?, ?, ?)");
    if ($log_stmt) {
        $log_stmt->bind_param("sss", $action, $details, $ip);
        $log_stmt->execute();
        $log_stmt->close();
    }

    $_SESSION['flash_msg'] = "Event '" . $event['title'] . "' and its poster were successfully removed.";
    $_SESSION['flash_type'] = "warning";
} else {
    $_SESSION['flash_msg'] = "Failed to delete event: " . $conn->error;
    $_SESSION['flash_type'] = "danger";
}

header("Location: index.php");
exit();
?>
