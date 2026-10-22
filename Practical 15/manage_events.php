<?php
/**
 * Practical 15: Event Management CRUD Module (with File Upload Validation)
 * StudentHub Full-Stack Portal
 */
require_once __DIR__ . '/config/auth.php';
require_admin();

$page_title = "Manage Events (Admin)";

$error = '';
$flash = '';

// Handle Delete (GET)
if (isset($_GET['del_id'])) {
    $del_id = intval($_GET['del_id']);
    if ($del_id > 0) {
        // Fetch poster filename
        $stmt = $conn->prepare("SELECT title, poster FROM events WHERE id = ?");
        $stmt->bind_param("i", $del_id);
        $stmt->execute();
        $ev = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($ev) {
            $del = $conn->prepare("DELETE FROM events WHERE id = ?");
            $del->bind_param("i", $del_id);
            if ($del->execute()) {
                $del->close();
                if (!empty($ev['poster']) && file_exists(__DIR__ . '/uploads/' . $ev['poster'])) {
                    unlink(__DIR__ . '/uploads/' . $ev['poster']);
                }
                log_audit_action('DELETE_EVENT', "Admin deleted event #$del_id: '{$ev['title']}'.");
                $_SESSION['flash_msg'] = "Event '{$ev['title']}' was removed successfully.";
                $_SESSION['flash_type'] = "warning";
            }
        }
        header("Location: manage_events.php");
        exit();
    }
}

// Handle Add / Edit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'Technical');
    $event_date = trim($_POST['event_date'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = ($_POST['status'] ?? 'open') === 'closed' ? 'closed' : 'open';

    if (empty($title) || strlen($title) < 3) {
        $error = "Event title is required (min 3 characters).";
    } elseif (empty($event_date)) {
        $error = "Valid event date is required.";
    } else {
        $uploaded_poster = null;

        // Process File Upload if provided
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['poster']['tmp_name'];
            $size = $_FILES['poster']['size'];
            $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];

            if ($size > 2 * 1024 * 1024) {
                $error = "Poster file exceeds the 2 MB size limit.";
            } elseif (!in_array($ext, $allowed_exts, true)) {
                $error = "Invalid file extension. Only JPG, PNG, and WEBP images are allowed.";
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $tmp);
                finfo_close($finfo);

                if (!in_array($mime, $allowed_mimes, true)) {
                    $error = "Security error: File content signature does not match a genuine image format ($mime).";
                } else {
                    $uploaded_poster = 'poster_' . bin2hex(random_bytes(8)) . '.' . $ext;
                    if (!move_uploaded_file($tmp, __DIR__ . '/uploads/' . $uploaded_poster)) {
                        $error = "Failed to store uploaded poster image.";
                    }
                }
            }
        }

        if (empty($error)) {
            if ($action === 'add') {
                $ins = $conn->prepare("INSERT INTO events (title, category, description, event_date, poster, status) VALUES (?, ?, ?, ?, ?, ?)");
                $ins->bind_param("ssssss", $title, $category, $description, $event_date, $uploaded_poster, $status);
                if ($ins->execute()) {
                    $new_eid = $conn->insert_id;
                    $ins->close();
                    log_audit_action('ADD_EVENT', "Admin published event #$new_eid: '$title'.");
                    $_SESSION['flash_msg'] = "Event '$title' published successfully!";
                    $_SESSION['flash_type'] = "success";
                    header("Location: manage_events.php");
                    exit();
                } else {
                    $error = "Insert error: " . $conn->error;
                }
            } elseif ($action === 'edit') {
                $id = intval($_POST['id'] ?? 0);
                if ($id > 0) {
                    // If new poster uploaded, delete old poster
                    if ($uploaded_poster) {
                        $f_old = $conn->prepare("SELECT poster FROM events WHERE id = ?");
                        $f_old->bind_param("i", $id);
                        $f_old->execute();
                        $old_p = $f_old->get_result()->fetch_assoc()['poster'] ?? '';
                        $f_old->close();
                        if (!empty($old_p) && file_exists(__DIR__ . '/uploads/' . $old_p)) {
                            unlink(__DIR__ . '/uploads/' . $old_p);
                        }

                        $up = $conn->prepare("UPDATE events SET title=?, category=?, description=?, event_date=?, poster=?, status=? WHERE id=?");
                        $up->bind_param("ssssssi", $title, $category, $description, $event_date, $uploaded_poster, $status, $id);
                    } else {
                        $up = $conn->prepare("UPDATE events SET title=?, category=?, description=?, event_date=?, status=? WHERE id=?");
                        $up->bind_param("sssssi", $title, $category, $description, $event_date, $status, $id);
                    }

                    if ($up->execute()) {
                        $up->close();
                        log_audit_action('EDIT_EVENT', "Admin updated event #$id: '$title'.");
                        $_SESSION['flash_msg'] = "Event '$title' updated successfully!";
                        $_SESSION['flash_type'] = "success";
                        header("Location: manage_events.php");
                        exit();
                    } else {
                        $error = "Update error: " . $conn->error;
                    }
                }
            }
        }
    }
}

// Query events list
$events = $conn->query("SELECT * FROM events ORDER BY event_date ASC")->fetch_all(MYSQLI_ASSOC);

