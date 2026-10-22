document.addEventListener("DOMContentLoaded", function() {

    var button = document.querySelector(".button");

    if (button) {

        button.addEventListener("click", function() {

            localStorage.removeItem("loggedInStudentEmail");

            localStorage.removeItem("rememberStudent");

        });

    }

});
