<?php
/**
 * Practical 15: Campus Announcements & Notices
 * StudentHub Full-Stack Portal
 */
$page_title = "Campus Notices";
require_once __DIR__ . '/includes/header.php';
?>

<div class="content-container">
    <div class="page-header">
        <h1>Campus Notices & Circulars</h1>
        <p>Official communications from the Registrar, Department Heads, and Examination Section.</p>
    </div>

    <div class="notices-list">
        <div class="notice-card">
            <div class="notice-meta">
                <span class="category-pill" style="background:#fee2e2; color:#991b1b;">EXAMINATION</span>
                <span class="notice-date">21 Oct 2026</span>
            </div>
            <h3>Mid-Term Examination Schedule Announcement - Sem 3</h3>
            <p>
                The mid-semester practical and theory examination schedule for Semester 3 B.Tech / BCA / MCA has been finalized. 
                Students are advised to review the lab submission deadlines and ensure minimum 75% attendance criteria.
            </p>
            <div class="notice-footer">
                <small>Issued by: Controller of Examinations, CHARUSAT</small>
            </div>
        </div>

        <div class="notice-card">
            <div class="notice-meta">
                <span class="category-pill" style="background:#d1fae5; color:#065f46;">TECH FEST</span>
                <span class="notice-date">18 Oct 2026</span>
            </div>
            <h3>Registration Open for Spoural & Hackathon 2026</h3>
            <p>
                Student registrations for the annual technical symposium and 36-hour Hackathon 2026 are now active. 
                Teams of 2 to 4 members can register directly via the StudentHub Events portal.
            </p>
            <div class="notice-footer">
                <small>Issued by: Student Affairs Council</small>
            </div>
        </div>

        <div class="notice-card">
            <div class="notice-meta">
                <span class="category-pill" style="background:#e0e7ff; color:#3730a3;">ACADEMICS</span>
                <span class="notice-date">12 Oct 2026</span>
            </div>
            <h3>Web Development Frameworks (ITUE203) Laboratory Submissions</h3>
            <p>
                All students must ensure Practicals 1 through 15 are completed and committed to their respective GitHub repositories. 
                Practical evaluations and viva examinations will commence next week.
            </p>
            <div class="notice-footer">
                <small>Issued by: Department of Information Technology, FTE</small>
            </div>
        </div>

        <div class="notice-card">
            <div class="notice-meta">
                <span class="category-pill" style="background:#fef3c7; color:#92400e;">PLACEMENT</span>
                <span class="notice-date">05 Oct 2026</span>
            </div>
            <h3>Industry Internship Fair & Campus Placement Drive</h3>
            <p>
                Leading IT firms and software startups will conduct pre-placement talks and coding assessments on campus. 
                Pre-final and final year students must update their resumes and student profile records.
            </p>
            <div class="notice-footer">
                <small>Issued by: Central Training & Placement Cell</small>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
