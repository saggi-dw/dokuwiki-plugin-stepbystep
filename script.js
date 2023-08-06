/*
Author:  Swimmer F (https://stackoverflow.com/users/12061972/swimmer-f)
Link: https://stackoverflow.com/questions/66020524/collapsible-box-not-showing-all-of-my-text-in-html-and-css/66020964#66020964
*/
var coll = document.getElementsByClassName("stepbystep_collapsible");
var i;

for (i = 0; i < coll.length; i++) {
    coll[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var content = this.nextElementSibling;
        if (content.style.maxHeight) {
            content.style.maxHeight = content.scrollHeight + "px";
            setTimeout(function() {
                content.style.maxHeight = null;
            }, 50)
        } else {
            content.style.maxHeight = content.scrollHeight + "px";
            setTimeout(function() {
                content.style.maxHeight = "fit-content";
            }, 250)
        }
    });
}