<?php
/**
 * Practical 15: Universal Accessible Header & Dynamic Navigation
 * StudentHub Full-Stack Portal
 */
require_once __DIR__ . '/../config/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - StudentHub' : 'StudentHub Portal - CHARUSAT' ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Accessibility: Skip to main content link -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header class="site-header">
        <div class="header-inner">
            <div class="site-branding">
                <a href="index.php" class="brand-link">
                    <span class="brand-icon">🎓</span>
                    <div>
                        <span class="brand-title">StudentHub</span>
                        <span class="brand-subtitle">CHARUSAT FTE Portal</span>
                    </div>
                </a>
            </div>

            <!-- Hamburger Button for Mobile -->
            <button class="mobile-toggle" id="menuToggle" aria-label="Toggle navigation menu" aria-expanded="false">
                ☰
            </button>

            <!-- Dynamic Role-Aware Navigation Menu -->
            <nav class="site-nav" id="mainNav">
                <a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Home</a>
                <a href="about.php" class="<?= $current_page === 'about.php' ? 'active' : '' ?>">About</a>
                <a href="events.php" class="<?= $current_page === 'events.php' ? 'active' : '' ?>">Events</a>
                <a href="ajax_events.php" class="<?= $current_page === 'ajax_events.php' ? 'active' : '' ?>">AJAX Hub</a>
                <a href="timetable.php" class="<?= $current_page === 'timetable.php' ? 'active' : '' ?>">Timetable</a>
                <a href="notices.php" class="<?= $current_page === 'notices.php' ? 'active' : '' ?>">Notices</a>
                <a href="contact.php" class="<?= $current_page === 'contact.php' ? 'active' : '' ?>">Contact</a>

                <?php if ($user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <div class="nav-dropdown">
                            <a href="admin_dashboard.php" class="<?= in_array($current_page, ['admin_dashboard.php', 'manage_students.php', 'manage_events.php', 'audit_logs.php']) ? 'active' : '' ?>">
                                Admin Panel ▾
                            </a>
                            <div class="dropdown-menu">
                                <a href="admin_dashboard.php">Dashboard & Analytics</a>
                                <a href="manage_students.php">Manage Students (CRUD)</a>
                                <a href="manage_events.php">Manage Events (CRUD)</a>
                                <a href="audit_logs.php">System Audit Trail</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="student_dashboard.php" class="<?= $current_page === 'student_dashboard.php' ? 'active' : '' ?>">My Profile</a>
                    <?php endif; ?>

                    <span class="nav-user-greeting">
                        👋 <?= htmlspecialchars($user['name']) ?> (<?= ucfirst($user['role']) ?>)
                    </span>
                    <a href="logout.php" class="nav-btn-logout">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-btn-login <?= $current_page === 'login.php' ? 'active' : '' ?>">Login</a>
                    <a href="register.php" class="nav-btn-register <?= $current_page === 'register.php' ? 'active' : '' ?>">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main id="main-content" class="site-main">
