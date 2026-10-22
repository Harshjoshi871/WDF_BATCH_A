document.addEventListener("DOMContentLoaded", function() {

    var form = document.getElementById("loginForm");

    var eye = document.getElementById("eyeButton");

    if (form) {

        form.addEventListener("submit", function(e) {

            e.preventDefault();

            var email = document.getElementById("emailField").value.trim();

            var password = document.getElementById("passwordField").value.trim();

            if (email == "") {

                alert("Please enter your email.");

                return;
            }

            if (password.length < 6) {

                alert("Password must contain at least 6 characters.");

                return;
            }

            localStorage.setItem("loggedInStudentEmail", email);

            if (document.getElementById("rememberMe").checked) {

                localStorage.setItem("rememberStudent", "true");

            }

            alert("Login Successful!");

            window.location.href = "profile.html";

        });

    }

    if (eye) {

        eye.addEventListener("click", function() {

            var password = document.getElementById("passwordField");

            if (password.type == "password") {

                password.type = "text";

                eye.innerHTML = "🙈";

            }
            else {

                password.type = "password";

                eye.innerHTML = "👁";

            }

        });

    }

});
