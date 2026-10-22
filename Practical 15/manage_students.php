<?php
/**
 * Practical 15: Student Management CRUD Module
 * StudentHub Full-Stack Portal
 */
require_once __DIR__ . '/config/auth.php';
require_admin();

$page_title = "Student Records Management";

// Handle Actions (Add, Edit, Delete)
$error = '';
$flash = '';

// Add / Edit submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $phone = trim($_POST['phone'] ?? '');
        $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';

        if (empty($name) || strlen($name) < 3) {
            $error = "Student name must be at least 3 characters.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email address.";
        } else {
            $chk = $conn->prepare("SELECT id FROM students WHERE email = ?");
            $chk->bind_param("s", $email);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $error = "A student with email '$email' already exists.";
            } else {
                $ins = $conn->prepare("INSERT INTO students (name, email, course, year, phone, status) VALUES (?, ?, ?, ?, ?, ?)");
                $ins->bind_param("sssiss", $name, $email, $course, $year, $phone, $status);
                if ($ins->execute()) {
                    log_audit_action('ADD_STUDENT', "Admin added student '$name' ($email).");
                    $_SESSION['flash_msg'] = "Student '$name' added successfully!";
                    $_SESSION['flash_type'] = "success";
                    header("Location: manage_students.php");
                    exit();
                } else {
                    $error = "Database insert error: " . $conn->error;
                }
            }
            $chk->close();
        }
    } elseif ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $phone = trim($_POST['phone'] ?? '');
        $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';

        if ($id > 0) {
            $up = $conn->prepare("UPDATE students SET name = ?, email = ?, course = ?, year = ?, phone = ?, status = ? WHERE id = ?");
            $up->bind_param("sssissi", $name, $email, $course, $year, $phone, $status, $id);
            if ($up->execute()) {
                log_audit_action('EDIT_STUDENT', "Admin updated student record #$id ($name).");
                $_SESSION['flash_msg'] = "Student record #$id updated successfully!";
                $_SESSION['flash_type'] = "success";
                header("Location: manage_students.php");
                exit();
            } else {
                $error = "Update failed: " . $conn->error;
            }
        }
    }
}

// Handle Delete (GET)
if (isset($_GET['del_id'])) {
    $del_id = intval($_GET['del_id']);
    $del_type = $_GET['type'] ?? 'soft';

    if ($del_id > 0) {
        if ($del_type === 'permanent') {
            $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
            $stmt->bind_param("i", $del_id);
            $stmt->execute();
            $stmt->close();
            log_audit_action('DELETE_STUDENT_HARD', "Permanently removed student #$del_id.");
            $_SESSION['flash_msg'] = "Student #$del_id was permanently deleted.";
            $_SESSION['flash_type'] = "warning";
        } else {
            // Soft delete toggle
            $stmt = $conn->prepare("UPDATE students SET status = IF(status='active','inactive','active') WHERE id = ?");
            $stmt->bind_param("i", $del_id);
            $stmt->execute();
            $stmt->close();
            log_audit_action('DELETE_STUDENT_SOFT', "Toggled status for student #$del_id.");
            $_SESSION['flash_msg'] = "Student #$del_id status toggled.";
            $_SESSION['flash_type'] = "success";
        }
        header("Location: manage_students.php");
        exit();
    }
}

// Query parameters
$search = trim($_GET['search'] ?? '');
$course_filter = trim($_GET['course'] ?? '');
$status_filter = trim($_GET['status'] ?? 'all');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 8;
$offset = ($page - 1) * $limit;

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

// Count
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE $where_clause");
if (!empty($types)) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_row()[0] ?? 0;
$count_stmt->close();

$total_pages = max(1, ceil($total_records / $limit));

// Fetch
$data_stmt = $conn->prepare("SELECT id, name, email, course, year, phone, status FROM students WHERE $where_clause ORDER BY id DESC LIMIT ? OFFSET ?");
$p_params = $params;
$p_params[] = $limit;
$p_params[] = $offset;
$data_stmt->bind_param($types . "ii", ...$p_params);
$data_stmt->execute();
$students = $data_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$data_stmt->close();

