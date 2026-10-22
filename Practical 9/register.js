/**
 * Practical 9: Client-Side Form Validation & UX Enhancement
 * Consistent with Practical 5 validation standards
 */

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registrationForm");
    const nameInput = document.getElementById("name");
    const emailInput = document.getElementById("email");
    const phoneInput = document.getElementById("phone");
    const courseInput = document.getElementById("course");
    const yearInput = document.getElementById("year");
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirmPassword");
    const termsInput = document.getElementById("terms");

    const passwordEye = document.getElementById("passwordEye");
    const confirmEye = document.getElementById("confirmEye");
    const strengthBar = document.getElementById("strengthBar");
    const strengthText = document.getElementById("strengthText");

    // Regular expressions
    const nameRegex = /^[A-Za-z\s]{3,50}$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^[6-9]\d{9}$/;
    const strongPasswordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$!%*?&])[A-Za-z\d@#$!%*?&]{8,}$/;

    // Password Eye Toggles
    if (passwordEye) {
        passwordEye.addEventListener("click", function () {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                passwordEye.textContent = "🙈";
            } else {
                passwordInput.type = "password";
                passwordEye.textContent = "👁";
            }
        });
    }

    if (confirmEye) {
        confirmEye.addEventListener("click", function () {
            if (confirmPasswordInput.type === "password") {
                confirmPasswordInput.type = "text";
                confirmEye.textContent = "🙈";
            } else {
                confirmPasswordInput.type = "password";
                confirmEye.textContent = "👁";
            }
        });
    }

    // Dynamic Password Strength Meter
    passwordInput.addEventListener("input", function () {
        const val = passwordInput.value;
        let score = 0;

        if (val.length >= 8) score++;
        if (/[a-z]/.test(val)) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/\d/.test(val)) score++;
        if (/[@#$!%*?&]/.test(val)) score++;

        if (val.length === 0) {
            strengthBar.style.width = "0%";
            strengthBar.style.backgroundColor = "#e5e7eb";
            strengthText.textContent = "Password strength";
            strengthText.style.color = "#6b7280";
        } else if (score <= 2) {
            strengthBar.style.width = "25%";
            strengthBar.style.backgroundColor = "#ef4444";
            strengthText.textContent = "Weak password (add uppercase, number & symbol)";
            strengthText.style.color = "#ef4444";
        } else if (score === 3 || score === 4) {
            strengthBar.style.width = "60%";
            strengthBar.style.backgroundColor = "#f59e0b";
            strengthText.textContent = "Moderate password";
            strengthText.style.color = "#f59e0b";
        } else if (score === 5) {
            strengthBar.style.width = "100%";
            strengthBar.style.backgroundColor = "#10b981";
            strengthText.textContent = "Strong password ✓";
            strengthText.style.color = "#10b981";
        }
    });

    // Form Submit Event Handler
    form.addEventListener("submit", function (e) {
        let isValid = true;

        // Clear previous error messages
        document.querySelectorAll(".error-msg").forEach(el => el.textContent = "");

        // 1. Name validation
        if (!nameRegex.test(nameInput.value.trim())) {
            document.getElementById("nameError").textContent = "Please enter a valid full name (letters and spaces only, min 3 chars).";
            isValid = false;
        }

        // 2. Email validation
        if (!emailRegex.test(emailInput.value.trim())) {
            document.getElementById("emailError").textContent = "Please enter a valid email address.";
            isValid = false;
        }

        // 3. Phone validation
        if (!phoneRegex.test(phoneInput.value.trim())) {
            document.getElementById("phoneError").textContent = "Enter a valid 10-digit Indian phone number starting with 6-9.";
            isValid = false;
        }

        // 4. Course validation
        if (courseInput.value === "") {
            document.getElementById("courseError").textContent = "Please select a degree/course.";
            isValid = false;
        }

        // 5. Year validation
        if (yearInput.value === "") {
            document.getElementById("yearError").textContent = "Please select your current year of study.";
            isValid = false;
        }

        // 6. Password validation
        if (!strongPasswordRegex.test(passwordInput.value)) {
            document.getElementById("passwordError").textContent = "Password must be at least 8 chars with uppercase, lowercase, digit & symbol (@#$!%*?&).";
            isValid = false;
        }

        // 7. Confirm password validation
        if (confirmPasswordInput.value === "") {
            document.getElementById("confirmPasswordError").textContent = "Please confirm your password.";
            isValid = false;
        } else if (confirmPasswordInput.value !== passwordInput.value) {
            document.getElementById("confirmPasswordError").textContent = "Passwords do not match.";
            isValid = false;
        }

        // 8. Terms acceptance
        if (!termsInput.checked) {
            document.getElementById("termsError").textContent = "You must agree to the terms and conditions.";
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault(); // Stop submission only if client validation fails
        }
    });
});
