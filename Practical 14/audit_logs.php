<?php
/**
 * Practical 14: Comprehensive Audit Trail Viewer
 * StudentHub Portal - CHARUSAT ITUE203
 */
require_once __DIR__ . '/session_check.php';

$action_filter = trim($_GET['action'] ?? '');
$search = trim($_GET['search'] ?? '');

$where = ["1=1"];
$params = [];
$types = "";

if (!empty($action_filter)) {
    $where[] = "a.action = ?";
    $params[] = $action_filter;
    $types .= "s";
}

if (!empty($search)) {
    $where[] = "(a.details LIKE ? OR a.ip_address LIKE ? OR u.name LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

$where_clause = implode(" AND ", $where);
$sql = "
    SELECT a.id, COALESCE(u.name, 'System') as user_name, COALESCE(u.email, 'system@studenthub.com') as user_email, 
           a.action, a.details, a.ip_address, a.created_at 
    FROM audit_logs a 
    LEFT JOIN users u ON a.user_id = u.id 
    WHERE $where_clause 
    ORDER BY a.created_at DESC 
    LIMIT 100
";

$stmt = $conn->prepare($sql);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Distinct actions for dropdown
$actions_res = $conn->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC");
$actions = $actions_res ? $actions_res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Practical 14: Complete Audit Trail</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="header-container">
            <div class="brand">
                <span class="logo-badge">📜</span>
                <div>
                    <h1>StudentHub - Audit Log Explorer</h1>
                    <p>Practical 14: Administrative Action Traceability</p>
                </div>
            </div>
            <nav class="dynamic-nav">
                <a href="index.php">&larr; Return to Dashboard</a>
                <a class="active" href="audit_logs.php">Audit Logs</a>
                <a href="../Practical 10/logout.php" class="nav-logout">Logout</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="dashboard-container">
            <div class="card">
                <div class="card-header-row">
                    <div>
                        <h2>System Audit Log Trail</h2>
                        <p class="subtitle">Complete chronological record of all administrative operations, security occurrences, and profile updates.</p>
                    </div>
                </div>

                <form method="GET" action="audit_logs.php" class="filter-bar" style="margin-top: 10px;">
                    <div class="filter-group flex-2">
                        <label for="search">Search Details or IP</label>
                        <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search keyword, IP address, user...">
                    </div>

                    <div class="filter-group">
                        <label for="action">Action Filter</label>
                        <select id="action" name="action">
                            <option value="">All Actions</option>
                            <?php foreach ($actions as $act): ?>
                                <option value="<?= htmlspecialchars($act['action']) ?>" <?= $action_filter === $act['action'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($act['action']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="audit_logs.php" class="btn btn-outline">Reset</a>
                    </div>
                </form>

                <div class="table-container" style="margin-top: 20px;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="70">Log ID</th>
                                <th width="160">Action</th>
                                <th>Initiating User</th>
                                <th>Operational Details</th>
                                <th width="120">IP Address</th>
                                <th width="160">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($logs) > 0): ?>
                                <?php foreach ($logs as $l): ?>
                                    <tr>
                                        <td>#<?= (int)$l['id'] ?></td>
                                        <td><span class="action-tag"><?= htmlspecialchars($l['action']) ?></span></td>
                                        <td>
                                            <strong><?= htmlspecialchars($l['user_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($l['user_email']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($l['details']) ?></td>
                                        <td><code><?= htmlspecialchars($l['ip_address']) ?></code></td>
                                        <td><small><?= date('M d, Y H:i:s', strtotime($l['created_at'])) ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 32px; color: #64748b;">
                                        No audit log entries matching your criteria.
                                    </td>
                                </tr>
                            <?php endif; ?>
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
