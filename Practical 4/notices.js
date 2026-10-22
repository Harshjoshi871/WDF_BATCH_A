document.addEventListener("DOMContentLoaded", function() {

    var notices = document.querySelectorAll(".notice");

    for (var i = 0; i < notices.length; i++) {

        notices[i].addEventListener("click", function() {

            alert("Please read this notice carefully.");

        });

    }

});

