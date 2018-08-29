$(document).ready(function() {
	$("div.page-options > p > a#save").click(function(e) {
		e.preventDefault();
		var url = "/editor/?action=save&id=" + $("div.page-meta span#id").html() + "&title=" + encodeURI($("div.page-meta input#title").val()) + "&isnew=";
		if ($("div.page-meta span#isnew").html() == "true") {
			url += "yes";
		} else {
			url += "no";
		}
		console.log("URL: " + url);
		window.location = url;
		return false;
	});
});