$flash = $_SESSION['flash_msg'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

require_once __DIR__ . '/includes/header.php';
?>

<div class="content-container">
    <div class="toolbar">
        <div>
            <h1>Student Records Management</h1>
            <p>Comprehensive administrative CRUD with MySQLi prepared statements, pagination, soft delete, and CSV export.</p>
        </div>
        <div class="toolbar-actions">
            <button id="addStudentModalBtn" class="btn btn-primary">+ Add Student</button>
            <a href="export_students.php?<?= http_build_query($_GET) ?>" class="btn btn-secondary">📥 Export CSV</a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flash_type) ?>">
            <?= htmlspecialchars($flash) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <form method="GET" action="manage_students.php" class="filter-bar">
        <div class="filter-group flex-2">
            <label for="search">Search Keywords</label>
            <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search name, email, phone...">
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
                <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <div class="filter-buttons">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="manage_students.php" class="btn btn-outline">Reset</a>
        </div>
    </form>

    <div class="meta-row">
        <span>Records Found: <strong><?= $total_records ?></strong></span>
        <span>Page <strong><?= $page ?></strong> of <strong><?= $total_pages ?></strong></span>
    </div>

    <!-- Data Table -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Course</th>
                        <th width="80">Year</th>
                        <th>Phone</th>
                        <th width="100">Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td>#<?= (int)$s['id'] ?></td>
                                <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                                <td><?= htmlspecialchars($s['email']) ?></td>
                                <td><span class="badge-course"><?= htmlspecialchars($s['course']) ?></span></td>
                                <td>Year <?= (int)$s['year'] ?></td>
                                <td><?= htmlspecialchars($s['phone'] ?? '-') ?></td>
                                <td>
                                    <span class="status-pill status-<?= htmlspecialchars($s['status']) ?>">
                                        <?= ucfirst(htmlspecialchars($s['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-sm btn-edit" onclick='openEditStudentModal(<?= json_encode($s) ?>)'>Edit</button>
                                    <a href="manage_students.php?del_id=<?= (int)$s['id'] ?>&type=soft" 
                                       class="btn-sm btn-warn" 
                                       onclick="return confirm('Toggle active/inactive status for <?= htmlspecialchars(addslashes($s['name'])) ?>?');">
                                        <?= $s['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                    </a>
                                    <a href="manage_students.php?del_id=<?= (int)$s['id'] ?>&type=permanent" 
                                       class="btn-sm btn-delete" 
                                       onclick="return confirm('Permanently remove <?= htmlspecialchars(addslashes($s['name'])) ?>?');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 32px; color: #64748b;">
                                No student records found. <a href="manage_students.php">Clear filters</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" 
                   class="<?= $p === $page ? 'active-page' : '' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal for Add / Edit Student -->
<div id="studentModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="studentModalTitle">Add New Student</h3>
            <button type="button" class="close-btn" id="closeStudentModal">&times;</button>
        </div>

        <form method="POST" action="manage_students.php">
            <input type="hidden" name="action" id="studentAction" value="add">
            <input type="hidden" name="id" id="studentId" value="">

            <div class="form-group">
                <label for="m_name">Full Name *</label>
                <input type="text" id="m_name" name="name" required>
            </div>

            <div class="form-group">
                <label for="m_email">Email Address *</label>
                <input type="email" id="m_email" name="email" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="m_course">Degree *</label>
                    <select id="m_course" name="course" required>
                        <option value="B.Tech IT">B.Tech IT</option>
                        <option value="B.Tech CSE">B.Tech CSE</option>
                        <option value="BCA">BCA</option>
                        <option value="MCA">MCA</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="m_year">Year *</label>
                    <select id="m_year" name="year" required>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="m_phone">Phone *</label>
                    <input type="tel" id="m_phone" name="phone" required>
                </div>

                <div class="form-group">
                    <label for="m_status">Status</label>
                    <select id="m_status" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="cancelStudentModal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveStudentBtn">Save Student</button>
            </div>
        </form>
    </div>
</div>

<script>
    const stuModal = document.getElementById('studentModal');
    const openAddBtn = document.getElementById('addStudentModalBtn');
    const closeBtn = document.getElementById('closeStudentModal');
    const cancelBtn = document.getElementById('cancelStudentModal');

    function openAddModal() {
        document.getElementById('studentModalTitle').textContent = 'Add New Student';
        document.getElementById('studentAction').value = 'add';
        document.getElementById('studentId').value = '';
        document.getElementById('m_name').value = '';
        document.getElementById('m_email').value = '';
        document.getElementById('m_phone').value = '';
        document.getElementById('m_course').value = 'B.Tech IT';
        document.getElementById('m_year').value = '1';
        document.getElementById('m_status').value = 'active';
        stuModal.style.display = 'flex';
    }

    function openEditStudentModal(s) {
        document.getElementById('studentModalTitle').textContent = 'Edit Student #' + s.id;
        document.getElementById('studentAction').value = 'edit';
        document.getElementById('studentId').value = s.id;
        document.getElementById('m_name').value = s.name;
        document.getElementById('m_email').value = s.email;
        document.getElementById('m_phone').value = s.phone || '';
        document.getElementById('m_course').value = s.course;
        document.getElementById('m_year').value = s.year;
        document.getElementById('m_status').value = s.status;
        stuModal.style.display = 'flex';
    }

    function closeStuModal() {
        stuModal.style.display = 'none';
    }

    if (openAddBtn) openAddBtn.addEventListener('click', openAddModal);
    if (closeBtn) closeBtn.addEventListener('click', closeStuModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeStuModal);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
