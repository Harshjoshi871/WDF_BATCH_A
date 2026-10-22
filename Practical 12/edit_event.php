<?php
/**
 * Practical 12: Edit Event with Optional Poster Replacement
 * StudentHub Portal - CHARUSAT ITUE203
 */
session_start();
require_once __DIR__ . '/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch event record
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    $_SESSION['flash_msg'] = "Event not found.";
    $_SESSION['flash_type'] = "danger";
    header("Location: index.php");
    exit();
}

$error = '';
$title = $event['title'];
$category = $event['category'];
$description = $event['description'];
$event_date = $event['event_date'];
$status = $event['status'];
$current_poster = $event['poster'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'Technical');
    $description = trim($_POST['description'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');
    $status = ($_POST['status'] === 'closed') ? 'closed' : 'open';

    if (empty($title) || strlen($title) < 3) {
        $error = "Event title is required (min 3 characters).";
    } elseif (empty($event_date)) {
        $error = "Please specify a valid event date.";
    } else {
        $poster_filename = $current_poster;

        // Check if a new poster was uploaded
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['poster']['tmp_name'];
            $file_size = $_FILES['poster']['size'];
            $orig_name = $_FILES['poster']['name'];
            $file_ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
            $max_size = 2 * 1024 * 1024;

            if ($file_size > $max_size) {
                $error = "Poster file size exceeds maximum permitted limit (2 MB).";
            } elseif (!in_array($file_ext, $allowed_exts, true)) {
                $error = "Invalid file extension. Only JPG, JPEG, PNG, and WEBP images are allowed.";
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);

                if (!in_array($mime_type, $allowed_mimes, true)) {
                    $error = "Security warning: Uploaded file content is not a genuine image format ($mime_type).";
                } else {
                    $new_filename = 'poster_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
                    $target_dest = __DIR__ . '/uploads/' . $new_filename;

                    if (move_uploaded_file($file_tmp, $target_dest)) {
                        // Unlink old poster file if present on disk
                        if (!empty($current_poster) && file_exists(__DIR__ . '/uploads/' . $current_poster)) {
                            unlink(__DIR__ . '/uploads/' . $current_poster);
                        }
                        $poster_filename = $new_filename;
                    } else {
                        $error = "Failed to upload new poster image.";
                    }
                }
            }
        }

        if (empty($error)) {
            $update_stmt = $conn->prepare("UPDATE events SET title = ?, category = ?, description = ?, event_date = ?, poster = ?, status = ? WHERE id = ?");
            $update_stmt->bind_param("ssssssi", $title, $category, $description, $event_date, $poster_filename, $status, $id);

            if ($update_stmt->execute()) {
                $update_stmt->close();

                // Audit log entry
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $action = "EDIT_EVENT";
                $details = "Updated event #$id: '$title'.";
                $log_stmt = $conn->prepare("INSERT INTO audit_logs (action, details, ip_address) VALUES (?, ?, ?)");
                if ($log_stmt) {
                    $log_stmt->bind_param("sss", $action, $details, $ip);
                    $log_stmt->execute();
                    $log_stmt->close();
                }

                $_SESSION['flash_msg'] = "Event '$title' updated successfully!";
                $_SESSION['flash_type'] = "success";
                header("Location: index.php");
                exit();
            } else {
                $error = "Database update failed: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Practical 12: Edit Event #<?= $id ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="header-container">
            <div class="brand">
                <span class="logo-badge">🎪</span>
                <div>
                    <h1>StudentHub Portal</h1>
                    <p>Practical 12: Edit Event #<?= $id ?></p>
                </div>
            </div>
            <nav>
                <a href="index.php">&larr; Back to Events</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="card form-card">
            <h2>Edit Campus Event</h2>
            <p class="subtitle">Modify event schedule, category, status, or replace poster image.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit_event.php?id=<?= $id ?>" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Event Title *</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="Technical" <?= $category === 'Technical' ? 'selected' : '' ?>>Technical</option>
                            <option value="Workshop" <?= $category === 'Workshop' ? 'selected' : '' ?>>Workshop</option>
                            <option value="Cultural" <?= $category === 'Cultural' ? 'selected' : '' ?>>Cultural</option>
                            <option value="Sports" <?= $category === 'Sports' ? 'selected' : '' ?>>Sports</option>
                            <option value="Seminar" <?= $category === 'Seminar' ? 'selected' : '' ?>>Seminar</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="event_date">Event Date *</label>
                        <input type="date" id="event_date" name="event_date" value="<?= htmlspecialchars($event_date) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Event Description</label>
                    <textarea id="description" name="description" rows="4"><?= htmlspecialchars($description) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Current Poster Preview</label>
                    <div style="margin-bottom: 12px;">
                        <?php 
                            $curr_src = (!empty($current_poster) && file_exists(__DIR__ . '/uploads/' . $current_poster))
                                ? 'uploads/' . htmlspecialchars($current_poster)
                                : 'uploads/default_poster.svg';
                        ?>
                        <img src="<?= $curr_src ?>" alt="Current Poster" style="max-height: 140px; border-radius: 6px; border: 1px solid #e2e8f0;">
                    </div>
                    <label for="poster">Upload New Poster (Leave blank to keep existing)</label>
                    <input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp">
                    <small class="help-text">Max 2MB | JPG, PNG, WEBP</small>
                </div>

                <div class="form-group">
                    <label for="status">Event Status</label>
                    <select id="status" name="status">
                        <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open for Registrations</option>
                        <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Event</button>
                    <a href="index.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>© 2026 StudentHub Portal | CHARUSAT - ITUE203 Web Development Frameworks</p>
    </footer>

</body>
</html>
