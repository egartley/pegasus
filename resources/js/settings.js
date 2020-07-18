$(document).ready(function () {
    $("input#permalinktextbox").val($("span.hidden#onloadpermalink").html());
    $("button#permalinkapply").on("click", function () {
        var input = $("input#permalinktextbox").val();

        $("span#permalinkapplystatustext").html("Changing permalinks...");
        $.post("/action/", {
            action: "updatepermalink",
            value: input
        }).done(function (data) {
            $("span#permalinkapplystatustext").html(data)
        });
    })
});