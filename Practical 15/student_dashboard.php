<?php
/**
 * Practical 15: Student Dashboard
 * StudentHub Full-Stack Portal
 */
require_once __DIR__ . '/config/auth.php';
require_student();

$page_title = "My Student Dashboard";
require_once __DIR__ . '/includes/header.php';

// Handle cancel registration action
if (isset($_GET['cancel_event_id'])) {
    $cancel_id = intval($_GET['cancel_event_id']);
    
    // Find student ID
    $s_stmt = $conn->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
    $s_stmt->bind_param("s", $user['email']);
    $s_stmt->execute();
    $s_row = $s_stmt->get_result()->fetch_assoc();
    $s_stmt->close();

    if ($s_row) {
        $del_reg = $conn->prepare("DELETE FROM registrations WHERE student_id = ? AND event_id = ?");
        $del_reg->bind_param("ii", $s_row['id'], $cancel_id);
        $del_reg->execute();
        $del_reg->close();

        log_audit_action('CANCEL_EVENT_REGISTRATION', "Student cancelled registration for event #$cancel_id.");
        $_SESSION['flash_msg'] = "Registration cancelled successfully.";
        $_SESSION['flash_type'] = "warning";
        header("Location: student_dashboard.php");
        exit();
    }
}

// Fetch student profile data
$s_stmt = $conn->prepare("SELECT * FROM students WHERE email = ? LIMIT 1");
$s_stmt->bind_param("s", $user['email']);
$s_stmt->execute();
$student = $s_stmt->get_result()->fetch_assoc();
$s_stmt->close();

$student_id = $student['id'] ?? 0;

// Fetch registered events
$my_events = [];
if ($student_id > 0) {
    $e_sql = "
        SELECT e.id, e.title, e.category, e.event_date, e.poster, r.registration_date 
        FROM registrations r 
        JOIN events e ON r.event_id = e.id 
        WHERE r.student_id = ? 
        ORDER BY e.event_date ASC
    ";
    $e_stmt = $conn->prepare($e_sql);
    $e_stmt->bind_param("i", $student_id);
    $e_stmt->execute();
    $my_events = $e_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $e_stmt->close();
}

$flash = $_SESSION['flash_msg'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? 'info';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
?>

<div class="content-container">
    <div class="admin-profile-banner" style="background: linear-gradient(135deg, #4338ca 0%, #1e1b4b 100%);">
        <div>
            <h2>Hello, <?= htmlspecialchars($user['name']) ?>!</h2>
            <p>Institutional ID: <strong>26IT<?= str_pad((string)$student_id, 3, '0', STR_PAD_LEFT) ?></strong> | Degree: <?= htmlspecialchars($student['course'] ?? 'Undergraduate') ?> Year <?= (int)($student['year'] ?? 1) ?></p>
        </div>
        <div class="admin-badge">
            <span style="background: rgba(255,255,255,0.25);">🎓 STUDENT PROFILE</span>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flash_type) ?>">
            <?= htmlspecialchars($flash) ?>
        </div>
    <?php endif; ?>

    <div class="grid-2col" style="margin-top: 24px;">
        <div class="card">
            <h3>📋 My Academic Profile</h3>
            <div style="margin-top: 16px; font-size: 14px; line-height: 2;">
                <div><strong>Full Name:</strong> <?= htmlspecialchars($user['name']) ?></div>
                <div><strong>Registered Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
                <div><strong>Degree Program:</strong> <?= htmlspecialchars($student['course'] ?? 'N/A') ?></div>
                <div><strong>Current Year:</strong> Year <?= (int)($student['year'] ?? 1) ?></div>
                <div><strong>Contact Number:</strong> <?= htmlspecialchars($student['phone'] ?? 'N/A') ?></div>
                <div><strong>Profile Status:</strong> <span class="status-pill status-active">Active Enrollment</span></div>
            </div>
        </div>

        <div class="card">
            <h3>⚡ Quick Actions</h3>
            <div class="quick-links-list" style="margin-top: 16px; display: flex; flex-direction: column; gap: 10px;">
                <a href="events.php" class="btn btn-primary">🎪 Browse Campus Events & Symposia</a>
                <a href="ajax_events.php" class="btn btn-secondary">⚡ Explore Asynchronous AJAX Hub</a>
                <a href="timetable.php" class="btn btn-outline">📅 View Class Schedule Timetable</a>
                <a href="notices.php" class="btn btn-outline">📢 Read Recent Examination Notices</a>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 24px;">
        <div class="card-header-row">
            <h3>🎪 My Enrolled Campus Events</h3>
            <span class="badge badge-subtle"><?= count($my_events) ?> Events Enrolled</span>
        </div>

        <?php if (!empty($my_events)): ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Category</th>
                            <th>Event Date</th>
                            <th>Enrolled On</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_events as $ev): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($ev['title']) ?></strong></td>
                                <td><span class="category-pill"><?= htmlspecialchars($ev['category']) ?></span></td>
                                <td><?= date('D, d M Y', strtotime($ev['event_date'])) ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($ev['registration_date'])) ?></td>
                                <td>
                                    <a href="student_dashboard.php?cancel_event_id=<?= (int)$ev['id'] ?>" 
                                       class="btn-sm btn-delete"
                                       onclick="return confirm('Cancel registration for <?= htmlspecialchars(addslashes($ev['title'])) ?>?');">
                                        Cancel
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>You have not registered for any upcoming events yet.</p>
                <a href="events.php" class="btn btn-primary" style="margin-top: 10px;">Browse Available Events &rarr;</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
