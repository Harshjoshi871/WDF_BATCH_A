<?php
/**
 * Practical 9: Secure User Registration Form
 * StudentHub Portal - CHARUSAT ITUE203
 */
session_start();

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errorMessage = $_SESSION['reg_error'] ?? '';
$successMessage = $_SESSION['reg_success'] ?? '';
$oldInput = $_SESSION['old_input'] ?? [];

// Clear flash messages after retrieving
unset($_SESSION['reg_error'], $_SESSION['reg_success'], $_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Practical 9: Secure User Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="header-container">
            <div class="brand">
                <span class="logo-badge">🎓</span>
                <div>
                    <h1>StudentHub Portal</h1>
                    <p>CHARUSAT Student Registration System</p>
                </div>
            </div>
            <nav>
                <a href="../Practical 8/test_connection.php">Database Status</a>
                <a class="active" href="register.php">Practical 9: Register</a>
                <a href="../Practical 10/login.php">Practical 10: Login</a>
                <a href="../Practical 11/index.php">Practical 11: Students</a>
                <a href="../Practical 12/index.php">Practical 12: Events</a>
                <a href="../Practical 13/index.html">Practical 13: AJAX CRUD</a>
                <a href="../Practical 14/index.php">Practical 14: Dashboard</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="card register-card">
            <div class="card-header">
                <h2>Practical 9: Secure User Registration</h2>
                <p>Register a new student account with MySQLi prepared statements, duplicate checks, and bcrypt password hashing.</p>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-error" role="alert">
                    <strong>Error:</strong> <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success" role="alert">
                    <strong>Success!</strong> <?= htmlspecialchars($successMessage) ?>
                    <div style="margin-top: 8px;">
                        <a href="../Practical 10/login.php" class="alert-link">Click here to Login &rarr;</a>
                    </div>
                </div>
            <?php endif; ?>

            <form id="registrationForm" action="register_process.php" method="POST" novalidate>
                <!-- CSRF Token (Advanced Requirement) -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" placeholder="e.g. Harsh Joshi" 
                           value="<?= htmlspecialchars($oldInput['name'] ?? '') ?>" required>
                    <small class="error-msg" id="nameError"></small>
                </div>

                <div class="form-group">
                    <label for="email">Institutional / Personal Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" placeholder="e.g. harsh@gmail.com" 
                           value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>" required>
                    <small class="error-msg" id="emailError"></small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" placeholder="10-digit mobile number" 
                               value="<?= htmlspecialchars($oldInput['phone'] ?? '') ?>" required>
                        <small class="error-msg" id="phoneError"></small>
                    </div>

                    <div class="form-group">
                        <label for="course">Course <span class="required">*</span></label>
                        <select id="course" name="course" required>
                            <option value="">-- Select Course --</option>
                            <option value="B.Tech IT" <?= ($oldInput['course'] ?? '') === 'B.Tech IT' ? 'selected' : '' ?>>B.Tech IT</option>
                            <option value="B.Tech CSE" <?= ($oldInput['course'] ?? '') === 'B.Tech CSE' ? 'selected' : '' ?>>B.Tech CSE</option>
                            <option value="BCA" <?= ($oldInput['course'] ?? '') === 'BCA' ? 'selected' : '' ?>>BCA</option>
                            <option value="MCA" <?= ($oldInput['course'] ?? '') === 'MCA' ? 'selected' : '' ?>>MCA</option>
                        </select>
                        <small class="error-msg" id="courseError"></small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="year">Academic Year <span class="required">*</span></label>
                    <select id="year" name="year" required>
                        <option value="">-- Select Year --</option>
                        <option value="1" <?= ($oldInput['year'] ?? '') === '1' ? 'selected' : '' ?>>1st Year</option>
                        <option value="2" <?= ($oldInput['year'] ?? '') === '2' ? 'selected' : '' ?>>2nd Year</option>
                        <option value="3" <?= ($oldInput['year'] ?? '') === '3' ? 'selected' : '' ?>>3rd Year</option>
                        <option value="4" <?= ($oldInput['year'] ?? '') === '4' ? 'selected' : '' ?>>4th Year</option>
                    </select>
                    <small class="error-msg" id="yearError"></small>
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="password-input-wrapper">
                        <input type="password" id="password" name="password" placeholder="At least 8 chars, 1 uppercase, 1 number, 1 special symbol" required>
                        <button type="button" class="eye-btn" id="passwordEye" title="Show/Hide Password">👁</button>
                    </div>
                    <div class="strength-meter-container">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <small id="strengthText" class="strength-text">Password strength</small>
                    <small class="error-msg" id="passwordError"></small>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password <span class="required">*</span></label>
                    <div class="password-input-wrapper">
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password" required>
                        <button type="button" class="eye-btn" id="confirmEye" title="Show/Hide Password">👁</button>
                    </div>
                    <small class="error-msg" id="confirmPasswordError"></small>
                </div>

                <div class="form-group checkbox-group">
                    <label class="terms-label">
                        <input type="checkbox" id="terms" name="terms" required>
                        I accept the <a href="#" onclick="alert('StudentHub Acceptable Usage Policy');return false;">Terms and Conditions</a>
                    </label>
                    <small class="error-msg" id="termsError"></small>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    Create Secure Account
                </button>
            </form>

            <div class="card-footer">
                <p>Already have an account? <a href="../Practical 10/login.php">Login here</a></p>
            </div>
        </div>
    </main>

    <footer>
        <p>© 2026 StudentHub Portal | CHARUSAT - ITUE203 Web Development Frameworks</p>
    </footer>

    <script src="register.js"></script>
</body>
</html>
