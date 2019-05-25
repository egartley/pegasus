$(document).ready(function () {
    $("input#permalinktextbox").val($("span.hidden#onloadpermalink").html());
    $("button#permalinkapply").on("click", function () {
        var newpermalink = $("input#permalinktextbox").val();

        $("span#permalinkapplystatustext").html("Changing permalinks...");
        $.post("/submit/", {
            action: "updatepermalink",
            value: newpermalink
        }).done(function (data) {
            $("span#permalinkapplystatustext").html(data)
        });
    })
});