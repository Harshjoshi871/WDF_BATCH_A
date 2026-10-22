<?php
/**
 * Practical 11: Export Student Records to CSV (Intermediate Extension)
 * StudentHub Portal - CHARUSAT ITUE203
 */
require_once __DIR__ . '/db.php';

$search = trim($_GET['search'] ?? '');
$course_filter = trim($_GET['course'] ?? '');
$status_filter = trim($_GET['status'] ?? 'all');

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
$sql = "SELECT id, name, email, course, year, phone, status, created_at FROM students WHERE $where_clause ORDER BY id ASC";

$stmt = $conn->prepare($sql);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$filename = "students_export_" . date("Y-m-d_His") . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Write UTF-8 BOM for Microsoft Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header Row
fputcsv($output, ['ID', 'Full Name', 'Email Address', 'Course', 'Academic Year', 'Phone Number', 'Status', 'Registration Date']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
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

fclose($output);
$stmt->close();
exit();
?>
