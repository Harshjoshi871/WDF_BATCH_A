<?php
/**
 * Practical 15: System Audit Trail
 * StudentHub Full-Stack Portal
 */
require_once __DIR__ . '/config/auth.php';
require_admin();

$page_title = "System Audit Trail";

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
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}

$sql = "
    SELECT a.id, COALESCE(u.name, 'System / Visitor') as user_name, COALESCE(u.email, 'system') as user_email, 
           a.action, a.details, a.ip_address, a.created_at 
    FROM audit_logs a 
    LEFT JOIN users u ON a.user_id = u.id 
    WHERE " . implode(" AND ", $where) . " 
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

$actions = $conn->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="content-container">
    <div class="toolbar">
        <div>
            <h1>Comprehensive Audit Trail</h1>
            <p>Chronological security and administrative ledger tracking authentication, user operations, and record changes.</p>
        </div>
        <a href="admin_dashboard.php" class="btn btn-outline">&larr; Return to Dashboard</a>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="audit_logs.php" class="filter-bar">
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

    <div class="card" style="padding: 0; overflow: hidden; margin-top: 20px;">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="70">ID</th>
                        <th width="160">Action</th>
                        <th>User</th>
                        <th>Operation Details</th>
                        <th width="120">IP Address</th>
                        <th width="160">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
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
                        <tr><td colspan="6" style="text-align: center; padding: 24px;">No audit records matching criteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
