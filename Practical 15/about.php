<?php
/**
 * Practical 15: About Page
 * StudentHub Full-Stack Portal
 */
$page_title = "About StudentHub";
require_once __DIR__ . '/includes/header.php';
?>

<div class="content-container">
    <div class="page-header">
        <h1>About StudentHub Portal</h1>
        <p>A comprehensive semester project engineered for CHARUSAT University students and faculty.</p>
    </div>

    <div class="about-grid">
        <div class="about-text-card">
            <h2>Project Vision & Scope</h2>
            <p>
                StudentHub is an integrated, accessible, and responsive student academic portal developed as part of the 
                <strong>Web Development Frameworks (ITUE203)</strong> curriculum at the Faculty of Technology and Engineering (FTE), 
                Charotar University of Science and Technology (CHARUSAT).
            </p>
            <p>
                The platform bridges campus communication, event registrations, academic schedules, administrative moderation, 
                and role-based student management into a secure web application adhering to modern industry standards.
            </p>

            <h3 style="margin-top: 24px; color: var(--primary);">Key Architectural Pillars</h3>
            <ul class="styled-list">
                <li><strong>Semantic & Accessible HTML5:</strong> Clean markup structure with ARIA labels, semantic landmark elements, and keyboard navigability.</li>
                <li><strong>Responsive UI Engineering:</strong> Fluid multi-device design implemented with CSS Grid, Flexbox, and mobile-first media queries.</li>
                <li><strong>Modern Client Interactivity:</strong> ES6+ JavaScript, asynchronous Fetch API, DOM manipulation, real-time debounced search, and modal dialogues.</li>
                <li><strong>Robust PHP 8 Backend:</strong> Secure server-side processing, input sanitization, file-based data sinks, and RESTful JSON APIs.</li>
                <li><strong>Relational MySQL/MariaDB:</strong> Normalized 3NF schema, ACID transactions, foreign key constraints, and MySQLi parameterized prepared statements.</li>
                <li><strong>Enterprise-Grade Web Security:</strong> Bcrypt password hashing (`password_hash`), session fixation immunity (`session_regenerate_id`), CSRF token verification, SHA-256 remember-me tokens, and persistent audit logging.</li>
            </ul>
        </div>

        <div class="about-info-card">
            <h3>Curriculum Summary</h3>
            <div class="info-metric">
                <strong>University:</strong> CHARUSAT
            </div>
            <div class="info-metric">
                <strong>Faculty:</strong> FTE (Tech & Engg)
            </div>
            <div class="info-metric">
                <strong>Subject:</strong> Web Dev Frameworks (ITUE203)
            </div>
            <div class="info-metric">
                <strong>Semester:</strong> 3rd Semester (ODD 2026-27)
            </div>
            <div class="info-metric">
                <strong>Practicals Covered:</strong> Practicals 1 to 15 (Complete)
            </div>

            <div class="tech-stack-box">
                <h4>Technologies Stack</h4>
                <div class="tech-pills">
                    <span>HTML5</span>
                    <span>CSS3</span>
                    <span>JavaScript ES6+</span>
                    <span>PHP 8.2</span>
                    <span>MySQL / MariaDB</span>
                    <span>Apache / XAMPP</span>
                    <span>Fetch API</span>
                    <span>Bcrypt</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
