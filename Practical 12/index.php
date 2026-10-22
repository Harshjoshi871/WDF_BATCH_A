<?php
/**
 * Practical 12: Event Management CRUD with Poster Upload
 * StudentHub Portal - CHARUSAT ITUE203
 */
session_start();
require_once __DIR__ . '/db.php';

// Flash Messages
$flash_msg = $_SESSION['flash_msg'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

// Filter Inputs (Intermediate Extension)
$category_filter = trim($_GET['category'] ?? '');
$status_filter = trim($_GET['status'] ?? 'all');
$search = trim($_GET['search'] ?? '');

$where = ["1=1"];
$params = [];
$types = "";

if (!empty($search)) {
    $where[] = "(title LIKE ? OR description LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

if (!empty($category_filter)) {
    $where[] = "category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if ($status_filter === 'open' || $status_filter === 'closed') {
    $where[] = "status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$where_clause = implode(" AND ", $where);
$sql = "SELECT id, title, category, description, event_date, poster, status FROM events WHERE $where_clause ORDER BY event_date ASC";

$stmt = $conn->prepare($sql);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Practical 12: Event Management CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="header-container">
            <div class="brand">
                <span class="logo-badge">🎪</span>
                <div>
                    <h1>StudentHub Portal</h1>
                    <p>Practical 12: Event Management & File Validation</p>
                </div>
            </div>
            <nav>
                <a href="../Practical 8/test_connection.php">Database</a>
                <a href="../Practical 9/register.php">Practical 9: Register</a>
                <a href="../Practical 10/login.php">Practical 10: Login</a>
                <a href="../Practical 11/index.php">Practical 11: Students</a>
                <a class="active" href="index.php">Practical 12: Events</a>
                <a href="../Practical 13/index.html">Practical 13: AJAX CRUD</a>
                <a href="../Practical 14/index.php">Practical 14: Dashboard</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="card list-card">
            <div class="toolbar">
                <div>
                    <h2>Campus Event Management</h2>
                    <p>Create, update, delete events and manage event posters with MIME and size validation.</p>
                </div>
                <div>
                    <a href="add_event.php" class="btn btn-primary">+ Add New Event</a>
                </div>
            </div>

            <?php if (!empty($flash_msg)): ?>
                <div class="alert alert-<?= htmlspecialchars($flash_type) ?>">
                    <?= htmlspecialchars($flash_msg) ?>
                </div>
            <?php endif; ?>

            <!-- Filter and Search Bar -->
            <form method="GET" action="index.php" class="filter-bar">
                <div class="filter-group flex-2">
                    <label for="search">Search Events</label>
                    <input type="text" id="search" name="search" placeholder="Search by event title or keyword..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="filter-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <option value="Technical" <?= $category_filter === 'Technical' ? 'selected' : '' ?>>Technical</option>
                        <option value="Workshop" <?= $category_filter === 'Workshop' ? 'selected' : '' ?>>Workshop</option>
                        <option value="Cultural" <?= $category_filter === 'Cultural' ? 'selected' : '' ?>>Cultural</option>
                        <option value="Sports" <?= $category_filter === 'Sports' ? 'selected' : '' ?>>Sports</option>
                        <option value="Seminar" <?= $category_filter === 'Seminar' ? 'selected' : '' ?>>Seminar</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="status">Registration Status</label>
                    <select id="status" name="status">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Status</option>
                        <option value="open" <?= $status_filter === 'open' ? 'selected' : '' ?>>Open for Registration</option>
                        <option value="closed" <?= $status_filter === 'closed' ? 'selected' : '' ?>>Closed / Concluded</option>
                    </select>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="index.php" class="btn btn-outline">Reset</a>
                </div>
            </form>

            <!-- Event Cards Grid -->
            <div class="event-grid">
                <?php if (count($events) > 0): ?>
                    <?php foreach ($events as $e): ?>
                        <?php 
                            $poster_src = (!empty($e['poster']) && file_exists(__DIR__ . '/uploads/' . $e['poster'])) 
                                ? 'uploads/' . htmlspecialchars($e['poster']) 
                                : 'uploads/default_poster.svg';
                        ?>
                        <div class="event-card">
                            <div class="event-poster-wrapper">
                                <img src="<?= $poster_src ?>" alt="<?= htmlspecialchars($e['title']) ?>" class="event-poster">
                                <span class="status-badge status-<?= htmlspecialchars($e['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($e['status'])) ?>
                                </span>
                            </div>

                            <div class="event-body">
                                <span class="category-pill"><?= htmlspecialchars($e['category']) ?></span>
                                <h3 class="event-title"><?= htmlspecialchars($e['title']) ?></h3>
                                <div class="event-date">
                                    🗓 <?= htmlspecialchars(date('D, d M Y', strtotime($e['event_date']))) ?>
                                </div>
                                <p class="event-desc"><?= htmlspecialchars(mb_strimwidth($e['description'] ?? '', 0, 110, '...')) ?></p>
                            </div>

                            <div class="event-footer">
                                <a href="edit_event.php?id=<?= (int)$e['id'] ?>" class="btn-sm btn-edit">Edit Event</a>
                                <a href="delete_event.php?id=<?= (int)$e['id'] ?>" 
                                   class="btn-sm btn-delete" 
                                   onclick="return confirm('Permanently remove event: <?= htmlspecialchars(addslashes($e['title'])) ?>?');">
                                   Delete
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 48px; background: #f8fafc; border-radius: 8px;">
                        <p style="color: #64748b; font-size: 15px;">No events found matching your filter criteria.</p>
                        <a href="add_event.php" class="btn btn-primary" style="margin-top: 12px;">Create an Event</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>© 2026 StudentHub Portal | CHARUSAT - ITUE203 Web Development Frameworks</p>
    </footer>

</body>
</html>
