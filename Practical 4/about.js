document.addEventListener("DOMContentLoaded", function() {

    var title = document.querySelector(".card h2");

    if (title) {

        title.addEventListener("click", function() {

            alert("Welcome to the StudentHub About page.");

        });

    }

});
