<?php
/**
 * Practical 15: Event Registration Action Handler
 * StudentHub Full-Stack Portal
 */
require_once __DIR__ . '/config/auth.php';
require_student();

$event_id = intval($_GET['event_id'] ?? 0);
if ($event_id <= 0) {
    header("Location: events.php");
    exit();
}

$user = current_user();

// 1. Fetch student ID
$s_stmt = $conn->prepare("SELECT id, name FROM students WHERE email = ? LIMIT 1");
$s_stmt->bind_param("s", $user['email']);
$s_stmt->execute();
$student = $s_stmt->get_result()->fetch_assoc();
$s_stmt->close();

if (!$student) {
    $_SESSION['flash_msg'] = "Student academic profile record not found. Please contact administration.";
    $_SESSION['flash_type'] = "danger";
    header("Location: events.php");
    exit();
}

$student_id = $student['id'];

// 2. Fetch Event Details and check if open
$e_stmt = $conn->prepare("SELECT title, status FROM events WHERE id = ? LIMIT 1");
$e_stmt->bind_param("i", $event_id);
$e_stmt->execute();
$event = $e_stmt->get_result()->fetch_assoc();
$e_stmt->close();

if (!$event) {
    $_SESSION['flash_msg'] = "Event not found.";
    $_SESSION['flash_type'] = "danger";
    header("Location: events.php");
    exit();
}

if ($event['status'] !== 'open') {
    $_SESSION['flash_msg'] = "Sorry, registrations for '" . $event['title'] . "' are currently closed.";
    $_SESSION['flash_type'] = "warning";
    header("Location: events.php");
    exit();
}

// 3. Prevent Duplicate Registration
$dup_stmt = $conn->prepare("SELECT id FROM registrations WHERE student_id = ? AND event_id = ?");
$dup_stmt->bind_param("ii", $student_id, $event_id);
$dup_stmt->execute();
$dup_stmt->store_result();

if ($dup_stmt->num_rows > 0) {
    $dup_stmt->close();
    $_SESSION['flash_msg'] = "You are already registered for '" . $event['title'] . "'.";
    $_SESSION['flash_type'] = "info";
    header("Location: events.php");
    exit();
}
$dup_stmt->close();

// 4. Insert Registration Record
$reg_stmt = $conn->prepare("INSERT INTO registrations (student_id, event_id) VALUES (?, ?)");
$reg_stmt->bind_param("ii", $student_id, $event_id);

if ($reg_stmt->execute()) {
    $reg_stmt->close();

    // Log Action
    log_audit_action('EVENT_REGISTER', "Student {$student['name']} registered for event '{$event['title']}' (#$event_id).");

    $_SESSION['flash_msg'] = "Congratulations! You have successfully registered for '" . $event['title'] . "'.";
    $_SESSION['flash_type'] = "success";
    header("Location: student_dashboard.php");
    exit();
} else {
    $_SESSION['flash_msg'] = "Registration failed due to a database error: " . $conn->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: events.php");
    exit();
}
?>
