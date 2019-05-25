$(document).ready(function () {
    $("input#permalinktextbox").val($("span.hidden#onloadpermalink").html());
    $("button#permalinkapply").on("click", function () {
        $("span#permalinkapplystatustext").html("Changing permalinks...");
        $.post("/submit/", {
            action: "updatepermalink",
            value: $("input#permalinktextbox").val()
        }).done(function (data) {
            $("span#permalinkapplystatustext").html(data)
        });
    })
});