$flash = $_SESSION['flash_msg'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

require_once __DIR__ . '/includes/header.php';
?>

<div class="content-container">
    <div class="toolbar">
        <div>
            <h1>Event Management (Admin CRUD)</h1>
            <p>Publish, edit, and delete campus events with secure file uploads, MIME inspection, and directory protection.</p>
        </div>
        <div>
            <button id="addEventModalBtn" class="btn btn-primary">+ Create New Event</button>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flash_type) ?>">
            <?= htmlspecialchars($flash) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Events Table -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="70">Poster</th>
                        <th>Event Title</th>
                        <th>Category</th>
                        <th>Event Date</th>
                        <th>Status</th>
                        <th width="160">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $ev): ?>
                            <?php 
                                $poster = (!empty($ev['poster']) && file_exists(__DIR__ . '/uploads/' . $ev['poster']))
                                    ? 'uploads/' . htmlspecialchars($ev['poster'])
                                    : '../Practical 12/uploads/default_poster.svg';
                            ?>
                            <tr>
                                <td>
                                    <img src="<?= $poster ?>" alt="Poster" style="width: 50px; height: 35px; object-fit: cover; border-radius: 4px;">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($ev['title']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars(mb_strimwidth($ev['description'] ?? '', 0, 60, '...')) ?></small>
                                </td>
                                <td><span class="category-pill"><?= htmlspecialchars($ev['category']) ?></span></td>
                                <td><?= date('D, d M Y', strtotime($ev['event_date'])) ?></td>
                                <td>
                                    <span class="status-pill status-<?= htmlspecialchars($ev['status']) ?>">
                                        <?= ucfirst(htmlspecialchars($ev['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-sm btn-edit" onclick='openEditEventModal(<?= json_encode($ev) ?>)'>Edit</button>
                                    <a href="manage_events.php?del_id=<?= (int)$ev['id'] ?>" 
                                       class="btn-sm btn-delete" 
                                       onclick="return confirm('Permanently delete event <?= htmlspecialchars(addslashes($ev['title'])) ?>?');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 24px;">No events registered.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Add / Edit Event -->
<div id="eventModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="eventModalTitle">Create Campus Event</h3>
            <button type="button" class="close-btn" id="closeEventModal">&times;</button>
        </div>

        <form method="POST" action="manage_events.php" enctype="multipart/form-data">
            <input type="hidden" name="action" id="eventAction" value="add">
            <input type="hidden" name="id" id="eventId" value="">

            <div class="form-group">
                <label for="e_title">Event Title *</label>
                <input type="text" id="e_title" name="title" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="e_category">Category *</label>
                    <select id="e_category" name="category" required>
                        <option value="Technical">Technical</option>
                        <option value="Workshop">Workshop</option>
                        <option value="Cultural">Cultural</option>
                        <option value="Sports">Sports</option>
                        <option value="Seminar">Seminar</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="e_date">Event Date *</label>
                    <input type="date" id="e_date" name="event_date" required>
                </div>
            </div>

            <div class="form-group">
                <label for="e_desc">Description</label>
                <textarea id="e_desc" name="description" rows="3"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="e_poster">Poster File (Max 2MB)</label>
                    <input type="file" id="e_poster" name="poster" accept=".jpg,.jpeg,.png,.webp">
                    <small class="help-text">JPG, PNG, WEBP</small>
                </div>

                <div class="form-group">
                    <label for="e_status">Registration Status</label>
                    <select id="e_status" name="status">
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="cancelEventModal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveEventBtn">Save Event</button>
            </div>
        </form>
    </div>
</div>

<script>
    const evModal = document.getElementById('eventModal');
    const openEvBtn = document.getElementById('addEventModalBtn');
    const closeEvBtn = document.getElementById('closeEventModal');
    const cancelEvBtn = document.getElementById('cancelEventModal');

    function openAddEv() {
        document.getElementById('eventModalTitle').textContent = 'Create Campus Event';
        document.getElementById('eventAction').value = 'add';
        document.getElementById('eventId').value = '';
        document.getElementById('e_title').value = '';
        document.getElementById('e_category').value = 'Technical';
        document.getElementById('e_date').value = '';
        document.getElementById('e_desc').value = '';
        document.getElementById('e_status').value = 'open';
        evModal.style.display = 'flex';
    }

    function openEditEventModal(e) {
        document.getElementById('eventModalTitle').textContent = 'Edit Event #' + e.id;
        document.getElementById('eventAction').value = 'edit';
        document.getElementById('eventId').value = e.id;
        document.getElementById('e_title').value = e.title;
        document.getElementById('e_category').value = e.category;
        document.getElementById('e_date').value = e.event_date;
        document.getElementById('e_desc').value = e.description || '';
        document.getElementById('e_status').value = e.status;
        evModal.style.display = 'flex';
    }

    function closeEv() {
        evModal.style.display = 'none';
    }

    if (openEvBtn) openEvBtn.addEventListener('click', openAddEv);
    if (closeEvBtn) closeEvBtn.addEventListener('click', closeEv);
    if (cancelEvBtn) cancelEvBtn.addEventListener('click', closeEv);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
