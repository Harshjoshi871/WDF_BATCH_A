document.addEventListener("DOMContentLoaded", function() {

    var form = document.getElementById("registerForm");

    

    if (form) {

        form.addEventListener("submit", function(e) {

            e.preventDefault();

            var password = document.getElementById("registerPassword").value;

            var confirm = document.getElementById("confirmPassword").value;

            if (password.length < 6) {

                alert("Password must contain at least 6 characters.");

                return;

            }

            if (password != confirm) {

                alert("Passwords do not match.");

                return;

            }

            alert("Registration Successful!");

            window.location.href = "login.html";

        });

    }

});
