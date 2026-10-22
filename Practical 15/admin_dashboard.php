<?php
/**
 * Practical 15: Admin Dashboard & System Control
 * StudentHub Full-Stack Portal
 */
require_once __DIR__ . '/config/auth.php';
require_admin();

$page_title = "Admin Dashboard";
require_once __DIR__ . '/includes/header.php';

// Dynamic KPI counts
$cnt_students = $conn->query("SELECT COUNT(*) FROM students WHERE status='active'")->fetch_row()[0] ?? 0;
$cnt_events = $conn->query("SELECT COUNT(*) FROM events")->fetch_row()[0] ?? 0;
$cnt_regs = $conn->query("SELECT COUNT(*) FROM registrations")->fetch_row()[0] ?? 0;
$cnt_users = $conn->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetch_row()[0] ?? 0;

// Monthly registrations chart data
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
    while ($r = $chart_res->fetch_assoc()) {
        $chart_data[] = $r;
        if ($r['reg_count'] > $max_reg) $max_reg = $r['reg_count'];
    }
}

// Recent Registrations
$recent_regs = $conn->query("
    SELECT r.id, s.name as student_name, s.course, e.title as event_title, r.registration_date 
    FROM registrations r 
    JOIN students s ON r.student_id = s.id 
    JOIN events e ON r.event_id = e.id 
    ORDER BY r.registration_date DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Recent Audit Logs
$recent_logs = $conn->query("
    SELECT a.id, COALESCE(u.name, 'System') as user_name, a.action, a.details, a.ip_address, a.created_at 
    FROM audit_logs a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="content-container">
    <div class="admin-profile-banner">
        <div>
            <h2>Administrator Command Center</h2>
            <p>Welcome back, <strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($user['email']) ?>)</p>
        </div>
        <div class="admin-badge">
            <span>🛡️ SUPER ADMIN</span>
        </div>
    </div>

    <!-- Quick Navigation to Integrated CRUD Modules -->
    <div class="module-cards" style="margin-top: 24px;">
        <div class="module-item">
            <h4>👨‍🎓 Student Management (CRUD)</h4>
            <p>Full student directory, search, course filter, pagination, soft delete, and CSV exports.</p>
            <a href="manage_students.php" class="module-link">Open Student Directory &rarr;</a>
        </div>
        <div class="module-item">
            <h4>🎪 Event Management (CRUD)</h4>
            <p>Publish campus events, manage dates, and upload poster artwork with MIME verification.</p>
            <a href="manage_events.php" class="module-link">Open Event Manager &rarr;</a>
        </div>
        <div class="module-item">
            <h4>⚡ Asynchronous AJAX Portal</h4>
            <p>Interactive single-page event manager consuming RESTful PHP JSON API via fetch().</p>
            <a href="ajax_events.php" class="module-link">Launch AJAX SPA &rarr;</a>
        </div>
        <div class="module-item">
            <h4>📜 System Audit Trail</h4>
            <p>Inspect security incidents, student logins, deletions, and administrative actions.</p>
            <a href="audit_logs.php" class="module-link">View Full Audit Log &rarr;</a>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="stats-grid" style="margin-top: 24px;">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;">👨‍🎓</div>
            <div>
                <div class="stat-value"><?= number_format($cnt_students) ?></div>
                <div class="stat-label">Active Students</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fef3c7; color: #d97706;">🎪</div>
            <div>
                <div class="stat-value"><?= number_format($cnt_events) ?></div>
                <div class="stat-label">Campus Events</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #d1fae5; color: #059669;">📝</div>
            <div>
                <div class="stat-value"><?= number_format($cnt_regs) ?></div>
                <div class="stat-label">Total Registrations</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">🔐</div>
            <div>
                <div class="stat-value"><?= number_format($cnt_users) ?></div>
                <div class="stat-label">Active Accounts</div>
            </div>
        </div>
    </div>

    <div class="grid-2col" style="margin-top: 24px;">
        <!-- Monthly Registrations Chart -->
        <div class="card">
            <div class="card-header-row">
                <h3>📈 Registrations Distribution</h3>
                <span class="badge-subtle">Aggregated Metric</span>
            </div>
            <div class="chart-container">
                <?php if (!empty($chart_data)): ?>
                    <div class="bar-chart">
                        <?php foreach ($chart_data as $cd): ?>
                            <?php $pct = round(($cd['reg_count'] / $max_reg) * 100); ?>
                            <div class="bar-group">
                                <div class="bar-wrapper">
                                    <div class="bar" style="height: <?= max(12, $pct) ?>%;">
                                        <span class="bar-tooltip"><?= (int)$cd['reg_count'] ?> enrollments</span>
                                    </div>
                                </div>
                                <div class="bar-label"><?= htmlspecialchars($cd['month_label']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #94a3b8; padding: 20px;">No registration history found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Latest Event Registrations -->
        <div class="card">
            <div class="card-header-row">
                <h3>⚡ Latest Event Registrations</h3>
                <a href="events.php" class="view-link">View All &rarr;</a>
            </div>
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Event</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_regs)): ?>
                        <?php foreach ($recent_regs as $r): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($r['student_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($r['course']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($r['event_title']) ?></td>
                                <td><small><?= date('M d, h:i A', strtotime($r['registration_date'])) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align:center; color:#94a3b8;">No registrations yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Audit Logs -->
    <div class="card" style="margin-top: 24px;">
        <div class="card-header-row">
            <div>
                <h3>📜 Recent Administrative Audit Entries</h3>
                <p class="subtitle">Real-time ledger of system logins, modifications, and student record creation.</p>
            </div>
            <a href="audit_logs.php" class="btn btn-outline">Open Full Audit Trail &rarr;</a>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="70">Log ID</th>
                        <th width="160">Action</th>
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
                            <td><small><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
