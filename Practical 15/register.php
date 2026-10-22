<?php
/**
 * Practical 15: Student Registration Page
 * StudentHub Full-Stack Portal
 */
require_once __DIR__ . '/config/auth.php';

if (is_logged_in()) {
    header("Location: index.php");
    exit();
}

$page_title = "Register Student Account";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';
$name = $email = $phone = $course = '';
$year = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $error = "Security error: Invalid CSRF token.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $terms = isset($_POST['terms']);

        if (empty($name) || !preg_match("/^[A-Za-z\s]{3,50}$/", $name)) {
            $error = "Full Name must be between 3 and 50 alphabetic characters.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please provide a valid email address.";
        } elseif (!preg_match("/^[6-9]\d{9}$/", $phone)) {
            $error = "Phone number must be a valid 10-digit number.";
        } elseif (!in_array($course, ['B.Tech IT', 'B.Tech CSE', 'BCA', 'MCA'], true)) {
            $error = "Please select a valid academic degree.";
        } elseif ($year < 1 || $year > 4) {
            $error = "Please select an academic year (1-4).";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        } elseif (!$terms) {
            $error = "You must agree to the Terms and Conditions.";
        } else {
            // Duplicate Check
            $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $chk->bind_param("s", $email);
            $chk->execute();
            $chk->store_result();

            if ($chk->num_rows > 0) {
                $error = "An account with the email '$email' already exists. Please sign in.";
                $chk->close();
            } else {
                $chk->close();

                // Bcrypt hash
                $hashed_pw = password_hash($password, PASSWORD_BCRYPT);

                $conn->begin_transaction();
                try {
                    // Insert User
                    $u_stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'student', 'active')");
                    $u_stmt->bind_param("sss", $name, $email, $hashed_pw);
                    $u_stmt->execute();
                    $new_uid = $conn->insert_id;
                    $u_stmt->close();

                    // Insert Student
                    $st_stmt = $conn->prepare("INSERT INTO students (user_id, name, email, course, year, phone, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                    $st_stmt->bind_param("isssis", $new_uid, $name, $email, $course, $year, $phone);
                    $st_stmt->execute();
                    $st_stmt->close();

                    // Log Audit
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                    $log_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, 'USER_REGISTER', ?, ?)");
                    $det = "Registered student $name ($email) in $course Year $year.";
                    $log_stmt->bind_param("iss", $new_uid, $det, $ip);
                    $log_stmt->execute();
                    $log_stmt->close();

                    $conn->commit();
                    $success = "Account created successfully for $name! You can now log in.";
                    $name = $email = $phone = $course = $year = '';
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Registration failed: " . $e->getMessage();
                }
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="card auth-card" style="max-width: 580px;">
        <div class="card-header">
            <h2>Join StudentHub Portal</h2>
            <p>Create an institutional account to participate in campus events and track your academics.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
                <div style="margin-top: 8px;">
                    <a href="login.php" style="font-weight: bold; text-decoration: underline;">Proceed to Login &rarr;</a>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="e.g. Harsh Joshi" required>
            </div>

            <div class="form-group">
                <label for="email">Institutional Email *</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="e.g. student@charusat.edu.in" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Mobile Number *</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="10-digit phone" required>
                </div>

                <div class="form-group">
                    <label for="course">Course / Degree *</label>
                    <select id="course" name="course" required>
                        <option value="">-- Select Degree --</option>
                        <option value="B.Tech IT" <?= $course === 'B.Tech IT' ? 'selected' : '' ?>>B.Tech IT</option>
                        <option value="B.Tech CSE" <?= $course === 'B.Tech CSE' ? 'selected' : '' ?>>B.Tech CSE</option>
                        <option value="BCA" <?= $course === 'BCA' ? 'selected' : '' ?>>BCA</option>
                        <option value="MCA" <?= $course === 'MCA' ? 'selected' : '' ?>>MCA</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="year">Year of Study *</label>
                <select id="year" name="year" required>
                    <option value="">-- Select Year --</option>
                    <option value="1" <?= (int)$year === 1 ? 'selected' : '' ?>>1st Year</option>
                    <option value="2" <?= (int)$year === 2 ? 'selected' : '' ?>>2nd Year</option>
                    <option value="3" <?= (int)$year === 3 ? 'selected' : '' ?>>3rd Year</option>
                    <option value="4" <?= (int)$year === 4 ? 'selected' : '' ?>>4th Year</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password (Minimum 8 chars) *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="eye-btn" id="eyeBtn">👁</button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password *</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>

            <div class="form-group">
                <label class="terms-label">
                    <input type="checkbox" name="terms" required>
                    <span>I accept the CHARUSAT Code of Conduct and StudentHub policies.</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Register Account &rarr;</button>
        </form>

        <div class="card-footer" style="margin-top: 20px;">
            <p>Already registered? <a href="login.php">Sign in here</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
