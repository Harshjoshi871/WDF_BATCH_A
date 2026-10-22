<?php
/**
 * Practical 15: Unified Authentication Page
 * StudentHub Full-Stack Portal
 */
require_once __DIR__ . '/config/auth.php';

// Redirect if already authenticated
if (is_logged_in()) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}

$page_title = "Login";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $error = "Security error: Invalid CSRF token.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember_me = isset($_POST['remember_me']);

        $stmt = $conn->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user_row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user_row && password_verify($password, $user_row['password'])) {
            if ($user_row['status'] !== 'active') {
                $error = "This account is inactive. Please contact the administrator.";
            } else {
                // Prevent session fixation
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user_row['id'];
                $_SESSION['name'] = $user_row['name'];
                $_SESSION['email'] = $user_row['email'];
                $_SESSION['role'] = $user_row['role'];
                $_SESSION['last_activity'] = time();

                // Update last login
                $up_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $up_stmt->bind_param("i", $user_row['id']);
                $up_stmt->execute();
                $up_stmt->close();

                // Remember Me Token (Advanced Extension)
                if ($remember_me) {
                    $raw_token = bin2hex(random_bytes(32));
                    $token_hash = hash('sha256', $raw_token);

                    $rem_stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $rem_stmt->bind_param("si", $token_hash, $user_row['id']);
                    $rem_stmt->execute();
                    $rem_stmt->close();

                    setcookie('studenthub_remember', $raw_token, [
                        'expires'  => time() + (86400 * 30),
                        'path'     => '/',
                        'domain'   => '',
                        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                }

                log_audit_action('USER_LOGIN', "User {$user_row['name']} logged in.");

                if (!empty($redirect) && strpos($redirect, 'http') === false) {
                    header("Location: " . $redirect);
                } elseif ($user_row['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: student_dashboard.php");
                }
                exit();
            }
        } else {
            $error = "Invalid email address or password combination.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="card auth-card">
        <div class="card-header">
            <h2>Welcome Back to StudentHub</h2>
            <p>Sign in to access your student profile or administrative control panel.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out'): ?>
            <div class="alert alert-success">
                You have logged out successfully.
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'timeout'): ?>
            <div class="alert alert-warning">
                Your session expired due to 15 minutes of inactivity. Please sign in again.
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'login_required'): ?>
            <div class="alert alert-warning">
                Please log in to access that page.
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'unauthorized'): ?>
            <div class="alert alert-danger">
                Access Denied: You lack permissions for that administrative resource.
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label for="email">Institutional / Personal Email *</label>
                <input type="email" id="email" name="email" placeholder="e.g. admin@studenthub.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="button" class="eye-btn" id="eyeBtn">👁</button>
                </div>
            </div>

            <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                <label class="terms-label">
                    <input type="checkbox" name="remember_me" value="1">
                    <span>Remember me on this browser</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign In &rarr;</button>
        </form>

        <div class="demo-credentials" style="margin-top: 24px;">
            <h4>Demo Testing Accounts:</h4>
            <div class="cred-box">
                <div><strong>Admin Account:</strong> <code>admin@studenthub.com</code> / <code>Admin@123</code></div>
                <div><strong>Student Account:</strong> <code>harsh@gmail.com</code> / <code>Student@123</code></div>
            </div>
        </div>

        <div class="card-footer" style="margin-top: 20px;">
            <p>New to StudentHub? <a href="register.php">Create a student account</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
