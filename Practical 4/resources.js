document.addEventListener("DOMContentLoaded", function() {

    var links = document.querySelectorAll(".resources a");

    for (var i = 0; i < links.length; i++) {

        links[i].addEventListener("click", function(e) {

            e.preventDefault();

            alert("Resource selected.");

        });

    }

});
