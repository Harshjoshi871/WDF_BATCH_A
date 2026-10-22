<?php
/**
 * Practical 12: Add New Event with Validated Poster Upload
 * StudentHub Portal - CHARUSAT ITUE203
 */
session_start();
require_once __DIR__ . '/db.php';

$error = '';
$title = '';
$category = 'Technical';
$description = '';
$event_date = '';
$status = 'open';

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
        $poster_filename = null;

        // Process File Upload if provided
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['poster']['tmp_name'];
            $file_size = $_FILES['poster']['size'];
            $orig_name = $_FILES['poster']['name'];
            $file_ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
            $max_size = 2 * 1024 * 1024; // 2 Megabytes

            // 1. File Size Validation
            if ($file_size > $max_size) {
                $error = "Poster file size exceeds maximum permitted limit (2 MB).";
            }
            // 2. Extension Validation
            elseif (!in_array($file_ext, $allowed_exts, true)) {
                $error = "Invalid file extension. Only JPG, JPEG, PNG, and WEBP images are allowed.";
            } else {
                // 3. MIME Type Server-side Verification via Fileinfo
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);

                if (!in_array($mime_type, $allowed_mimes, true)) {
                    $error = "Security warning: Uploaded file content does not match a genuine image format ($mime_type).";
                } else {
                    // 4. Advanced Extension: Cryptographically unique renaming to prevent collisions/traversals
                    $poster_filename = 'poster_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
                    $target_dest = __DIR__ . '/uploads/' . $poster_filename;

                    if (!move_uploaded_file($file_tmp, $target_dest)) {
                        $error = "Failed to save uploaded poster image to server directory.";
                    }
                }
            }
        }

        // If no file errors, insert event record
        if (empty($error)) {
            $insert_stmt = $conn->prepare("INSERT INTO events (title, category, description, event_date, poster, status) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssss", $title, $category, $description, $event_date, $poster_filename, $status);

            if ($insert_stmt->execute()) {
                $event_id = $conn->insert_id;
                $insert_stmt->close();

                // Audit log entry (Advanced Extension)
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $action = "ADD_EVENT";
                $details = "Created campus event #$event_id: '$title' ($category).";
                $log_stmt = $conn->prepare("INSERT INTO audit_logs (action, details, ip_address) VALUES (?, ?, ?)");
                if ($log_stmt) {
                    $log_stmt->bind_param("sss", $action, $details, $ip);
                    $log_stmt->execute();
                    $log_stmt->close();
                }

                $_SESSION['flash_msg'] = "Event '$title' successfully published!";
                $_SESSION['flash_type'] = "success";
                header("Location: index.php");
                exit();
            } else {
                $error = "Database insertion failed: " . $conn->error;
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
    <title>StudentHub - Practical 12: Add Event</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="header-container">
            <div class="brand">
                <span class="logo-badge">🎪</span>
                <div>
                    <h1>StudentHub Portal</h1>
                    <p>Practical 12: Add New Event</p>
                </div>
            </div>
            <nav>
                <a href="index.php">&larr; Back to Events</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="card form-card">
            <h2>Create New College Event</h2>
            <p class="subtitle">Publish workshops, hackathons, seminars, or cultural festivals.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="add_event.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Event Title *</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" 
                           placeholder="e.g. AI & Robotics Symposium 2026" required>
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
                    <textarea id="description" name="description" rows="4" 
                              placeholder="Provide overview, eligibility, venue, and rules..."><?= htmlspecialchars($description) ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="poster">Event Poster Image (Max 2MB)</label>
                        <input type="file" id="poster" name="poster" accept=".jpg,.jpeg,.png,.webp">
                        <small class="help-text">Allowed formats: JPG, JPEG, PNG, WEBP (Server checks real MIME type)</small>
                    </div>

                    <div class="form-group">
                        <label for="status">Initial Status</label>
                        <select id="status" name="status">
                            <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open for Registrations</option>
                            <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Publish Event</button>
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
