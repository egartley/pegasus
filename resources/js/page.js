// https://stackoverflow.com/a/488073
function isWithinView(e, full) {
	var pageTop = $(window).scrollTop();
	var pageBottom = pageTop + $(window).height();
	var elementTop = e.offset().top;
	var elementBottom = elementTop + e.height();

	if (full === true) {
		return ((pageTop < elementTop) && (pageBottom > elementBottom));
	} else {
		return ((elementTop <= pageBottom) && (elementBottom >= pageTop));
	}
}

var showingInfobox = true;

$(document).ready(function() {
	editorInit();
	var infobox = $('table.infobox');
	$(window).scroll(function() {
		if (!isWithinView(infobox, false)) {
			if (showingInfobox) {
				// not visible, but still showing, so hide it
				infobox.hide();
				showingInfobox = false;
			} else {
				// not visible, not showing, all good
			}
		} else {
			if (!showingInfobox) {
				// visible, but not showing, so show it
				infobox.show();
				showingInfobox = true;
			} else {
				// visible, showing, all good
			}
		}
	})
})