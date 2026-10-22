<?php
/**
 * Practical 15: CSV Exporter for Students
 * StudentHub Full-Stack Portal
 */
require_once __DIR__ . '/config/auth.php';
require_admin();

$search = trim($_GET['search'] ?? '');
$course = trim($_GET['course'] ?? '');
$status = trim($_GET['status'] ?? 'all');

$where = ["1=1"];
$params = [];
$types = "";

if (!empty($search)) {
    $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}

if (!empty($course)) {
    $where[] = "course = ?";
    $params[] = $course;
    $types .= "s";
}

if ($status === 'active' || $status === 'inactive') {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql = "SELECT id, name, email, course, year, phone, status, created_at FROM students WHERE " . implode(" AND ", $where) . " ORDER BY id ASC";
$stmt = $conn->prepare($sql);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$filename = "studenthub_students_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$fp = fopen('php://output', 'w');
fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($fp, ['ID', 'Full Name', 'Email Address', 'Degree Course', 'Academic Year', 'Phone', 'Status', 'Registration Date']);

while ($row = $res->fetch_assoc()) {
    fputcsv($fp, [
        $row['id'],
        $row['name'],
        $row['email'],
        $row['course'],
        'Year ' . $row['year'],
        $row['phone'] ?? 'N/A',
        ucfirst($row['status']),
        $row['created_at']
    ]);
}

fclose($fp);
$stmt->close();
exit();
?>
