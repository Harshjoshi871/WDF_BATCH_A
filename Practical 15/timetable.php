<?php
/**
 * Practical 15: Academic Class Timetable
 * StudentHub Full-Stack Portal
 */
$page_title = "Academic Timetable";
require_once __DIR__ . '/includes/header.php';
?>

<div class="content-container">
    <div class="page-header">
        <h1>Weekly Academic Timetable</h1>
        <p>Faculty of Technology and Engineering | Semester 3 (ODD 2026-27)</p>
    </div>

    <div class="card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="color: var(--primary);">B.Tech Information Technology - Div A</h3>
                <p class="subtitle">Room: Lab 402 / Lecture Hall 204</p>
            </div>
            <span class="badge badge-subtle">Effective from: July 2026</span>
        </div>

        <div class="table-container">
            <table class="data-table" style="text-align: center;">
                <thead>
                    <tr>
                        <th style="text-align: left;">Time / Day</th>
                        <th>Monday</th>
                        <th>Tuesday</th>
                        <th>Wednesday</th>
                        <th>Thursday</th>
                        <th>Friday</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>09:00 - 10:00</strong></td>
                        <td><span class="badge-course">WDF</span><br><small>ITUE203</small></td>
                        <td><span class="badge-course">DSA</span><br><small>ITUE201</small></td>
                        <td><span class="badge-course">CN</span><br><small>ITUE202</small></td>
                        <td><span class="badge-course">WDF</span><br><small>ITUE203</small></td>
                        <td><span class="badge-course">DSA</span><br><small>ITUE201</small></td>
                    </tr>
                    <tr>
                        <td><strong>10:00 - 11:00</strong></td>
                        <td><span class="badge-course">JAVA</span><br><small>ITUE204</small></td>
                        <td><span class="badge-course">MATHS-3</span><br><small>MA201</small></td>
                        <td><span class="badge-course">JAVA</span><br><small>ITUE204</small></td>
                        <td><span class="badge-course">CN</span><br><small>ITUE202</small></td>
                        <td><span class="badge-course">MATHS-3</span><br><small>MA201</small></td>
                    </tr>
                    <tr style="background: #f8fafc; font-weight: bold; color: #64748b;">
                        <td><strong>11:00 - 11:30</strong></td>
                        <td colspan="5" style="letter-spacing: 2px;">☕ RECESS / BREAK</td>
                    </tr>
                    <tr>
                        <td><strong>11:30 - 01:30</strong></td>
                        <td><strong style="color: #4f46e5;">WDF Lab</strong><br><small>Batch B1 (Lab 402)</small></td>
                        <td><strong style="color: #059669;">DSA Lab</strong><br><small>Batch B2 (Lab 405)</small></td>
                        <td><strong style="color: #d97706;">JAVA Lab</strong><br><small>Batch B1 (Lab 401)</small></td>
                        <td><strong style="color: #dc2626;">CN Lab</strong><br><small>Batch B2 (Lab 403)</small></td>
                        <td><strong style="color: #7c3aed;">Project Lab</strong><br><small>All Batches</small></td>
                    </tr>
                    <tr style="background: #f8fafc; font-weight: bold; color: #64748b;">
                        <td><strong>01:30 - 02:15</strong></td>
                        <td colspan="5" style="letter-spacing: 2px;">🍱 LUNCH BREAK</td>
                    </tr>
                    <tr>
                        <td><strong>02:15 - 03:15</strong></td>
                        <td><span class="badge-course">CN</span><br><small>ITUE202</small></td>
                        <td><span class="badge-course">WDF</span><br><small>ITUE203</small></td>
                        <td><span class="badge-course">DSA</span><br><small>ITUE201</small></td>
                        <td><span class="badge-course">JAVA</span><br><small>ITUE204</small></td>
                        <td><span class="badge-course">LIB</span><br><small>Library Study</small></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
