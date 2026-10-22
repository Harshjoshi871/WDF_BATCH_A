<?php
/**
 * Practical 15: RESTful JSON API Endpoint
 * StudentHub Full-Stack Portal
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/db.php';

function json_res(int $code, bool $success, string $message, $data = null) {
    http_response_code($code);
    echo json_encode([
        'status'    => $code,
        'success'   => $success,
        'message'   => $message,
        'data'      => $data,
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

$raw = file_get_contents('php://input');
$json = json_decode($raw, true) ?? [];
$req = array_merge($_GET, $_POST, $json);

$action = $req['action'] ?? 'list';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if ($action === 'list') {
        $search = trim($req['search'] ?? '');
        $category = trim($req['category'] ?? '');

        $where = ["1=1"];
        $params = [];
        $types = "";

        if (!empty($search)) {
            $where[] = "(title LIKE ? OR description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= "ss";
        }
        if (!empty($category)) {
            $where[] = "category = ?";
            $params[] = $category;
            $types .= "s";
        }

        $sql = "SELECT id, title, category, description, event_date, poster, status FROM events WHERE " . implode(" AND ", $where) . " ORDER BY event_date ASC";
        $stmt = $conn->prepare($sql);
        if (!empty($types)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        json_res(200, true, "Events fetched", $rows);
    } elseif ($action === 'get') {
        $id = intval($req['id'] ?? 0);
        $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) json_res(200, true, "Event details", $row);
        json_res(404, false, "Event not found");
    }
}

if ($method === 'POST') {
    if ($action === 'create') {
        $title = trim($req['title'] ?? '');
        $category = trim($req['category'] ?? 'Technical');
        $desc = trim($req['description'] ?? '');
        $date = trim($req['event_date'] ?? '');
        $status = ($req['status'] ?? 'open') === 'closed' ? 'closed' : 'open';

        if (empty($title) || empty($date)) {
            json_res(400, false, "Title and date are mandatory.");
        }

        $stmt = $conn->prepare("INSERT INTO events (title, category, description, event_date, poster, status) VALUES (?, ?, ?, ?, 'default_poster.svg', ?)");
        $stmt->bind_param("sssss", $title, $category, $desc, $date, $status);
        if ($stmt->execute()) {
            $nid = $conn->insert_id;
            $stmt->close();
            json_res(201, true, "Event published via API", ['id' => $nid, 'title' => $title]);
        } else {
            json_res(500, false, "DB Error: " . $conn->error);
        }
    } elseif ($action === 'update') {
        $id = intval($req['id'] ?? 0);
        $title = trim($req['title'] ?? '');
        $category = trim($req['category'] ?? 'Technical');
        $desc = trim($req['description'] ?? '');
        $date = trim($req['event_date'] ?? '');
        $status = ($req['status'] ?? 'open') === 'closed' ? 'closed' : 'open';

        if ($id <= 0 || empty($title) || empty($date)) {
            json_res(400, false, "Invalid payload for event update.");
        }

        $stmt = $conn->prepare("UPDATE events SET title=?, category=?, description=?, event_date=?, status=? WHERE id=?");
        $stmt->bind_param("sssssi", $title, $category, $desc, $date, $status, $id);
        if ($stmt->execute()) {
            $stmt->close();
            json_res(200, true, "Event updated via API");
        } else {
            json_res(500, false, "DB Error: " . $conn->error);
        }
    } elseif ($action === 'delete') {
        $id = intval($req['id'] ?? 0);
        if ($id <= 0) json_res(400, false, "Valid ID required.");

        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->close();
            json_res(200, true, "Event deleted successfully");
        } else {
            json_res(500, false, "DB Error: " . $conn->error);
        }
    }
}

json_res(405, false, "Method not allowed.");
?>
