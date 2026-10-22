/**
 * Practical 15: Universal Site JavaScript
 * StudentHub Full-Stack Portal
 */

document.addEventListener("DOMContentLoaded", () => {
    // 1. Mobile Menu Toggle
    const menuToggle = document.getElementById("menuToggle");
    const mainNav = document.getElementById("mainNav");

    if (menuToggle && mainNav) {
        menuToggle.addEventListener("click", () => {
            const isExpanded = menuToggle.getAttribute("aria-expanded") === "true";
            menuToggle.setAttribute("aria-expanded", !isExpanded);
            mainNav.classList.toggle("nav-open");
        });
    }

    // 2. Password Eye Toggle
    const eyeBtn = document.getElementById("eyeBtn");
    const passwordInput = document.getElementById("password");

    if (eyeBtn && passwordInput) {
        eyeBtn.addEventListener("click", () => {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeBtn.textContent = "🙈";
            } else {
                passwordInput.type = "password";
                eyeBtn.textContent = "👁";
            }
        });
    }
});
