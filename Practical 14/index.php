<?php
/**
 * Practical 14: Admin Dashboard with RBAC, Dynamic Menu, Analytics & Audit Log
 * StudentHub Portal - CHARUSAT ITUE203
 */
require_once __DIR__ . '/session_check.php';

// Flash Messages
$flash_msg = $_SESSION['flash_msg'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// 1. Dynamic SQL Aggregation Queries
$total_students = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0] ?? 0;
$total_events = $conn->query("SELECT COUNT(*) FROM events")->fetch_row()[0] ?? 0;
$total_registrations = $conn->query("SELECT COUNT(*) FROM registrations")->fetch_row()[0] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetch_row()[0] ?? 0;

// 2. Dynamic Menu Configuration based on Role
$role = $_SESSION['role'] ?? 'guest';
$menu_items = [];

if ($role === 'admin') {
    $menu_items = [
        ['label' => 'Dashboard Overview', 'url' => 'index.php', 'active' => true],
        ['label' => 'Student Management', 'url' => '../Practical 11/index.php', 'active' => false],
        ['label' => 'Event Management', 'url' => '../Practical 12/index.php', 'active' => false],
        ['label' => 'AJAX Event Portal', 'url' => '../Practical 13/index.html', 'active' => false],
        ['label' => 'Full Audit Trail', 'url' => 'audit_logs.php', 'active' => false],
        ['label' => 'Logout', 'url' => '../Practical 10/logout.php', 'active' => false, 'class' => 'nav-logout']
    ];
} else {
    $menu_items = [
        ['label' => 'Student Profile', 'url' => '../Practical 10/student_dashboard.php', 'active' => true],
        ['label' => 'Browse Events', 'url' => '../Practical 12/index.php', 'active' => false],
        ['label' => 'Logout', 'url' => '../Practical 10/logout.php', 'active' => false]
    ];
}

// 3. Advanced Extension: Event Registration Analytics by Month
$chart_sql = "
    SELECT DATE_FORMAT(registration_date, '%b %Y') as month_label, COUNT(*) as reg_count 
    FROM registrations 
    GROUP BY month_label, YEAR(registration_date), MONTH(registration_date) 
    ORDER BY YEAR(registration_date) ASC, MONTH(registration_date) ASC 
    LIMIT 6
";
$chart_res = $conn->query($chart_sql);
$chart_data = [];
$max_reg = 1;
if ($chart_res) {
    while ($row = $chart_res->fetch_assoc()) {
        $chart_data[] = $row;
        if ($row['reg_count'] > $max_reg) {
            $max_reg = $row['reg_count'];
        }
    }
}

// 4. Intermediate Extension: User Management & Status Toggle List
$users_res = $conn->query("SELECT id, name, email, role, status, last_login, created_at FROM users ORDER BY id ASC");
$users_list = $users_res ? $users_res->fetch_all(MYSQLI_ASSOC) : [];

// 5. Recent 5 Event Registrations with JOIN
$recent_reg_sql = "
    SELECT r.id, s.name as student_name, s.course, e.title as event_title, r.registration_date 
    FROM registrations r 
    JOIN students s ON r.student_id = s.id 
    JOIN events e ON r.event_id = e.id 
    ORDER BY r.registration_date DESC 
    LIMIT 5
";
$recent_regs = $conn->query($recent_reg_sql)->fetch_all(MYSQLI_ASSOC);

// 6. Recent 6 Audit Logs
$logs_sql = "
    SELECT a.id, COALESCE(u.name, 'System') as user_name, a.action, a.details, a.ip_address, a.created_at 
    FROM audit_logs a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 6
