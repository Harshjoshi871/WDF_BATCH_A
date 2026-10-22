document.addEventListener("DOMContentLoaded", function() {

    var form = document.getElementById("contactForm");

    if (form) {

        form.addEventListener("submit", function(e) {

            e.preventDefault();

            alert("Your message has been sent successfully!");

            form.reset();

        });

    }

});