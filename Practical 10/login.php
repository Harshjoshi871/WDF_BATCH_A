<?php
/**
 * Practical 10: Secure Login Page
 * StudentHub Portal - CHARUSAT ITUE203
 */
require_once __DIR__ . '/session_helper.php';

// If user already logged in, redirect to respective dashboard
if (is_logged_in()) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        header("Location: student_dashboard.php");
        exit();
    }
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$login_error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

// Handle query message flags
$msg = $_GET['msg'] ?? '';
$info_message = '';
if ($msg === 'timeout') {
    $info_message = "Your session expired due to 15 minutes of inactivity. Please log in again.";
} elseif ($msg === 'logged_out') {
    $info_message = "You have been logged out securely.";
} elseif ($msg === 'login_required') {
    $info_message = "Please log in to access this page.";
} elseif ($msg === 'unauthorized') {
    $info_message = "Access Denied: You do not have permissions for that resource.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Practical 10: Secure Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="header-container">
            <div class="brand">
                <span class="logo-badge">🎓</span>
                <div>
                    <h1>StudentHub Portal</h1>
                    <p>CHARUSAT Authentication & Access Control</p>
                </div>
            </div>
            <nav>
                <a href="../Practical 8/test_connection.php">Database Status</a>
                <a href="../Practical 9/register.php">Practical 9: Register</a>
                <a class="active" href="login.php">Practical 10: Login</a>
                <a href="../Practical 11/index.php">Practical 11: Students</a>
                <a href="../Practical 12/index.php">Practical 12: Events</a>
                <a href="../Practical 13/index.html">Practical 13: AJAX CRUD</a>
                <a href="../Practical 14/index.php">Practical 14: Dashboard</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="card login-card">
            <div class="card-header">
                <h2>Practical 10: Secure User Login</h2>
                <p>Login with sessions, role-based redirection, timeout, and secure remember-me token.</p>
            </div>

            <?php if (!empty($info_message)): ?>
                <div class="alert alert-info" role="alert">
                    <?= htmlspecialchars($info_message) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($login_error)): ?>
                <div class="alert alert-error" role="alert">
                    <strong>Authentication Error:</strong> <?= htmlspecialchars($login_error) ?>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="e.g. admin@studenthub.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="eye-btn" id="eyeButton" title="Show/Hide Password">👁</button>
                    </div>
                </div>

                <div class="form-group form-row-between">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me" id="rememberMe" value="1">
                        <span>Remember Me for 30 Days</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    Secure Login &rarr;
                </button>
            </form>

            <div class="demo-credentials">
                <h4>Quick Test Credentials:</h4>
                <div class="cred-box">
                    <div><strong>Admin Role:</strong> <code>admin@studenthub.com</code> / <code>Admin@123</code></div>
                    <div><strong>Student Role:</strong> <code>harsh@gmail.com</code> / <code>Student@123</code></div>
                </div>
            </div>

            <div class="card-footer">
                <p>Don't have an account? <a href="../Practical 9/register.php">Register as a Student</a></p>
            </div>
        </div>
    </main>

    <footer>
        <p>© 2026 StudentHub Portal | CHARUSAT - ITUE203 Web Development Frameworks</p>
    </footer>

    <script>
        const eye = document.getElementById("eyeButton");
        const passwordInput = document.getElementById("password");

        if (eye && passwordInput) {
            eye.addEventListener("click", function() {
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    eye.textContent = "🙈";
                } else {
                    passwordInput.type = "password";
                    eye.textContent = "👁";
                }
            });
        }
    </script>
</body>
</html>
