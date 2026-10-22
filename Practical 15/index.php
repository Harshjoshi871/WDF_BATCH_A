<?php
/**
 * Practical 15: Home Page - Integrated StudentHub Portal
 * StudentHub Full-Stack Portal - CHARUSAT ITUE203
 */
$page_title = "Home - Integrated Campus Portal";
require_once __DIR__ . '/includes/header.php';

// Fetch quick statistics
$stat_students = $conn->query("SELECT COUNT(*) FROM students WHERE status='active'")->fetch_row()[0] ?? 0;
$stat_events = $conn->query("SELECT COUNT(*) FROM events WHERE status='open'")->fetch_row()[0] ?? 0;
$stat_regs = $conn->query("SELECT COUNT(*) FROM registrations")->fetch_row()[0] ?? 0;

// Fetch 3 upcoming open events
$events_stmt = $conn->query("SELECT id, title, category, description, event_date, poster FROM events WHERE status='open' ORDER BY event_date ASC LIMIT 3");
$featured_events = $events_stmt ? $events_stmt->fetch_all(MYSQLI_ASSOC) : [];
?>

<section class="hero-section">
    <div class="hero-container">
        <span class="hero-tag">🌟 CHARUSAT UNIVERSITY STUDENT PORTAL</span>
        <h1 class="hero-title">Empowering Academic Excellence & Campus Engagement</h1>
        <p class="hero-subtitle">
            Welcome to StudentHub — your unified, accessible portal for course profiles, campus events, weekly timetables, institutional notices, and streamlined student services.
        </p>
        <div class="hero-buttons">
            <?php if (!$user): ?>
                <a href="register.php" class="btn btn-primary btn-lg">Get Started / Register &rarr;</a>
                <a href="login.php" class="btn btn-secondary btn-lg">Student / Admin Login</a>
            <?php else: ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php" class="btn btn-primary btn-lg">Open Admin Dashboard &rarr;</a>
                <?php else: ?>
                    <a href="student_dashboard.php" class="btn btn-primary btn-lg">My Student Dashboard &rarr;</a>
                <?php endif; ?>
                <a href="events.php" class="btn btn-secondary btn-lg">Explore Campus Events</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Live Statistics Bar -->
<section class="stats-banner">
    <div class="stats-banner-container">
        <div class="stat-banner-item">
            <span class="stat-number"><?= number_format($stat_students) ?>+</span>
            <span class="stat-desc">Enrolled Students</span>
        </div>
        <div class="stat-banner-item">
            <span class="stat-number"><?= number_format($stat_events) ?></span>
            <span class="stat-desc">Active Campus Events</span>
        </div>
        <div class="stat-banner-item">
            <span class="stat-number"><?= number_format($stat_regs) ?>+</span>
            <span class="stat-desc">Event Registrations</span>
        </div>
        <div class="stat-banner-item">
            <span class="stat-number">100%</span>
            <span class="stat-desc">Bcrypt & RBAC Secured</span>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="section-container">
    <div class="section-header">
        <h2>Core Student Modules</h2>
        <p>Explore the connected modules built across the semester web development curriculum.</p>
    </div>

    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">📅</div>
            <h3>Academic Timetable</h3>
            <p>Access department-wise weekly class schedules, laboratory timings, and lecture venues.</p>
            <a href="timetable.php" class="feature-link">View Timetable &rarr;</a>
        </div>

        <div class="feature-card">
            <div class="feature-icon">📢</div>
            <h3>Official Notices</h3>
            <p>Stay updated with circulars, midterm examination notices, fee dates, and placement drives.</p>
            <a href="notices.php" class="feature-link">Read Notices &rarr;</a>
        </div>

        <div class="feature-card">
            <div class="feature-icon">⚡</div>
            <h3>Asynchronous Event Hub</h3>
            <p>Explore, filter, and register for workshops and coding competitions with zero page reload.</p>
            <a href="ajax_events.php" class="feature-link">Open AJAX Hub &rarr;</a>
        </div>

        <div class="feature-card">
            <div class="feature-icon">🛡️</div>
            <h3>Role-Based Access</h3>
            <p>Role-segregated environments for students and faculty administrators with automated audit logs.</p>
            <a href="login.php" class="feature-link">Authentication &rarr;</a>
        </div>
    </div>
</section>

<!-- Featured Events Section -->
<section class="section-container bg-light-section">
    <div class="section-header">
        <h2>Featured Campus Events</h2>
        <p>Don't miss out on technical hackathons, symposiums, and cultural festivals at CHARUSAT.</p>
    </div>

    <div class="events-preview-grid">
        <?php if (!empty($featured_events)): ?>
            <?php foreach ($featured_events as $event): ?>
                <?php 
                    $poster_path = (!empty($event['poster']) && file_exists(__DIR__ . '/uploads/' . $event['poster']))
                        ? 'uploads/' . htmlspecialchars($event['poster'])
                        : '../Practical 12/uploads/default_poster.svg';
                ?>
                <div class="event-preview-card">
                    <img src="<?= $poster_path ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="event-preview-img">
                    <div class="event-preview-body">
                        <span class="category-pill"><?= htmlspecialchars($event['category']) ?></span>
                        <h4><?= htmlspecialchars($event['title']) ?></h4>
                        <div class="event-preview-date">🗓 <?= date('D, d M Y', strtotime($event['event_date'])) ?></div>
                        <p><?= htmlspecialchars(mb_strimwidth($event['description'] ?? '', 0, 95, '...')) ?></p>
                        <a href="events.php" class="btn btn-outline btn-block">View & Register</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #64748b; grid-column: 1 / -1;">No active events right now. Check back soon!</p>
        <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 32px;">
        <a href="events.php" class="btn btn-primary">Browse All Campus Events &rarr;</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
