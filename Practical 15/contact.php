<?php
/**
 * Practical 15: Contact & Inquiries Page (Connecting Practical 7 File Storage)
 * StudentHub Full-Stack Portal
 */
$page_title = "Contact Us";
require_once __DIR__ . '/includes/header.php';

$success = false;
$errors = [];
$name = '';
$email = '';
$phone = '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, "UTF-8");
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = preg_replace("/[^0-9]/", "", trim($_POST['phone'] ?? ''));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''), ENT_QUOTES, "UTF-8");
    $message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, "UTF-8");

    if (empty($name) || strlen($name) < 3) {
        $errors[] = "Please provide your full name (at least 3 characters).";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }
    if (!preg_match("/^[6-9]\d{9}$/", $phone)) {
        $errors[] = "Phone number must be a valid 10-digit number.";
    }
    if (empty($subject)) {
        $errors[] = "Please select or state a subject for your inquiry.";
    }
    if (empty($message) || strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters long.";
    }

    if (empty($errors)) {
        // Safe CSV file writing using flock (Practical 7 implementation)
        $csv_file = __DIR__ . '/inquiries.csv';
        $fh = fopen($csv_file, 'a');
        if ($fh && flock($fh, LOCK_EX)) {
            fputcsv($fh, [$name, $email, $phone, $subject, $message, date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            flock($fh, LOCK_UN);
            fclose($fh);
            $success = true;
            // Clear inputs on success
            $name = $email = $phone = $subject = $message = '';
        } else {
            $errors[] = "Could not open storage file for recording inquiry.";
            if ($fh) fclose($fh);
        }
    }
}
?>

<div class="content-container">
    <div class="page-header">
        <h1>Contact StudentHub & Campus Administration</h1>
        <p>Reach out for academic assistance, event coordination, technical support, or general inquiries.</p>
    </div>

    <div class="contact-grid">
        <div class="card" style="padding: 28px;">
            <h3>Send an Inquiry</h3>
            <p class="subtitle">Submissions are processed by PHP server-side validation and recorded safely in CSV format.</p>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>Thank you!</strong> Your message has been safely received. A campus representative will respond shortly.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Please address the following:</strong>
                    <ul style="margin-left: 20px; margin-top: 6px;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="contact.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Institutional Email *</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="10-digit number" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">Inquiry Subject *</label>
                        <select id="subject" name="subject" required>
                            <option value="">-- Select Category --</option>
                            <option value="Event Registration" <?= $subject === 'Event Registration' ? 'selected' : '' ?>>Event Registration</option>
                            <option value="Academic Portal Issue" <?= $subject === 'Academic Portal Issue' ? 'selected' : '' ?>>Academic Portal Issue</option>
                            <option value="Student Profile Correction" <?= $subject === 'Student Profile Correction' ? 'selected' : '' ?>>Student Profile Correction</option>
                            <option value="General Campus Question" <?= $subject === 'General Campus Question' ? 'selected' : '' ?>>General Campus Question</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="message">Detailed Message *</label>
                    <textarea id="message" name="message" rows="4" placeholder="How can we assist you?..." required><?= htmlspecialchars($message) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Transmit Message &rarr;</button>
            </form>
        </div>

        <div class="contact-info-card">
            <h3>Campus Contact Information</h3>
            <div class="contact-point">
                <strong>📍 Campus Location:</strong>
                <p>Faculty of Technology and Engineering (FTE)<br>
                CHARUSAT Campus, 139, Changa - Valetva Rd, Gujarat 388421</p>
            </div>

            <div class="contact-point">
                <strong>📞 Department Helpdesk:</strong>
                <p>+91 (02697) 265011 / 265021</p>
            </div>

            <div class="contact-point">
                <strong>✉️ Email Address:</strong>
                <p>info@charusat.ac.in | studenthub@charusat.edu.in</p>
            </div>

            <div class="contact-point">
                <strong>⏰ Office Hours:</strong>
                <p>Monday – Friday: 09:00 AM – 04:30 PM<br>Saturday: 09:00 AM – 01:00 PM</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
