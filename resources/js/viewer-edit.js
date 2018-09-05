$(document).ready(function() {
	$('button.save-changes').click(function(){
		var contentobject = {};
		// TODO: construct content object from HTML
		$.post("/editor/", contentobject);
	});
});