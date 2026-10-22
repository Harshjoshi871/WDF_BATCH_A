<?php
/**
 * Practical 11: Student Management CRUD with Search, Filter & Pagination
 * StudentHub Portal - CHARUSAT ITUE203
 */
session_start();
require_once __DIR__ . '/db.php';

// Flash Messages
$flash_msg = $_SESSION['flash_msg'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// Filtering & Search Inputs
$search = trim($_GET['search'] ?? '');
$course_filter = trim($_GET['course'] ?? '');
$status_filter = trim($_GET['status'] ?? 'all');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 8;
$offset = ($page - 1) * $limit;

// Base WHERE conditions
$where = ["1=1"];
$params = [];
$types = "";

if (!empty($search)) {
    $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

if (!empty($course_filter)) {
    $where[] = "course = ?";
    $params[] = $course_filter;
    $types .= "s";
}

if ($status_filter === 'active' || $status_filter === 'inactive') {
    $where[] = "status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$where_clause = implode(" AND ", $where);

// 1. Get Total Count for Pagination
$count_sql = "SELECT COUNT(*) as total FROM students WHERE $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (!empty($types)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'] ?? 0;
$count_stmt->close();

$total_pages = max(1, ceil($total_records / $limit));
if ($page > $total_pages) $page = $total_pages;

// 2. Fetch Paginated Records
$data_sql = "SELECT id, name, email, course, year, phone, status FROM students WHERE $where_clause ORDER BY id DESC LIMIT ? OFFSET ?";
$data_stmt = $conn->prepare($data_sql);

$page_params = $params;
$page_params[] = $limit;
$page_params[] = $offset;
$page_types = $types . "ii";

$data_stmt->bind_param($page_types, ...$page_params);
$data_stmt->execute();
$students = $data_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$data_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Practical 11: Student Management CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="header-container">
            <div class="brand">
                <span class="logo-badge">👥</span>
                <div>
                    <h1>StudentHub Portal</h1>
                    <p>Practical 11: Student Management Module</p>
                </div>
            </div>
            <nav>
                <a href="../Practical 8/test_connection.php">Database</a>
                <a href="../Practical 9/register.php">Practical 9: Register</a>
                <a href="../Practical 10/login.php">Practical 10: Login</a>
                <a class="active" href="index.php">Practical 11: Students</a>
                <a href="../Practical 12/index.php">Practical 12: Events</a>
                <a href="../Practical 13/index.html">Practical 13: AJAX CRUD</a>
                <a href="../Practical 14/index.php">Practical 14: Dashboard</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="card list-card">
            <div class="toolbar">
                <div>
                    <h2>Student Records Management</h2>
                    <p>Add, edit, soft-delete, search, filter and export 20+ student profiles using MySQLi.</p>
                </div>
                <div class="toolbar-actions">
                    <a href="add_student.php" class="btn btn-primary">+ Add New Student</a>
                    <a href="export_csv.php?<?= http_build_query($_GET) ?>" class="btn btn-secondary">📥 Export to CSV</a>
                </div>
            </div>

            <?php if (!empty($flash_msg)): ?>
                <div class="alert alert-<?= htmlspecialchars($flash_type) ?>">
                    <?= htmlspecialchars($flash_msg) ?>
                </div>
            <?php endif; ?>

            <!-- Search and Filter Form -->
            <form method="GET" action="index.php" class="filter-bar">
                <div class="filter-group flex-2">
                    <label for="search">Search Keywords</label>
                    <input type="text" id="search" name="search" placeholder="Search by name, email, or phone..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="filter-group">
                    <label for="course">Course Filter</label>
                    <select id="course" name="course">
                        <option value="">All Courses</option>
                        <option value="B.Tech IT" <?= $course_filter === 'B.Tech IT' ? 'selected' : '' ?>>B.Tech IT</option>
                        <option value="B.Tech CSE" <?= $course_filter === 'B.Tech CSE' ? 'selected' : '' ?>>B.Tech CSE</option>
                        <option value="BCA" <?= $course_filter === 'BCA' ? 'selected' : '' ?>>BCA</option>
                        <option value="MCA" <?= $course_filter === 'MCA' ? 'selected' : '' ?>>MCA</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Status</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active Only</option>
                        <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
                    </select>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="index.php" class="btn btn-outline">Reset</a>
                </div>
            </form>

            <div class="meta-row">
                <span>Total records found: <strong><?= $total_records ?></strong></span>
                <span>Page <strong><?= $page ?></strong> of <strong><?= $total_pages ?></strong></span>
            </div>

            <!-- Student Data Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="60">ID</th>
                            <th>Student Name</th>
                            <th>Email Address</th>
                            <th>Course</th>
                            <th width="80">Year</th>
                            <th>Phone</th>
                            <th width="100">Status</th>
                            <th width="190">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($students) > 0): ?>
                            <?php foreach ($students as $stu): ?>
                                <tr>
                                    <td>#<?= (int)$stu['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($stu['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($stu['email']) ?></td>
                                    <td><span class="badge badge-course"><?= htmlspecialchars($stu['course']) ?></span></td>
                                    <td>Year <?= (int)$stu['year'] ?></td>
                                    <td><?= htmlspecialchars($stu['phone'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($stu['status'] === 'active'): ?>
                                            <span class="status-pill status-active">Active</span>
                                        <?php else: ?>
                                            <span class="status-pill status-inactive">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-cells">
                                        <a href="edit_student.php?id=<?= (int)$stu['id'] ?>" class="btn-sm btn-edit">Edit</a>
                                        <a href="delete_student.php?id=<?= (int)$stu['id'] ?>&type=soft" 
                                           class="btn-sm btn-warn" 
                                           onclick="return confirm('Change status to Inactive (Soft Delete) for <?= htmlspecialchars(addslashes($stu['name'])) ?>?');">
                                           <?= $stu['status'] === 'active' ? 'Deactivate' : 'Reactivate' ?>
                                        </a>
                                        <a href="delete_student.php?id=<?= (int)$stu['id'] ?>&type=permanent" 
                                           class="btn-sm btn-delete" 
                                           onclick="return confirm('Permanently delete student <?= htmlspecialchars(addslashes($stu['name'])) ?>? This cannot be undone.');">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 32px; color: #6b7280;">
                                    No students matching your search criteria. <a href="index.php">View all students</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">&laquo; First</a>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&lsaquo; Prev</a>
                    <?php endif; ?>

                    <?php for ($p = max(1, $page - 2); $p <= min($total_pages, $page + 2); $p++): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" 
                           class="<?= $p === $page ? 'active-page' : '' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next &rsaquo;</a>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>">Last &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>© 2026 StudentHub Portal | CHARUSAT - ITUE203 Web Development Frameworks</p>
    </footer>

</body>
</html>