";
$recent_logs = $conn->query($logs_sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Practical 14: Admin Control Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="header-container">
            <div class="brand">
                <span class="logo-badge">📊</span>
                <div>
                    <h1>StudentHub Admin Command Center</h1>
                    <p>Practical 14: RBAC, SQL Analytics & Audit Logs</p>
                </div>
            </div>
            <!-- Dynamic Role-Based Navigation Menu -->
            <nav class="dynamic-nav">
                <?php foreach ($menu_items as $item): ?>
                    <a href="<?= htmlspecialchars($item['url']) ?>" 
                       class="<?= $item['active'] ? 'active' : '' ?> <?= htmlspecialchars($item['class'] ?? '') ?>">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="dashboard-container">

            <?php if (!empty($flash_msg)): ?>
                <div class="alert alert-<?= htmlspecialchars($flash_type) ?>">
                    <?= htmlspecialchars($flash_msg) ?>
                </div>
            <?php endif; ?>

            <div class="admin-profile-banner">
                <div>
                    <h2>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></h2>
                    <p>Logged in as <strong><?= htmlspecialchars($_SESSION['email'] ?? '') ?></strong> (Admin Role)</p>
                </div>
                <div class="admin-badge">
                    <span>👑 SUPER ADMIN</span>
                </div>
            </div>

            <!-- KPI Metric Cards (SQL Aggregation) -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;">👨‍🎓</div>
                    <div>
                        <div class="stat-value"><?= number_format($total_students) ?></div>
                        <div class="stat-label">Total Registered Students</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef3c7; color: #d97706;">🎪</div>
                    <div>
                        <div class="stat-value"><?= number_format($total_events) ?></div>
                        <div class="stat-label">Total Campus Events</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #d1fae5; color: #059669;">📝</div>
                    <div>
                        <div class="stat-value"><?= number_format($total_registrations) ?></div>
                        <div class="stat-label">Total Event Registrations</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">🔐</div>
                    <div>
                        <div class="stat-value"><?= number_format($total_users) ?></div>
                        <div class="stat-label">Active Portal Accounts</div>
                    </div>
                </div>
            </div>

            <div class="grid-2col">
                <!-- Advanced Extension: Monthly Event Registration Analytics Chart -->
                <div class="card">
                    <div class="card-header-row">
                        <h3>📈 Event Registrations by Month</h3>
                        <span class="badge-subtle">Aggregated Data</span>
                    </div>
                    <div class="chart-container">
                        <?php if (!empty($chart_data)): ?>
                            <div class="bar-chart">
                                <?php foreach ($chart_data as $cd): ?>
                                    <?php $pct = round(($cd['reg_count'] / $max_reg) * 100); ?>
                                    <div class="bar-group">
                                        <div class="bar-wrapper">
                                            <div class="bar" style="height: <?= max(12, $pct) ?>%;">
                                                <span class="bar-tooltip"><?= (int)$cd['reg_count'] ?> registrations</span>
                                            </div>
                                        </div>
                                        <div class="bar-label"><?= htmlspecialchars($cd['month_label']) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: #64748b; padding: 24px; text-align: center;">No registration activity found yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Registrations JOIN View -->
                <div class="card">
                    <div class="card-header-row">
                        <h3>⚡ Latest Event Enrollments</h3>
                        <a href="../Practical 12/index.php" class="view-link">View All Events &rarr;</a>
                    </div>
                    <table class="simple-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Event</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_regs) > 0): ?>
                                <?php foreach ($recent_regs as $reg): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($reg['student_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($reg['course']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($reg['event_title']) ?></td>
                                        <td><small><?= date('M d, h:i A', strtotime($reg['registration_date'])) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align:center; color:#94a3b8; padding: 18px;">No event enrollments recorded yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Intermediate Extension: User Management & Active/Inactive Status Toggle -->
            <div class="card" style="margin-top: 24px;">
                <div class="card-header-row">
                    <div>
                        <h3>👥 User Account Moderation & Status Toggle</h3>
                        <p class="subtitle">Enable or disable access permissions instantly with automatic audit logging.</p>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="60">ID</th>
                                <th>Name</th>
                                <th>Email Address</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th width="140">Status Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users_list as $u): ?>
                                <tr>
                                    <td>#<?= (int)$u['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <span class="role-pill role-<?= htmlspecialchars($u['role']) ?>">
                                            <?= strtoupper(htmlspecialchars($u['role'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-pill status-<?= htmlspecialchars($u['status']) ?>">
                                            <?= ucfirst(htmlspecialchars($u['status'])) ?>
                                        </span>
                                    </td>
                                    <td><small><?= $u['last_login'] ? date('M d, Y H:i', strtotime($u['last_login'])) : 'Never' ?></small></td>
                                    <td>
                                        <?php if ((int)$u['id'] === (int)$_SESSION['user_id']): ?>
                                            <span class="text-muted"><small>(Current User)</small></span>
                                        <?php else: ?>
                                            <a href="toggle_user.php?id=<?= (int)$u['id'] ?>" 
                                               class="btn-sm <?= $u['status'] === 'active' ? 'btn-deactivate' : 'btn-activate' ?>"
                                               onclick="return confirm('Toggle status for <?= htmlspecialchars(addslashes($u['name'])) ?>?');">
                                                <?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Audit Logs Section -->
            <div class="card" style="margin-top: 24px;">
                <div class="card-header-row">
                    <div>
                        <h3>📜 Security & Administrative Audit Trail</h3>
                        <p class="subtitle">Chronological ledger of logins, insertions, modifications, and status changes.</p>
                    </div>
                    <a href="audit_logs.php" class="btn btn-outline">View Complete Audit Log &rarr;</a>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="60">ID</th>
                                <th width="140">Action</th>
                                <th>User</th>
                                <th>Details</th>
                                <th width="120">IP Address</th>
                                <th width="150">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td>#<?= (int)$log['id'] ?></td>
                                    <td><span class="action-tag"><?= htmlspecialchars($log['action']) ?></span></td>
                                    <td><strong><?= htmlspecialchars($log['user_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($log['details']) ?></td>
                                    <td><code><?= htmlspecialchars($log['ip_address']) ?></code></td>
                                    <td><small><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <footer>
        <p>© 2026 StudentHub Portal | CHARUSAT - ITUE203 Web Development Frameworks</p>
    </footer>

</body>
</html>
