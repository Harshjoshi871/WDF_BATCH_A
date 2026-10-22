document.addEventListener("DOMContentLoaded", function() {

    var rows = document.querySelectorAll("table tr");

    for (var i = 1; i < rows.length; i++) {

        rows[i].addEventListener("click", function() {

            alert("Event selected.");

        });

    }

});

