<?php
/**
 * Practical 10: Admin Dashboard (Protected by RBAC)
 * StudentHub Portal - CHARUSAT ITUE203
 */
require_once __DIR__ . '/session_helper.php';
require_role('admin');

$user = get_logged_in_user();

// Fetch admin last login from DB
$stmt = $conn->prepare("SELECT last_login FROM users WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$db_user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Admin Dashboard (Practical 10)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="header-container">
            <div class="brand">
                <span class="logo-badge">🛡️</span>
                <div>
                    <h1>StudentHub - Admin Portal</h1>
                    <p>Role-Based Access Control Active</p>
                </div>
            </div>
            <nav>
                <a class="active" href="admin_dashboard.php">Admin Home</a>
                <a href="../Practical 11/index.php">Practical 11: Students</a>
                <a href="../Practical 12/index.php">Practical 12: Events</a>
                <a href="../Practical 13/index.html">Practical 13: AJAX CRUD</a>
                <a href="../Practical 14/index.php">Practical 14: Dashboard & Logs</a>
                <a href="logout.php" class="btn-logout-nav">Logout</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="card dashboard-card">
            <div class="dashboard-header">
                <div class="user-greeting">
                    <h2>Welcome, Administrator <?= htmlspecialchars($user['name']) ?>!</h2>
                    <span class="badge badge-admin">Role: <?= strtoupper(htmlspecialchars($user['role'])) ?></span>
                </div>
                <a href="logout.php" class="btn btn-danger">Sign Out</a>
            </div>

            <div class="session-info-box">
                <h3>🔒 Active Security & Session Context</h3>
                <div class="session-grid">
                    <div><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
                    <div><strong>Session ID:</strong> <code><?= htmlspecialchars(session_id()) ?></code></div>
                    <div><strong>Last Login Timestamp:</strong> <?= htmlspecialchars($db_user['last_login'] ?? 'Just now') ?></div>
                    <div><strong>Session Timeout:</strong> 15 minutes inactivity limit (Auto-refresh on click)</div>
                </div>
            </div>

            <h3 style="margin-top: 24px; margin-bottom: 12px; color: #374151;">Connected Modules (Practicals 11–15):</h3>
            <div class="module-cards">
                <div class="module-item">
                    <h4>Practical 11: Student CRUD</h4>
                    <p>Manage 20+ student profiles with search, filter, CSV export, and soft delete.</p>
                    <a href="../Practical 11/index.php" class="module-link">Open Student CRUD &rarr;</a>
                </div>
                <div class="module-item">
                    <h4>Practical 12: Event CRUD & Posters</h4>
                    <p>Create and edit college events with MIME-validated poster uploads.</p>
                    <a href="../Practical 12/index.php" class="module-link">Open Event CRUD &rarr;</a>
                </div>
                <div class="module-item">
                    <h4>Practical 13: Asynchronous AJAX CRUD</h4>
                    <p>RESTful JSON API with zero-reload dynamic Fetch API integration.</p>
                    <a href="../Practical 13/index.html" class="module-link">Open AJAX Portal &rarr;</a>
                </div>
                <div class="module-item">
                    <h4>Practical 14: Admin Analytics & Audit Logs</h4>
                    <p>Aggregation cards, registration charts, active user toggles, and audit trails.</p>
                    <a href="../Practical 14/index.php" class="module-link">Open Admin Analytics &rarr;</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>© 2026 StudentHub Portal | CHARUSAT - ITUE203 Web Development Frameworks</p>
    </footer>

</body>
</html>
