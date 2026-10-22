const form = document.getElementById("registrationForm");

const nameRegex = /^[A-Za-z ]{3,40}$/;
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const mobileRegex = /^[6-9]\d{9}$/;
const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$!%])[A-Za-z\d@#$!%]{8,}$/;

const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirmPassword");

const passwordEye = document.getElementById("passwordEye");
const confirmEye = document.getElementById("confirmEye");

const strengthBar = document.getElementById("strengthBar");

passwordEye.addEventListener("click", function () {
    if (password.type === "password") {
        password.type = "text";
        passwordEye.textContent = "🙈";
    } else {
        password.type = "password";
        passwordEye.textContent = "👁";
    }
});

confirmEye.addEventListener("click", function () {
    if (confirmPassword.type === "password") {
        confirmPassword.type = "text";
        confirmEye.textContent = "🙈";
    } else {
        confirmPassword.type = "password";
        confirmEye.textContent = "👁";
    }
});

password.addEventListener("input", function () {

    const value = password.value;

    if (value.length === 0) {
        strengthBar.style.width = "0%";
    } 
    else if (value.length < 6) {
        strengthBar.style.width = "25%";
    } 
    else if (!passwordRegex.test(value)) {
        strengthBar.style.width = "50%";
    } 
    else {
        strengthBar.style.width = "100%";
    }
});

form.addEventListener("submit", function (event) {

    event.preventDefault();

    document.querySelectorAll("small").forEach(function (element) {
        element.textContent = "";
    });

    document.getElementById("successMessage").textContent = "";

    let valid = true;

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const mobile = document.getElementById("mobile").value.trim();
    const course = document.getElementById("course").value;
    const year = document.getElementById("year").value;
    const gender = document.querySelector('input[name="gender"]:checked');
    const terms = document.getElementById("terms").checked;

    if (!nameRegex.test(name)) {
        document.getElementById("nameError").textContent =
            "Enter a valid name.";
        valid = false;
    }

    if (!emailRegex.test(email)) {
        document.getElementById("emailError").textContent =
            "Enter a valid email address.";
        valid = false;
    }

    if (!mobileRegex.test(mobile)) {
        document.getElementById("mobileError").textContent =
            "Enter a valid 10-digit mobile number.";
        valid = false;
    }

    if (!passwordRegex.test(password.value)) {
        document.getElementById("passwordError").textContent =
            "Password must contain 8 characters, uppercase, lowercase, number and special character.";
        valid = false;
    }

    if (confirmPassword.value === "") {
        document.getElementById("confirmPasswordError").textContent =
            "Please confirm your password.";
        valid = false;
    } 
    else if (confirmPassword.value !== password.value) {
        document.getElementById("confirmPasswordError").textContent =
            "Passwords do not match.";
        valid = false;
    }

    if (course === "") {
        document.getElementById("courseError").textContent =
            "Please select a course.";
        valid = false;
    }

    if (year === "") {
        document.getElementById("yearError").textContent =
            "Please select your year.";
        valid = false;
    }

    if (!gender) {
        document.getElementById("genderError").textContent =
            "Please select your gender.";
        valid = false;
    }

    if (!terms) {
        document.getElementById("termsError").textContent =
            "Please accept the terms and conditions.";
        valid = false;
    }

    if (valid) {

        document.getElementById("successMessage").textContent =
            "Registration successful!";

        form.reset();

        strengthBar.style.width = "0%";

        password.type = "password";
        confirmPassword.type = "password";

        passwordEye.textContent = "👁";
        confirmEye.textContent = "👁";
    }
});