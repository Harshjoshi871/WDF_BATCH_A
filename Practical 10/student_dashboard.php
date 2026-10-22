<?php
/**
 * Practical 10: Student Dashboard (Protected by RBAC)
 * StudentHub Portal - CHARUSAT ITUE203
 */
require_once __DIR__ . '/session_helper.php';
require_role('student');

$user = get_logged_in_user();

// Fetch student details from students table
$stu_stmt = $conn->prepare("SELECT id, course, year, phone FROM students WHERE email = ? LIMIT 1");
$stu_stmt->bind_param("s", $user['email']);
$stu_stmt->execute();
$student_info = $stu_stmt->get_result()->fetch_assoc();
$stu_stmt->close();

$student_id = $student_info['id'] ?? 0;

// Fetch events registered by this student
$reg_events = [];
if ($student_id > 0) {
    $e_stmt = $conn->prepare("
        SELECT e.title, e.category, e.event_date, r.registration_date 
        FROM registrations r 
        JOIN events e ON r.event_id = e.id 
        WHERE r.student_id = ? 
        ORDER BY e.event_date ASC
    ");
    $e_stmt->bind_param("i", $student_id);
    $e_stmt->execute();
    $res = $e_stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $reg_events[] = $row;
    }
    $e_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Student Dashboard (Practical 10)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="header-container">
            <div class="brand">
                <span class="logo-badge">🎓</span>
                <div>
                    <h1>StudentHub - Student Portal</h1>
                    <p>CHARUSAT Academic Space</p>
                </div>
            </div>
            <nav>
                <a class="active" href="student_dashboard.php">My Profile</a>
                <a href="../Practical 12/index.php">Upcoming Events</a>
                <a href="../Practical 13/index.html">Event Explorer</a>
                <a href="logout.php" class="btn-logout-nav">Logout</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="card dashboard-card">
            <div class="dashboard-header">
                <div class="user-greeting">
                    <h2>Hello, <?= htmlspecialchars($user['name']) ?>!</h2>
                    <span class="badge badge-student">Role: STUDENT</span>
                </div>
                <a href="logout.php" class="btn btn-danger">Sign Out</a>
            </div>

            <div class="session-info-box">
                <h3>📋 Student Academic Information</h3>
                <div class="session-grid">
                    <div><strong>Registered Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
                    <div><strong>Course:</strong> <?= htmlspecialchars($student_info['course'] ?? 'Not specified') ?></div>
                    <div><strong>Year of Study:</strong> <?= htmlspecialchars((string)($student_info['year'] ?? 'N/A')) ?></div>
                    <div><strong>Contact Mobile:</strong> <?= htmlspecialchars($student_info['phone'] ?? 'N/A') ?></div>
                </div>
            </div>

            <h3 style="margin-top: 24px; margin-bottom: 12px; color: #374151;">My Registered College Events:</h3>
            <?php if (!empty($reg_events)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Category</th>
                            <th>Event Date</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reg_events as $event): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($event['title']) ?></strong></td>
                                <td><span class="category-badge"><?= htmlspecialchars($event['category']) ?></span></td>
                                <td><?= htmlspecialchars($event['event_date']) ?></td>
                                <td><?= htmlspecialchars(date('d M Y, h:i A', strtotime($event['registration_date']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">
                    You have not registered for any events yet. Check out the <a href="../Practical 12/index.php">Events section</a> or <a href="../Practical 13/index.html">AJAX Portal</a>!
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>© 2026 StudentHub Portal | CHARUSAT - ITUE203 Web Development Frameworks</p>
    </footer>

</body>
</html>
