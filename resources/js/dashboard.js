$(document).ready(function () {
    $("button#makenewpage").on("click", function () {
        window.location = "/editor/?action=new"
    })
});