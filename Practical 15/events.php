<?php
/**
 * Practical 15: Events Browser & Enrollment Hub
 * StudentHub Full-Stack Portal
 */
$page_title = "Campus Events";
require_once __DIR__ . '/includes/header.php';

$category = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');

$where = ["1=1"];
$params = [];
$types = "";

if (!empty($category)) {
    $where[] = "category = ?";
    $params[] = $category;
    $types .= "s";
}

if (!empty($search)) {
    $where[] = "(title LIKE ? OR description LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

$sql = "SELECT id, title, category, description, event_date, poster, status FROM events WHERE " . implode(" AND ", $where) . " ORDER BY event_date ASC";
$stmt = $conn->prepare($sql);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check student's registered event IDs if logged in
$registered_event_ids = [];
if ($user && $user['role'] === 'student') {
    // Get student id
    $s_stmt = $conn->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
    $s_stmt->bind_param("s", $user['email']);
    $s_stmt->execute();
    $s_row = $s_stmt->get_result()->fetch_assoc();
    $s_stmt->close();

    if ($s_row) {
        $r_stmt = $conn->prepare("SELECT event_id FROM registrations WHERE student_id = ?");
        $r_stmt->bind_param("i", $s_row['id']);
        $r_stmt->execute();
        $r_res = $r_stmt->get_result();
        while ($r = $r_res->fetch_assoc()) {
            $registered_event_ids[] = (int)$r['event_id'];
        }
        $r_stmt->close();
    }
}

$flash = $_SESSION['flash_msg'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? 'info';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
?>

<div class="content-container">
    <div class="page-header">
        <h1>Campus Events & Technical Symposia</h1>
        <p>Participate in coding hackathons, technical workshops, paper presentations, and cultural fests.</p>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flash_type) ?>">
            <?= htmlspecialchars($flash) ?>
        </div>
    <?php endif; ?>

    <form method="GET" action="events.php" class="filter-bar">
        <div class="filter-group flex-2">
            <label for="search">Search Events</label>
            <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search keyword or event title...">
        </div>

        <div class="filter-group">
            <label for="category">Category Filter</label>
            <select id="category" name="category">
                <option value="">All Categories</option>
                <option value="Technical" <?= $category === 'Technical' ? 'selected' : '' ?>>Technical</option>
                <option value="Workshop" <?= $category === 'Workshop' ? 'selected' : '' ?>>Workshop</option>
                <option value="Cultural" <?= $category === 'Cultural' ? 'selected' : '' ?>>Cultural</option>
                <option value="Sports" <?= $category === 'Sports' ? 'selected' : '' ?>>Sports</option>
                <option value="Seminar" <?= $category === 'Seminar' ? 'selected' : '' ?>>Seminar</option>
            </select>
        </div>

        <div class="filter-buttons">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="events.php" class="btn btn-outline">Reset</a>
        </div>
    </form>

    <div class="events-main-grid">
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $e): ?>
                <?php 
                    $poster = (!empty($e['poster']) && file_exists(__DIR__ . '/uploads/' . $e['poster']))
                        ? 'uploads/' . htmlspecialchars($e['poster'])
                        : '../Practical 12/uploads/default_poster.svg';
                    $is_registered = in_array((int)$e['id'], $registered_event_ids, true);
                ?>
                <div class="event-card-full">
                    <div class="poster-container">
                        <img src="<?= $poster ?>" alt="<?= htmlspecialchars($e['title']) ?>">
                        <span class="status-pill status-<?= htmlspecialchars($e['status']) ?>">
                            <?= ucfirst(htmlspecialchars($e['status'])) ?>
                        </span>
                    </div>

                    <div class="event-card-content">
                        <span class="category-pill"><?= htmlspecialchars($e['category']) ?></span>
                        <h3><?= htmlspecialchars($e['title']) ?></h3>
                        <div class="event-date-row">
                            <span>🗓 <?= date('l, d F Y', strtotime($e['event_date'])) ?></span>
                        </div>
                        <p class="event-desc-text"><?= htmlspecialchars($e['description'] ?? 'No additional description provided.') ?></p>

                        <div class="event-card-action">
                            <?php if ($e['status'] === 'closed'): ?>
                                <button class="btn btn-disabled" disabled>Registrations Closed</button>
                            <?php elseif ($is_registered): ?>
                                <span class="badge-enrolled">✓ Registered</span>
                            <?php elseif ($user && $user['role'] === 'student'): ?>
                                <a href="event_register.php?event_id=<?= (int)$e['id'] ?>" 
                                   class="btn btn-primary"
                                   onclick="return confirm('Confirm registration for <?= htmlspecialchars(addslashes($e['title'])) ?>?');">
                                    Register Now &rarr;
                                </a>
                            <?php elseif ($user && $user['role'] === 'admin'): ?>
                                <a href="manage_events.php" class="btn btn-outline">Edit in Admin Panel</a>
                            <?php else: ?>
                                <a href="login.php?redirect=events.php" class="btn btn-secondary">Login to Register</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state" style="grid-column: 1 / -1;">
                <p>No campus events found matching your filter criteria.</p>
                <a href="events.php" class="btn btn-outline" style="margin-top: 12px;">Reset Filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
