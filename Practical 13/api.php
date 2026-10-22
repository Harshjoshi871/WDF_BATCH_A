<?php
/**
 * Practical 13: RESTful JSON API Endpoint for Events
 * StudentHub Portal - CHARUSAT ITUE203
 */

// Set strict JSON header
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

/**
 * Advanced Extension: Centralized Response & HTTP Status Helper
 */
function send_json_response(int $status_code, bool $success, string $message, $data = null) {
    http_response_code($status_code);
    echo json_encode([
        'status'    => $status_code,
        'success'   => $success,
        'message'   => $message,
        'data'      => $data,
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

// Read raw JSON body if request Content-Type is application/json
$raw_input = file_get_contents('php://input');
$json_data = json_decode($raw_input, true) ?? [];

// Merge request parameters
$request = array_merge($_GET, $_POST, $json_data);
$action = $request['action'] ?? '';

$method = $_SERVER['REQUEST_METHOD'];

// 1. GET Actions (List & Single Item)
if ($method === 'GET') {
    if ($action === 'list' || empty($action)) {
        $search = trim($request['search'] ?? '');
        $category = trim($request['category'] ?? '');

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

        if (!empty($category)) {
            $where[] = "category = ?";
            $params[] = $category;
            $types .= "s";
        }

        $sql = "SELECT id, title, category, description, event_date, poster, status FROM events WHERE " . implode(" AND ", $where) . " ORDER BY event_date ASC";
        $stmt = $conn->prepare($sql);
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        send_json_response(200, true, "Events retrieved successfully", $result);
    } 
    elseif ($action === 'get') {
        $id = intval($request['id'] ?? 0);
        if ($id <= 0) {
            send_json_response(400, false, "Invalid or missing event ID parameter.");
        }

        $stmt = $conn->prepare("SELECT id, title, category, description, event_date, poster, status FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $event = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$event) {
            send_json_response(404, false, "Event with ID $id not found.");
        }

        send_json_response(200, true, "Event details fetched successfully", $event);
    } 
    else {
        send_json_response(400, false, "Unrecognized GET action '$action'.");
    }
}

// 2. POST Actions (Create, Update, Delete)
if ($method === 'POST') {
    // Action: CREATE
    if ($action === 'create') {
        $title = trim($request['title'] ?? '');
        $category = trim($request['category'] ?? 'Technical');
        $description = trim($request['description'] ?? '');
        $event_date = trim($request['event_date'] ?? '');
        $status = ($request['status'] ?? 'open') === 'closed' ? 'closed' : 'open';

        // Validation
        if (empty($title) || strlen($title) < 3) {
            send_json_response(400, false, "Title must contain at least 3 characters.");
        }
        if (empty($event_date)) {
            send_json_response(400, false, "Valid event date is required.");
        }

        $poster = 'default_poster.svg';
        $stmt = $conn->prepare("INSERT INTO events (title, category, description, event_date, poster, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $category, $description, $event_date, $poster, $status);

        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            $stmt->close();

            // Log action
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $act = 'API_CREATE_EVENT';
            $det = "API created event #$new_id ($title).";
            $l_stmt = $conn->prepare("INSERT INTO audit_logs (action, details, ip_address) VALUES (?, ?, ?)");
            if ($l_stmt) {
                $l_stmt->bind_param("sss", $act, $det, $ip);
                $l_stmt->execute();
                $l_stmt->close();
            }

            send_json_response(201, true, "Event created successfully via API", [
                'id'          => $new_id,
                'title'       => $title,
                'category'    => $category,
                'description' => $description,
                'event_date'  => $event_date,
                'status'      => $status
            ]);
        } else {
            send_json_response(500, false, "Database error: " . $conn->error);
        }
    }

    // Action: UPDATE
    elseif ($action === 'update') {
        $id = intval($request['id'] ?? 0);
        $title = trim($request['title'] ?? '');
        $category = trim($request['category'] ?? 'Technical');
        $description = trim($request['description'] ?? '');
        $event_date = trim($request['event_date'] ?? '');
        $status = ($request['status'] ?? 'open') === 'closed' ? 'closed' : 'open';

        if ($id <= 0) {
            send_json_response(400, false, "Valid event ID is required for update.");
        }
        if (empty($title) || strlen($title) < 3) {
            send_json_response(400, false, "Title must contain at least 3 characters.");
        }
        if (empty($event_date)) {
            send_json_response(400, false, "Valid event date is required.");
        }

        $stmt = $conn->prepare("UPDATE events SET title = ?, category = ?, description = ?, event_date = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $title, $category, $description, $event_date, $status, $id);

        if ($stmt->execute()) {
            $stmt->close();

            // Log action
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $act = 'API_UPDATE_EVENT';
            $det = "API updated event #$id ($title).";
            $l_stmt = $conn->prepare("INSERT INTO audit_logs (action, details, ip_address) VALUES (?, ?, ?)");
            if ($l_stmt) {
                $l_stmt->bind_param("sss", $act, $det, $ip);
                $l_stmt->execute();
                $l_stmt->close();
            }

            send_json_response(200, true, "Event #$id updated successfully", [
                'id'          => $id,
                'title'       => $title,
                'category'    => $category,
                'description' => $description,
                'event_date'  => $event_date,
                'status'      => $status
            ]);
        } else {
            send_json_response(500, false, "Database error: " . $conn->error);
        }
    }

    // Action: DELETE
    elseif ($action === 'delete') {
        $id = intval($request['id'] ?? 0);
        if ($id <= 0) {
            send_json_response(400, false, "Valid event ID is required for deletion.");
        }

        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();

            // Log action
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $act = 'API_DELETE_EVENT';
            $det = "API deleted event #$id.";
            $l_stmt = $conn->prepare("INSERT INTO audit_logs (action, details, ip_address) VALUES (?, ?, ?)");
            if ($l_stmt) {
                $l_stmt->bind_param("sss", $act, $det, $ip);
                $l_stmt->execute();
                $l_stmt->close();
            }

            send_json_response(200, true, "Event #$id deleted successfully");
        } else {
            send_json_response(500, false, "Database error: " . $conn->error);
        }
    }

    else {
        send_json_response(400, false, "Unrecognized POST action '$action'.");
    }
}

send_json_response(405, false, "Method $method Not Allowed. Use GET or POST.");
?>
